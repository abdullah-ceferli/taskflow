<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Sanctum\Sanctum;
use Modules\Projects\Models\Project;
use Modules\Tasks\Models\Task;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function contractUser(): User
{
    $user = User::factory()->create();
    $user->assignRole(UserRole::Admin->value);

    return $user;
}

test('projects collection retains its documented resource envelope and fields', function (): void {
    $user = contractUser();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    Sanctum::actingAs($user, ['projects:read']);

    $this->getJson('/api/v1/projects')
        ->assertOk()
        ->assertJsonStructure(['data' => [[
            'id', 'name', 'slug', 'description', 'status', 'owner_id',
            'starts_at', 'due_at', 'created_at', 'updated_at',
        ]], 'links', 'meta'])
        ->assertJsonPath('data.0.id', $project->id)
        ->assertJsonMissingPath('data.0.password');
});

test('task resource retains its documented date and ownership fields', function (): void {
    $user = contractUser();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $user->id,
    ]);
    Sanctum::actingAs($user, ['tasks:read']);

    $this->getJson("/api/v1/tasks/{$task->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => [
            'id', 'number', 'project_id', 'creator_id', 'assignee_id',
            'title', 'description', 'status', 'priority', 'due_at',
            'started_at', 'completed_at', 'created_at', 'updated_at',
        ]])
        ->assertJsonPath('data.id', $task->id)
        ->assertJsonPath('data.project_id', $project->id)
        ->assertJsonMissingPath('data.password');
});
