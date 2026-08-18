<?php

declare(strict_types=1);

namespace Modules\Tasks\Providers;

use App\Http\Middleware\ResolveCurrentWorkspace;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Tasks\Console\Commands\DispatchRecurringTasks;
use Modules\Tasks\Contracts\MalwareScannerInterface;
use Modules\Tasks\Contracts\TaskMetricsInterface;
use Modules\Tasks\Livewire\QuickTaskCreate;
use Modules\Tasks\Livewire\TaskCommentForm;
use Modules\Tasks\Livewire\TaskFilters;
use Modules\Tasks\Livewire\TaskStatusSelector;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAttachment;
use Modules\Tasks\Models\TaskComment;
use Modules\Tasks\Policies\TaskAttachmentPolicy;
use Modules\Tasks\Policies\TaskCommentPolicy;
use Modules\Tasks\Policies\TaskPolicy;
use Modules\Tasks\Repositories\Contracts\TaskAttachmentRepositoryInterface;
use Modules\Tasks\Repositories\Contracts\TaskCommentRepositoryInterface;
use Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;
use Modules\Tasks\Repositories\Eloquent\EloquentTaskAttachmentRepository;
use Modules\Tasks\Repositories\Eloquent\EloquentTaskCommentRepository;
use Modules\Tasks\Repositories\Eloquent\EloquentTaskRepository;
use Modules\Tasks\Services\EloquentTaskMetrics;
use Modules\Tasks\Services\NoopMalwareScanner;

class TasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TaskRepositoryInterface::class, EloquentTaskRepository::class);
        $this->app->bind(TaskCommentRepositoryInterface::class, EloquentTaskCommentRepository::class);
        $this->app->bind(TaskAttachmentRepositoryInterface::class, EloquentTaskAttachmentRepository::class);
        $this->app->bind(TaskMetricsInterface::class, EloquentTaskMetrics::class);
        $this->app->bind(MalwareScannerInterface::class, NoopMalwareScanner::class);
    }

    public function boot(): void
    {
        $this->commands([DispatchRecurringTasks::class]);
        $this->loadRoutesFrom(module_path('Tasks', 'routes/web.php'));

        Route::middleware(['api', 'auth:sanctum', ResolveCurrentWorkspace::class])
            ->prefix('api/v1')
            ->as('api.v1.')
            ->group(module_path('Tasks', 'routes/api.php'));

        $this->loadViewsFrom(module_path('Tasks', 'resources/views'), 'tasks');
        $this->loadMigrationsFrom(module_path('Tasks', 'database/migrations'));

        Livewire::component('tasks.task-filters', TaskFilters::class);
        Livewire::component('tasks.task-status-selector', TaskStatusSelector::class);
        Livewire::component('tasks.task-comment-form', TaskCommentForm::class);
        Livewire::component('tasks.quick-task-create', QuickTaskCreate::class);

        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(TaskComment::class, TaskCommentPolicy::class);
        Gate::policy(TaskAttachment::class, TaskAttachmentPolicy::class);
    }
}
