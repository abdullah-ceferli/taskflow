<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Modules\Projects\Models\Project;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskComment;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

test('comments API creates comments and rejects nested route tampering', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    $project = Project::factory()->create(['owner_id' => $admin->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $admin->id]);
    $otherTask = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $admin->id]);
    $comment = TaskComment::factory()->create(['task_id' => $otherTask->id, 'user_id' => $admin->id]);
    Sanctum::actingAs($admin, ['comments:write']);

    $this->postJson("/api/v1/tasks/{$task->id}/comments", ['body' => 'Secure API comment'])
        ->assertCreated()
        ->assertJsonPath('data.body', 'Secure API comment');

    $this->deleteJson("/api/v1/tasks/{$task->id}/comments/{$comment->id}")->assertNotFound();
});

test('attachments API rejects unsupported file types before persistence', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    $project = Project::factory()->create(['owner_id' => $admin->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $admin->id]);
    Sanctum::actingAs($admin, ['tasks:write']);

    $this->post("/api/v1/tasks/{$task->id}/attachments", [
        'attachment' => UploadedFile::fake()->create('unsafe.exe', 4, 'application/octet-stream'),
    ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('attachment');
});
