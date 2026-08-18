<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Cache;
use Modules\Projects\Models\Project;
use Modules\Tasks\Enums\TaskPriority;
use Modules\Tasks\Models\Task;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

test('an administrator can render every main workspace page', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);

    $this->actingAs($admin)->get('/dashboard')->assertOk();
    $this->actingAs($admin)->get('/projects')->assertOk();
    $this->actingAs($admin)->get('/tasks')->assertOk();
    $this->actingAs($admin)->get('/activity')->assertOk();
});

test('dashboard renders assigned task models before and after summary caching', function (): void {
    Cache::setDefaultDriver('database');
    Cache::store('database')->flush();
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    $project = Project::factory()->create(['owner_id' => $admin->id]);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $admin->id,
        'assignee_id' => $admin->id,
        'priority' => TaskPriority::High,
        'title' => 'Cached dashboard task',
    ]);

    $this->actingAs($admin)->withSession(['current_workspace_id' => $project->workspace_id])
        ->get('/dashboard')->assertOk()->assertSee($task->title);
    $this->actingAs($admin)->withSession(['current_workspace_id' => $project->workspace_id])
        ->get('/dashboard')->assertOk()->assertSee($task->title);
});
