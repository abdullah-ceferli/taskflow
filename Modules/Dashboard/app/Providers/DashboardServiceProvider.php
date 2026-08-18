<?php

declare(strict_types=1);

namespace Modules\Dashboard\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(module_path('Dashboard', 'routes/web.php'));

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/v1')
            ->as('api.v1.')
            ->group(module_path('Dashboard', 'routes/api.php'));

        $this->loadViewsFrom(module_path('Dashboard', 'resources/views'), 'dashboard');
        $this->loadMigrationsFrom(module_path('Dashboard', 'database/migrations'));
    }
}
