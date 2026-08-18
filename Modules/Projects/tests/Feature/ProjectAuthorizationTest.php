<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Modules\Projects\Enums\ProjectMemberRole;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectMember;
use Modules\Projects\Repositories\Contracts\ProjectRepositoryInterface;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function projectUser(UserRole $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role->value);

    return $user;
}

test('project visibility is restricted to owned and joined projects', function (): void {
    $owner = projectUser(UserRole::ProjectManager);
    $member = projectUser(UserRole::Member);
    $outsider = projectUser(UserRole::Member);

    $visibleProject = Project::factory()->create(['owner_id' => $owner->id]);
    $hiddenProject = Project::factory()->create(['owner_id' => $owner->id]);
    ProjectMember::query()->create([
        'project_id' => $visibleProject->id,
        'user_id' => $member->id,
        'member_role' => ProjectMemberRole::Member,
        'joined_at' => now(),
    ]);

    $ids = app(ProjectRepositoryInterface::class)->paginateFor($member, null, null)->pluck('id')->all();

    expect($ids)->toContain($visibleProject->id)->not->toContain($hiddenProject->id);
    expect(Gate::forUser($member)->allows('view', $hiddenProject))->toBeFalse();
    expect(Gate::forUser($outsider)->allows('view', $visibleProject))->toBeFalse();
});

test('an archived project cannot be updated even by its owner', function (): void {
    $owner = projectUser(UserRole::ProjectManager);
    $project = Project::factory()->archived()->create(['owner_id' => $owner->id]);

    expect(Gate::forUser($owner)->allows('update', $project))->toBeFalse();
});
