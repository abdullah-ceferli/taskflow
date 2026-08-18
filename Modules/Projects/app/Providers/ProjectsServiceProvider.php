<?php

declare(strict_types=1);

namespace Modules\Projects\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Projects\Models\Project;
use Modules\Projects\Policies\ProjectPolicy;
use Modules\Projects\Repositories\Contracts\ProjectRepositoryInterface;
use Modules\Projects\Repositories\Eloquent\EloquentProjectRepository;

class ProjectsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProjectRepositoryInterface::class, EloquentProjectRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(module_path('Projects', 'routes/web.php'));

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/v1')
            ->as('api.v1.')
            ->group(module_path('Projects', 'routes/api.php'));

        $this->loadViewsFrom(module_path('Projects', 'resources/views'), 'projects');
        $this->loadMigrationsFrom(module_path('Projects', 'database/migrations'));

        Gate::policy(Project::class, ProjectPolicy::class);
    }
}
