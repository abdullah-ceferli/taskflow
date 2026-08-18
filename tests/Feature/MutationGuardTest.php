<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Services\CurrentWorkspace;
use App\Services\WorkspaceService;
use Database\Seeders\RolePermissionSeeder;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskStatusService;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

dataset('status transition mutation matrix', [
    'todo' => [TaskStatus::Todo, [TaskStatus::InProgress, TaskStatus::Cancelled]],
    'in progress' => [TaskStatus::InProgress, [TaskStatus::Todo, TaskStatus::Review, TaskStatus::Cancelled]],
    'review' => [TaskStatus::Review, [TaskStatus::InProgress, TaskStatus::Done]],
    'done manager reopen' => [TaskStatus::Done, [TaskStatus::InProgress]],
    'cancelled manager reopen' => [TaskStatus::Cancelled, [TaskStatus::Todo]],
]);

test('mutation pilot protects every task status transition edge', function (TaskStatus $status, array $expected): void {
    $manager = User::factory()->create();
    $manager->assignRole(UserRole::ProjectManager->value);
    $workspace = app(WorkspaceService::class)->createFor($manager, 'Mutation Guard');
    app(CurrentWorkspace::class)->resolve($manager, $workspace->id);
    $project = Project::factory()->create(['workspace_id' => $workspace->id, 'owner_id' => $manager->id, 'status' => ProjectStatus::Active]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id, 'status' => $status]);

    expect(app(TaskStatusService::class)->availableStatuses($task, $manager))->toEqual($expected);
})->with('status transition mutation matrix');

test('mutation pilot protects archived project update denial', function (): void {
    $manager = User::factory()->create();
    $manager->assignRole(UserRole::ProjectManager->value);
    $workspace = app(WorkspaceService::class)->createFor($manager, 'Archived Guard');
    app(CurrentWorkspace::class)->resolve($manager, $workspace->id);
    $project = Project::factory()->create(['workspace_id' => $workspace->id, 'owner_id' => $manager->id, 'status' => ProjectStatus::Archived]);

    expect(Gate::forUser($manager)->allows('update', $project))->toBeFalse();
});
