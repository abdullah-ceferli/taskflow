<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Sanctum\Sanctum;
use Modules\Projects\Models\Project;
use Modules\Tasks\Enums\TaskPriority;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function apiUser(UserRole $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role->value);

    return $user;
}

test('protected API endpoints reject unauthenticated requests and tokens without the required ability', function (): void {
    $this->getJson('/api/v1/projects')->assertUnauthorized();

    $member = apiUser(UserRole::Member);
    Sanctum::actingAs($member, ['tasks:read']);

    $this->getJson('/api/v1/projects')->assertForbidden();
});

test('token endpoint creates a usable token without storing plaintext token value', function (): void {
    $user = apiUser(UserRole::Member);

    $response = $this->postJson('/api/v1/auth/token', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Pest test device',
    ])->assertCreated()->assertJsonPath('data.abilities.0', 'projects:read');

    $token = $response->json('data.token');

    expect($token)->toBeString()->not->toBeEmpty();
    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->assertDatabaseMissing('personal_access_tokens', ['token' => $token]);

    $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.email', $user->email);
});

test('tasks API returns validation errors and creates tasks for an authorized token', function (): void {
    $admin = apiUser(UserRole::Admin);
    $project = Project::factory()->create(['owner_id' => $admin->id]);
    Sanctum::actingAs($admin, ['tasks:write', 'tasks:read']);

    $this->postJson('/api/v1/tasks', [])->assertUnprocessable()->assertJsonValidationErrors(['project_id', 'title', 'priority']);

    $this->postJson('/api/v1/tasks', [
        'project_id' => $project->id,
        'title' => 'Create release notes',
        'priority' => TaskPriority::High->value,
    ])->assertCreated()->assertJsonPath('data.project_id', $project->id)->assertJsonPath('data.title', 'Create release notes');
});

test('tasks API enforces the pagination cap', function (): void {
    $admin = apiUser(UserRole::Admin);
    Sanctum::actingAs($admin, ['tasks:read']);

    $this->getJson('/api/v1/tasks?per_page=101')->assertUnprocessable()->assertJsonValidationErrors('per_page');
});
