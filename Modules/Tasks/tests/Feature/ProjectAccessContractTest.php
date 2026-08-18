<?php

use App\Enums\UserRole;
use App\Exceptions\DomainRuleViolation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Projects\Contracts\ProjectAccessInterface;
use Modules\Projects\Data\ProjectAccessData;
use Modules\Projects\Models\Project;
use Modules\Tasks\Data\CreateTaskData;
use Modules\Tasks\Enums\TaskPriority;
use Modules\Tasks\Services\TaskService;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

test('task creation depends on the projects access contract', function (): void {
    $actor = User::factory()->create();
    $actor->assignRole(UserRole::ProjectManager->value);
    $project = Project::factory()->create(['owner_id' => $actor->id]);

    app()->instance(ProjectAccessInterface::class, new class implements ProjectAccessInterface
    {
        public function forActor(int $projectId, User $actor): ProjectAccessData
        {
            return new ProjectAccessData($projectId, $actor->id, active: true, member: true, manager: true);
        }
    });

    $task = app(TaskService::class)->create($actor, $project->id, new CreateTaskData(
        projectId: $project->id,
        title: 'Contract-backed task',
        description: null,
        assigneeId: null,
        priority: TaskPriority::Medium,
        dueAt: null,
    ));

    expect($task->project_id)->toBe($project->id);
});

test('task creation rejects an inactive access contract result', function (): void {
    $actor = User::factory()->create();
    $actor->assignRole(UserRole::ProjectManager->value);
    $project = Project::factory()->create(['owner_id' => $actor->id]);

    app()->instance(ProjectAccessInterface::class, new class implements ProjectAccessInterface
    {
        public function forActor(int $projectId, User $actor): ProjectAccessData
        {
            return new ProjectAccessData($projectId, $actor->id, active: false, member: true, manager: true);
        }
    });

    expect(fn () => app(TaskService::class)->create($actor, $project->id, new CreateTaskData(
        projectId: $project->id,
        title: 'Blocked task',
        description: null,
        assigneeId: null,
        priority: TaskPriority::Medium,
        dueAt: null,
    )))->toThrow(DomainRuleViolation::class, 'Tasks can only be created in active projects.');
});
