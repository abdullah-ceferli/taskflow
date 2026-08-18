<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Modules\Activity\Services\ActivityRecorder;
use Modules\Projects\Models\Project;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAttachment;
use Modules\Tasks\Models\TaskComment;
use Modules\Tasks\Repositories\Contracts\TaskAttachmentRepositoryInterface;
use Modules\Tasks\Services\TaskAttachmentService;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
});

test('a member can delete their own comment and attachment on an assigned task', function (): void {
    $owner = User::factory()->create();
    $owner->assignRole(UserRole::ProjectManager->value);
    $member = User::factory()->create();
    $member->assignRole(UserRole::Member->value);
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id, 'assignee_id' => $member->id]);
    $comment = TaskComment::factory()->create(['task_id' => $task->id, 'user_id' => $member->id]);
    $attachment = TaskAttachment::factory()->create(['task_id' => $task->id, 'uploaded_by' => $member->id]);

    expect(Gate::forUser($member)->allows('delete', $comment))->toBeTrue()
        ->and(Gate::forUser($member)->allows('delete', $attachment))->toBeTrue();
});

test('attachment delete restores the file when metadata deletion fails', function (): void {
    $owner = User::factory()->create();
    $owner->assignRole(UserRole::Admin->value);
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id]);
    $attachment = TaskAttachment::factory()->create(['task_id' => $task->id, 'uploaded_by' => $owner->id, 'disk' => 'local', 'path' => 'task-attachments/recovery/file.pdf']);
    Storage::disk('local')->put($attachment->path, 'recover me');

    $repository = Mockery::mock(TaskAttachmentRepositoryInterface::class);
    $repository->shouldReceive('delete')->once()->andThrow(new RuntimeException('Database write failed'));
    $service = new TaskAttachmentService($repository, app(ActivityRecorder::class));

    expect(fn () => $service->delete($attachment->load('task'), $owner))->toThrow(RuntimeException::class);
    Storage::disk('local')->assertExists($attachment->path);
});
