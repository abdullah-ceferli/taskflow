<?php

use App\Enums\UserRole;
use App\Exceptions\DomainRuleViolation;
use App\Exceptions\OptimisticLockConflict;
use App\Models\User;
use App\Services\CurrentWorkspace;
use App\Services\WorkspaceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\DB;
use Modules\Projects\Models\Project;
use Modules\Projects\Services\ProjectMilestoneService;
use Modules\Tasks\Data\CreateRecurringTaskData;
use Modules\Tasks\Enums\RecurrenceFrequency;
use Modules\Tasks\Enums\TaskPriority;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Services\EloquentTaskMetrics;
use Modules\Tasks\Services\RecurringTaskService;
use Modules\Tasks\Services\TaskBoardService;
use Modules\Tasks\Services\TaskDependencyService;
use Modules\Tasks\Services\TaskStatusService;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function planningContext(): array
{
    $manager = User::factory()->create();
    $manager->assignRole(UserRole::ProjectManager->value);
    $workspace = app(WorkspaceService::class)->createFor($manager, 'Planning Workspace');
    app(CurrentWorkspace::class)->resolve($manager, $workspace->id);
    $project = Project::factory()->create(['workspace_id' => $workspace->id, 'owner_id' => $manager->id]);

    return [$manager, $workspace, $project];
}

test('board read model stays scoped and stale status writes return a conflict audit', function (): void {
    [$manager, $workspace, $project] = planningContext();
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id]);
    $originalTimestamp = $task->updated_at->toISOString();
    $task->forceFill(['updated_at' => now()->addMinute()])->saveQuietly();

    expect(fn () => app(TaskStatusService::class)->change($task, TaskStatus::InProgress, $manager, $originalTimestamp))
        ->toThrow(OptimisticLockConflict::class);
    $this->assertDatabaseHas('activity_log', ['event' => 'task.board_conflict', 'subject_id' => $task->id]);

    $otherManager = User::factory()->create();
    $otherManager->assignRole(UserRole::ProjectManager->value);
    $otherWorkspace = app(WorkspaceService::class)->createFor($otherManager, 'Foreign Planning');
    $foreignProject = Project::factory()->create(['workspace_id' => $otherWorkspace->id, 'owner_id' => $otherManager->id]);
    $foreign = Task::factory()->create(['project_id' => $foreignProject->id, 'creator_id' => $otherManager->id]);
    $boardIds = app(TaskBoardService::class)->forProject($manager, $project->id)->flatMap(fn (array $column) => $column['tasks'])->pluck('id');

    expect($boardIds)->toContain($task->id)->not->toContain($foreign->id);
    $this->actingAs($manager)->withSession(['current_workspace_id' => $workspace->id])->get(route('projects.board', $project))->assertOk()->assertSee($task->title);
});

test('dependency cycles are rejected and unfinished dependencies block active work', function (): void {
    [$manager, , $project] = planningContext();
    $a = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id]);
    $b = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id]);
    $c = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id]);
    $dependencies = app(TaskDependencyService::class);
    $dependencies->add($a, $b, $manager);
    $dependencies->add($b, $c, $manager);

    expect(fn () => $dependencies->add($c, $a, $manager))->toThrow(DomainRuleViolation::class, 'cycle');
    expect(app(TaskStatusService::class)->availableStatuses($a, $manager))->toEqual([TaskStatus::Cancelled]);
    expect(fn () => app(TaskStatusService::class)->change($a, TaskStatus::InProgress, $manager))
        ->toThrow(DomainRuleViolation::class);

    $b->update(['status' => TaskStatus::Done, 'completed_at' => now()]);
    expect(app(TaskStatusService::class)->availableStatuses($a->fresh(), $manager))->toContain(TaskStatus::InProgress);
});

test('recurring generation is idempotent and template edits are not retroactive', function (): void {
    [$manager, , $project] = planningContext();
    $recurring = app(RecurringTaskService::class)->create($project, $manager, new CreateRecurringTaskData(
        'Weekly planning review',
        'Immutable occurrence snapshot',
        null,
        null,
        TaskPriority::High,
        2.5,
        RecurrenceFrequency::Weekly,
        1,
        'Asia/Baku',
        1,
        now()->subMinute()->toDateTimeImmutable(),
    ));

    $first = app(RecurringTaskService::class)->generate($recurring->id);
    $second = app(RecurringTaskService::class)->generate($recurring->id);
    $recurring->update(['title' => 'Changed template']);

    expect($first)->not->toBeNull()
        ->and($second)->toBeNull()
        ->and($first->fresh()->title)->toBe('Weekly planning review');
    $generatedDefinition = $recurring->fresh();
    expect($generatedDefinition->next_run_at->greaterThan($generatedDefinition->last_generated_at))->toBeTrue();
    $this->assertDatabaseCount('recurring_task_occurrences', 1);
    $this->assertDatabaseCount('tasks', 1);
});

test('milestone progress risk and workload use aggregate queries', function (): void {
    [$manager, , $project] = planningContext();
    $milestone = app(ProjectMilestoneService::class)->create($project, $manager, 'Launch', null, today()->subDay()->toDateString());
    Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id, 'assignee_id' => $manager->id, 'milestone_id' => $milestone->id, 'estimate_hours' => 20, 'status' => TaskStatus::Todo]);
    Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id, 'assignee_id' => $manager->id, 'milestone_id' => $milestone->id, 'estimate_hours' => 10, 'status' => TaskStatus::Done, 'completed_at' => now()]);

    $milestone = app(ProjectMilestoneService::class)->forProject($project)->first();
    expect($milestone->progress)->toBe(50)->and($milestone->at_risk)->toBeTrue();

    $queries = 0;
    DB::listen(function () use (&$queries): void {
        $queries++;
    });
    $workload = app(EloquentTaskMetrics::class)->workloadFor($manager)->firstWhere('user.id', $manager->id);
    expect($workload->allocated_hours)->toBe(20.0)
        ->and($workload->utilization)->toBe(50)
        ->and($queries)->toBeLessThanOrEqual(4);
});
