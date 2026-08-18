<?php

declare(strict_types=1);

namespace Modules\Dashboard\Providers;

use App\Http\Middleware\ResolveCurrentWorkspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Dashboard\Services\DashboardCache;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectMember;
use Modules\Tasks\Models\Task;
use Spatie\Activitylog\Models\Activity;

class DashboardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(module_path('Dashboard', 'routes/web.php'));

        Route::middleware(['api', 'auth:sanctum', ResolveCurrentWorkspace::class])
            ->prefix('api/v1')
            ->as('api.v1.')
            ->group(module_path('Dashboard', 'routes/api.php'));

        $this->loadViewsFrom(module_path('Dashboard', 'resources/views'), 'dashboard');
        $this->loadMigrationsFrom(module_path('Dashboard', 'database/migrations'));

        $invalidate = fn (?int $workspaceId) => app(DashboardCache::class)->invalidate($workspaceId);
        Project::saved(fn (Project $project) => $invalidate($project->workspace_id));
        Project::deleted(fn (Project $project) => $invalidate($project->workspace_id));
        Task::saved(fn (Task $task) => $invalidate((int) Project::query()->withTrashed()->whereKey($task->project_id)->value('workspace_id')));
        Task::deleted(fn (Task $task) => $invalidate((int) Project::query()->withTrashed()->whereKey($task->project_id)->value('workspace_id')));
        ProjectMember::saved(fn (ProjectMember $member) => $invalidate((int) Project::query()->withTrashed()->whereKey($member->project_id)->value('workspace_id')));
        ProjectMember::deleted(fn (ProjectMember $member) => $invalidate((int) Project::query()->withTrashed()->whereKey($member->project_id)->value('workspace_id')));
        WorkspaceMember::saved(fn (WorkspaceMember $member) => $invalidate($member->workspace_id));
        WorkspaceMember::deleted(fn (WorkspaceMember $member) => $invalidate($member->workspace_id));
        Activity::created(fn (Activity $activity) => $invalidate((int) $activity->properties->get('workspace_id')));
    }
}
