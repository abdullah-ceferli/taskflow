<?php

use App\Enums\UserRole;
use App\Jobs\ArchiveActivityLog;
use App\Jobs\PruneExpiredReportExportsJob;
use App\Jobs\PruneOrphanTaskAttachmentsJob;
use App\Models\User;
use App\Services\CurrentWorkspace;
use App\Services\PerformanceTelemetry;
use App\Services\WorkspaceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Modules\Activity\Enums\ActivityEvent;
use Modules\Activity\Services\ActivityRecorder;
use Modules\Dashboard\Services\DashboardService;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;
use Modules\Tasks\Models\Task;
use Spatie\Activitylog\Models\Activity;

beforeEach(function (): void {
    Cache::flush();
    Storage::fake('local');
    $this->seed(RolePermissionSeeder::class);
});

function performanceContext(): array
{
    $manager = User::factory()->create();
    $manager->assignRole(UserRole::ProjectManager->value);
    $workspace = app(WorkspaceService::class)->createFor($manager, 'Performance Workspace');
    app(CurrentWorkspace::class)->resolve($manager, $workspace->id);
    $project = Project::factory()->create(['workspace_id' => $workspace->id, 'owner_id' => $manager->id, 'status' => ProjectStatus::Active]);

    return [$manager, $workspace, $project];
}

test('performance telemetry calculates scoped p50 p95 error rate and query baseline', function (): void {
    $telemetry = app(PerformanceTelemetry::class);
    foreach ([[10, 200, 3], [20, 200, 4], [30, 500, 5], [100, 200, 4]] as [$duration, $status, $queries]) {
        $telemetry->record('tasks.index', 10, 20, $duration, $status, ['count' => $queries, 'total_ms' => 1.0, 'slow_count' => 0]);
    }

    $report = $telemetry->report()[0];
    expect($report)->toMatchArray([
        'route' => 'tasks.index',
        'workspace_id' => 10,
        'actor_id' => 20,
        'samples' => 4,
        'p50_ms' => 20.0,
        'p95_ms' => 100.0,
        'error_rate' => 25.0,
        'average_queries' => 4.0,
    ]);
});

test('dashboard read model is cached per actor and invalidated by workspace writes', function (): void {
    [$manager, , $project] = performanceContext();
    Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id]);
    $dashboard = app(DashboardService::class);

    $queries = 0;
    DB::listen(function () use (&$queries): void {
        $queries++;
    });
    $first = $dashboard->summary($manager);
    $firstQueryCount = $queries;
    $queries = 0;
    $cached = $dashboard->summary($manager);
    $cachedQueryCount = $queries;

    expect($first['totalTasks'])->toBe(1)
        ->and($cached['totalTasks'])->toBe(1)
        ->and($firstQueryCount)->toBeLessThanOrEqual(12)
        ->and($cachedQueryCount)->toBeLessThanOrEqual(4);

    Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id]);
    expect($dashboard->summary($manager)['totalTasks'])->toBe(2);
});

test('activity retention archives privately before deleting old rows', function (): void {
    [$manager, , $project] = performanceContext();
    $task = Task::factory()->create(['project_id' => $project->id, 'creator_id' => $manager->id]);
    app(ActivityRecorder::class)->record(ActivityEvent::TaskUpdated, $manager, $task, ['project_id' => $project->id, 'task_id' => $task->id]);
    $old = Activity::query()->latest('id')->firstOrFail();
    $old->forceFill(['created_at' => now()->subYears(2), 'updated_at' => now()->subYears(2)])->saveQuietly();
    app(ActivityRecorder::class)->record(ActivityEvent::TaskUpdated, $manager, $task, ['project_id' => $project->id, 'task_id' => $task->id]);
    $recentId = Activity::query()->latest('id')->value('id');

    (new ArchiveActivityLog(now()->subYear()->toDateTimeImmutable()))->handle();

    expect(Activity::query()->whereKey($old->id)->exists())->toBeFalse()
        ->and(Activity::query()->whereKey($recentId)->exists())->toBeTrue();
    expect(Storage::disk('local')->allFiles('activity-archive'))->toHaveCount(1);
});

test('scheduled heavy cleanup and retention work is queue compatible', function (): void {
    Queue::fake();

    PruneOrphanTaskAttachmentsJob::dispatch();
    PruneExpiredReportExportsJob::dispatch();
    ArchiveActivityLog::dispatch(now()->subYear()->toDateTimeImmutable());

    Queue::assertPushed(PruneOrphanTaskAttachmentsJob::class, 1);
    Queue::assertPushed(PruneExpiredReportExportsJob::class, 1);
    Queue::assertPushed(ArchiveActivityLog::class, 1);
});
