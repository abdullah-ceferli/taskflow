<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Projects\Models\Project;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Events\TaskStatusChanged;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\TaskStatusService;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

test('domain activity listener is idempotent for a repeated event id', function (): void {
    $manager = User::factory()->create();
    $manager->assignRole(UserRole::ProjectManager->value);
    $project = Project::factory()->create(['owner_id' => $manager->id]);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $manager->id,
        'status' => TaskStatus::Todo,
    ]);
    $event = new TaskStatusChanged($manager, $task, [
        'project_id' => $project->id,
        'task_id' => $task->id,
        'old' => TaskStatus::Todo->value,
        'new' => TaskStatus::InProgress->value,
    ], 'test-idempotency-key');

    event($event);
    event($event);

    expect(Activity::query()->where('properties->event_id', 'test-idempotency-key')->count())->toBe(1);
});

test('task status changes record safe old and new audit values', function (): void {
    $manager = User::factory()->create();
    $manager->assignRole(UserRole::ProjectManager->value);
    $project = Project::factory()->create(['owner_id' => $manager->id]);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'creator_id' => $manager->id,
        'status' => TaskStatus::Todo,
    ]);

    app(TaskStatusService::class)->change($task->load('project'), TaskStatus::InProgress, $manager);

    $activity = Activity::query()->where('event', 'task.status_changed')->firstOrFail();

    expect($activity->properties->get('old'))->toBe(TaskStatus::Todo->value)
        ->and($activity->properties->get('new'))->toBe(TaskStatus::InProgress->value)
        ->and($activity->properties->all())->not->toHaveKey('password')
        ->and($activity->properties->all())->not->toHaveKey('token');
});
