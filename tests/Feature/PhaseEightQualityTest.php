<?php

use App\Enums\TokenAbility;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\CurrentWorkspace;
use App\Services\WorkspaceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Modules\Projects\Models\Project;
use Modules\Tasks\Models\Task;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

dataset('task policy matrix', [
    'admin can manage workspace task' => [UserRole::Admin, true, true, true],
    'project manager owner can manage task' => [UserRole::ProjectManager, true, true, true],
    'assigned member can view but not update' => [UserRole::Member, false, true, false],
    'unassigned member cannot view or update' => [UserRole::Member, false, false, false],
]);

test('role and record policy matrix remains explicit', function (UserRole $role, bool $owner, bool $assigned, bool $mayUpdate): void {
    $actor = User::factory()->create();
    $actor->assignRole($role->value);
    $workspace = app(WorkspaceService::class)->createFor($actor, 'Policy Matrix');
    app(CurrentWorkspace::class)->resolve($actor, $workspace->id);
    $project = Project::factory()->create(['workspace_id' => $workspace->id, 'owner_id' => $owner ? $actor->id : User::factory()->create()->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $project->owner_id, 'assignee_id' => $assigned ? $actor->id : null]);

    expect(Gate::forUser($actor)->allows('view', $task))->toBe($assigned || $owner || $role === UserRole::Admin)
        ->and(Gate::forUser($actor)->allows('update', $task))->toBe($mayUpdate);
})->with('task policy matrix');

dataset('api ability matrix', [
    'projects read token' => [TokenAbility::ProjectsRead, '/api/v1/projects', 200],
    'tasks read cannot list projects' => [TokenAbility::TasksRead, '/api/v1/projects', 403],
    'dashboard read token' => [TokenAbility::DashboardRead, '/api/v1/dashboard/summary', 200],
]);

test('token abilities never bypass route and record authorization', function (TokenAbility $ability, string $uri, int $status): void {
    $actor = User::factory()->create();
    $actor->assignRole(UserRole::Admin->value);
    $workspace = app(WorkspaceService::class)->createFor($actor, 'Ability Matrix');
    Sanctum::actingAs($actor, [$ability->value]);

    $this->withHeader('X-Workspace-Id', (string) $workspace->id)->getJson($uri)->assertStatus($status);
})->with('api ability matrix');

test('guest authentication pages retain stable accessibility landmarks', function (string $route, array $labels): void {
    $response = $this->get(route($route))->assertOk()->assertSee('<main', false)->assertSee('<form', false);
    foreach ($labels as $label) {
        $response->assertSee('for="'.$label.'"', false)->assertSee('id="'.$label.'"', false);
    }
})->with([
    'login smoke' => ['login', ['email', 'password']],
    'registration smoke' => ['register', ['name', 'email', 'password', 'password_confirmation']],
]);
