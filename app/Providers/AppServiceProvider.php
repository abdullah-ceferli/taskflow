<?php

namespace App\Providers;

use App\Contracts\ExceptionTrackerInterface;
use App\Contracts\GlobalSearchInterface;
use App\Enums\PermissionName;
use App\Models\User;
use App\Services\CurrentWorkspace;
use App\Services\DatabaseGlobalSearch;
use App\Services\LogExceptionTracker;
use App\Services\QueryTelemetry;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(CurrentWorkspace::class);
        $this->app->scoped(QueryTelemetry::class);
        $this->app->bind(GlobalSearchInterface::class, DatabaseGlobalSearch::class);
        $this->app->bind(ExceptionTrackerInterface::class, LogExceptionTracker::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewDashboard', fn (User $user): bool => $user->hasPermissionTo(PermissionName::DashboardView->value));

        Queue::failing(function (JobFailed $event): void {
            Log::channel('structured')->error('taskflow.queue_job_failed', [
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue(),
                'job' => $event->job->resolveName(),
                'exception_class' => $event->exception::class,
            ]);
        });

        if (config('taskflow.performance.telemetry_enabled', true)) {
            DB::listen(function (QueryExecuted $query): void {
                app(QueryTelemetry::class)->record($query);
                if ($query->time >= (float) config('taskflow.performance.slow_query_ms', 250)) {
                    Log::warning('taskflow.slow_query', [
                        'connection' => $query->connectionName,
                        'duration_ms' => $query->time,
                        'sql' => $query->sql,
                    ]);
                }
            });
        }
    }
}
