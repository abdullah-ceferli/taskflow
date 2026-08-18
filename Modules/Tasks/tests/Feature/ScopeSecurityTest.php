<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Modules\Activity\Services\ActivityQueryService;
use Modules\Activity\Services\ActivityRecorder;
use Modules\Dashboard\Services\DashboardService;
use Modules\Projects\Models\Project;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAttachment;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
});

function scopedUser(UserRole $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role->value);

    return $user;
}

test('attachment download requires task visibility', function (): void {
    $admin = scopedUser(UserRole::Admin);
    $member = scopedUser(UserRole::Member);
    $outsider = scopedUser(UserRole::Member);
    $project = Project::factory()->create(['owner_id' => $admin->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $admin->id, 'assignee_id' => $member->id]);
    $attachment = TaskAttachment::factory()->create(['task_id' => $task->id, 'uploaded_by' => $admin->id, 'disk' => 'local', 'path' => 'task-attachments/demo/brief.pdf']);
    Storage::disk('local')->put($attachment->path, 'private task file');

    Sanctum::actingAs($outsider, ['tasks:read']);
    $this->get("/api/v1/tasks/{$task->id}/attachments/{$attachment->id}/download")->assertForbidden();

    Sanctum::actingAs($member, ['tasks:read']);
    $this->get("/api/v1/tasks/{$task->id}/attachments/{$attachment->id}/download")->assertOk();
});

test('member activity and dashboard data excludes unrelated tasks', function (): void {
    $admin = scopedUser(UserRole::Admin);
    $member = scopedUser(UserRole::Member);
    $otherMember = scopedUser(UserRole::Member);
    $project = Project::factory()->create(['owner_id' => $admin->id]);
    $memberTask = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $admin->id, 'assignee_id' => $member->id]);
    $otherTask = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $admin->id, 'assignee_id' => $otherMember->id]);
    $recorder = app(ActivityRecorder::class);
    $recorder->record('task.updated', $admin, $memberTask, ['project_id' => $project->id, 'task_id' => $memberTask->id]);
    $recorder->record('task.updated', $admin, $otherTask, ['project_id' => $project->id, 'task_id' => $otherTask->id]);

    $activities = app(ActivityQueryService::class)->recentForUser($member)->map(fn ($activity): mixed => $activity->properties->get('task_id'))->filter()->all();
    $summary = app(DashboardService::class)->summary($member);

    expect($activities)->toContain($memberTask->id)->not->toContain($otherTask->id);
    expect($summary['totalTasks'])->toBe(1)->and($summary['myTasks'])->toHaveCount(1);
});
