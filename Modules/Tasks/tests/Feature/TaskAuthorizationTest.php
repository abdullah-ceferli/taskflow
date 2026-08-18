<?php

use App\Enums\UserRole;
use App\Exceptions\DomainRuleViolation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Modules\Projects\Enums\ProjectMemberRole;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectMember;
use Modules\Tasks\Data\CreateTaskData;
use Modules\Tasks\Data\TaskFiltersData;
use Modules\Tasks\Enums\TaskPriority;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;
use Modules\Tasks\Services\TaskService;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function taskUser(UserRole $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role->value);

    return $user;
}

test('members only see tasks assigned to them', function (): void {
    $owner = taskUser(UserRole::ProjectManager);
    $member = taskUser(UserRole::Member);
    $otherMember = taskUser(UserRole::Member);
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    $visible = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id, 'assignee_id' => $member->id]);
    $hidden = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $owner->id, 'assignee_id' => $otherMember->id]);

    $ids = app(TaskRepositoryInterface::class)->paginateFor($member, TaskFiltersData::fromArray([]))->pluck('id')->all();

    expect($ids)->toContain($visible->id)->not->toContain($hidden->id);
    expect(Gate::forUser($member)->allows('view', $hidden))->toBeFalse();
});

test('task creation rejects archived projects and foreign assignees', function (): void {
    $manager = taskUser(UserRole::ProjectManager);
    $outsider = taskUser(UserRole::Member);
    $activeProject = Project::factory()->create(['owner_id' => $manager->id]);
    $archivedProject = Project::factory()->create(['owner_id' => $manager->id, 'status' => ProjectStatus::Archived]);
    $data = new CreateTaskData($activeProject->id, 'Valid title', null, $outsider->id, TaskPriority::Medium, null);

    expect(fn () => app(TaskService::class)->create($manager, $activeProject->id, $data))
        ->toThrow(DomainRuleViolation::class, 'assignee must be a project member');

    ProjectMember::query()->create([
        'project_id' => $activeProject->id,
        'user_id' => $outsider->id,
        'member_role' => ProjectMemberRole::Member,
        'joined_at' => now(),
    ]);

    $archivedData = new CreateTaskData($archivedProject->id, 'Blocked title', null, null, TaskPriority::Medium, null);

    expect(fn () => app(TaskService::class)->create($manager, $archivedProject->id, $archivedData))
        ->toThrow(DomainRuleViolation::class, 'active projects');
});
