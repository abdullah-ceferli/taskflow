<?php

declare(strict_types=1);

namespace Modules\Activity\Providers;

use App\Http\Middleware\ResolveCurrentWorkspace;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Activity\Listeners\RecordDomainActivity;
use Modules\Activity\Policies\ActivityPolicy;
use Modules\Projects\Events\ProjectMemberAdded;
use Modules\Tasks\Events\TaskAssigned;
use Modules\Tasks\Events\TaskCreated;
use Modules\Tasks\Events\TaskStatusChanged;
use Spatie\Activitylog\Models\Activity;

class ActivityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(module_path('Activity', 'routes/web.php'));

        Route::middleware(['api', 'auth:sanctum', ResolveCurrentWorkspace::class])
            ->prefix('api/v1')
            ->as('api.v1.')
            ->group(module_path('Activity', 'routes/api.php'));

        $this->loadViewsFrom(module_path('Activity', 'resources/views'), 'activity');
        $this->loadMigrationsFrom(module_path('Activity', 'database/migrations'));

        Gate::policy(Activity::class, ActivityPolicy::class);

        Event::listen(TaskCreated::class, RecordDomainActivity::class);
        Event::listen(TaskAssigned::class, RecordDomainActivity::class);
        Event::listen(TaskStatusChanged::class, RecordDomainActivity::class);
        Event::listen(ProjectMemberAdded::class, RecordDomainActivity::class);
    }
}
