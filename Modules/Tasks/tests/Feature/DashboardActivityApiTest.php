<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Modules\Projects\Models\Project;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Livewire\TaskStatusSelector;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskStatusService;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function dashboardAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole(UserRole::Admin->value);

    return $user;
}

test('task status selector changes status through the status service', function (): void {
    $admin = dashboardAdmin();
    $project = Project::factory()->create(['owner_id' => $admin->id]);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $admin->id,
        'status' => TaskStatus::Todo,
    ]);

    $this->actingAs($admin);
    Livewire::test(TaskStatusSelector::class, ['task' => $task])
        ->set('status', TaskStatus::InProgress->value)
        ->call('change');

    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});

test('dashboard and activity API endpoints enforce ability and return data for an administrator', function (): void {
    $admin = dashboardAdmin();
    $project = Project::factory()->create(['owner_id' => $admin->id]);
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $admin->id]);
    app(TaskStatusService::class)->change($task->load('project'), TaskStatus::InProgress, $admin);

    Sanctum::actingAs($admin, ['dashboard:read', 'activity:read']);

    $this->getJson('/api/v1/dashboard/summary')->assertOk()->assertJsonStructure(['data']);
    $this->getJson('/api/v1/activity')->assertOk()->assertJsonPath('data.0.event', 'task.status_changed');

    Sanctum::actingAs($admin, ['tasks:read']);
    $this->getJson('/api/v1/dashboard/summary')->assertForbidden();
});
