<?php

use App\Enums\UserRole;
use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\WorkspaceMember;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Modules\Activity\Enums\ActivityEvent;
use Modules\Activity\Services\ActivityRecorder;
use Modules\Projects\Enums\ProjectMemberRole;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectMember;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Models\Task;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

test('every protected API route rejects unauthenticated requests', function (): void {
    $routes = collect(Route::getRoutes()->getRoutesByName())
        ->filter(fn ($route, string $name): bool => str_starts_with($name, 'api.v1.'))
        ->filter(fn ($route): bool => collect($route->gatherMiddleware())->contains(
            fn (string $middleware): bool => $middleware === 'auth:sanctum',
        ));

    expect($routes)->not->toBeEmpty();
    foreach ($routes as $route) {
        $method = collect($route->methods())->first(fn (string $method): bool => $method !== 'HEAD');
        $uri = '/'.preg_replace('/\{[^}]+\}/', '1', $route->uri());
        $this->json($method, $uri)->assertUnauthorized();
    }
});

test('every ability-protected API route rejects a token without its required ability', function (): void {
    $this->withoutMiddleware(SubstituteBindings::class);
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    $project = Project::factory()->create(['owner_id' => $admin->id]);
    Sanctum::actingAs($admin, ['unrelated:ability']);

    $routes = collect(Route::getRoutes()->getRoutesByName())
        ->filter(fn ($route, string $name): bool => str_starts_with($name, 'api.v1.'))
        ->filter(fn ($route): bool => collect($route->gatherMiddleware())->contains(
            fn (string $middleware): bool => str_starts_with($middleware, 'abilities:')
                || str_starts_with($middleware, 'ability:'),
        ));

    expect($routes)->not->toBeEmpty();
    foreach ($routes as $route) {
        $method = collect($route->methods())->first(fn (string $method): bool => $method !== 'HEAD');
        $uri = '/'.preg_replace('/\{[^}]+\}/', '1', $route->uri());
        $response = $this->withHeader('X-Workspace-Id', (string) $project->workspace_id)->json($method, $uri);
        $this->assertSame(403, $response->status(), (string) $route->getName());
    }
});

test('projects Web and API enforce guest member outsider validation and archived rules', function (): void {
    $manager = User::factory()->create();
    $manager->assignRole(UserRole::ProjectManager->value);
    $member = User::factory()->create();
    $member->assignRole(UserRole::Member->value);
    $outsider = User::factory()->create();
    $outsider->assignRole(UserRole::Member->value);
    $project = Project::factory()->create(['owner_id' => $manager->id]);
    ProjectMember::query()->create([
        'project_id' => $project->id,
        'user_id' => $member->id,
        'member_role' => ProjectMemberRole::Member,
        'joined_at' => now(),
    ]);
    WorkspaceMember::query()->create([
        'workspace_id' => $project->workspace_id,
        'user_id' => $outsider->id,
        'role' => WorkspaceRole::Member,
        'joined_at' => now(),
    ]);

    $this->get('/projects')->assertRedirect(route('login'));
    $this->actingAs($member)->withSession(['current_workspace_id' => $project->workspace_id])
        ->get("/projects/{$project->id}")->assertOk();
    $this->actingAs($member)->withSession(['current_workspace_id' => $project->workspace_id])
        ->get("/projects/{$project->id}/edit")->assertForbidden();
    $this->actingAs($outsider)->withSession(['current_workspace_id' => $project->workspace_id])
        ->get("/projects/{$project->id}")->assertForbidden();

    Sanctum::actingAs($manager, ['projects:write']);
    $headers = ['X-Workspace-Id' => (string) $project->workspace_id];
    $this->withHeaders($headers)->postJson('/api/v1/projects', [])->assertUnprocessable()->assertJsonValidationErrors('name');
    $created = $this->withHeaders($headers)->postJson('/api/v1/projects', ['name' => 'Accepted API Project'])
        ->assertCreated()->assertJsonPath('data.name', 'Accepted API Project');
    $this->assertDatabaseHas('projects', ['id' => $created->json('data.id'), 'name' => 'Accepted API Project']);

    $archived = Project::factory()->archived()->create(['owner_id' => $manager->id, 'workspace_id' => $project->workspace_id]);
    $this->withHeaders($headers)->putJson("/api/v1/projects/{$archived->id}", ['name' => 'Must remain archived'])
        ->assertForbidden();
});

test('activity filters remain actor scoped and nested project access is authorized', function (): void {
    $manager = User::factory()->create();
    $manager->assignRole(UserRole::ProjectManager->value);
    $otherManager = User::factory()->create();
    $otherManager->assignRole(UserRole::ProjectManager->value);
    $member = User::factory()->create();
    $member->assignRole(UserRole::Member->value);
    $project = Project::factory()->create(['owner_id' => $manager->id]);
    WorkspaceMember::query()->create([
        'workspace_id' => $project->workspace_id,
        'user_id' => $otherManager->id,
        'role' => WorkspaceRole::Manager,
        'joined_at' => now(),
    ]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id, 'assignee_id' => $member->id]);
    $recorder = app(ActivityRecorder::class);
    $recorder->record(ActivityEvent::TaskUpdated, $manager, $task, ['project_id' => $project->id, 'task_id' => $task->id]);
    $recorder->record(ActivityEvent::TaskUpdated, $member, $task, ['project_id' => $project->id, 'task_id' => $task->id]);
    $headers = ['X-Workspace-Id' => (string) $project->workspace_id];

    Sanctum::actingAs($manager, ['activity:read']);
    $response = $this->withHeaders($headers)->getJson('/api/v1/activity?actor_id='.$manager->id)
        ->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.causer_id'))->toBe($manager->id);
    $this->withHeaders($headers)->getJson('/api/v1/activity?date_from=not-a-date')
        ->assertUnprocessable()->assertJsonValidationErrors('date_from');

    Sanctum::actingAs($otherManager, ['activity:read']);
    $this->withHeaders($headers)->getJson("/api/v1/projects/{$project->id}/activity")->assertForbidden();
});

test('dashboard metrics and lists are role scoped', function (UserRole $role, int $expectedTasks): void {
    $actor = User::factory()->create();
    $actor->assignRole($role->value);
    $project = Project::factory()->create(['owner_id' => $role === UserRole::Member ? User::factory()->create()->id : $actor->id]);
    $visible = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $project->owner_id,
        'assignee_id' => $role === UserRole::Member ? $actor->id : null,
        'status' => TaskStatus::Todo,
        'due_at' => now()->subDay(),
    ]);
    Task::factory()->create(['project_id' => $project->id, 'creator_id' => $project->owner_id]);
    Sanctum::actingAs($actor, ['dashboard:read']);
    $headers = ['X-Workspace-Id' => (string) $project->workspace_id];

    $this->withHeaders($headers)->getJson('/api/v1/dashboard/summary')
        ->assertOk()->assertJsonPath('data.total_tasks', $expectedTasks);
    $this->withHeaders($headers)->getJson('/api/v1/dashboard/my-tasks')->assertOk();
    $overdue = $this->withHeaders($headers)->getJson('/api/v1/dashboard/overdue')->assertOk();
    if ($role === UserRole::Member) {
        $overdue->assertJsonPath('data.0.id', $visible->id);
    }
})->with([
    'administrator' => [UserRole::Admin, 2],
    'project manager' => [UserRole::ProjectManager, 2],
    'assigned member' => [UserRole::Member, 1],
]);
