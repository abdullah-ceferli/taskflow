<?php

namespace Modules\Projects\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Projects\Repositories\Contracts\ProjectRepositoryInterface;
use Modules\Projects\Repositories\Eloquent\EloquentProjectRepository;
use Modules\Projects\Repositories\Contracts\ProjectMemberRepositoryInterface;
use Modules\Projects\Repositories\Eloquent\EloquentProjectMemberRepository;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectMember;
use Modules\Projects\Policies\ProjectPolicy;
use Modules\Projects\Policies\ProjectMemberPolicy;

class ProjectsServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Projects';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'projects';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Register module bindings
     */
    public function register(): void
    {
        // Bind repository interfaces to their implementations
        $this->app->bind(
            ProjectRepositoryInterface::class,
            EloquentProjectRepository::class,
        );

        $this->app->bind(
            ProjectMemberRepositoryInterface::class,
            EloquentProjectMemberRepository::class,
        );
    }

    /**
     * Boot module services
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(ProjectMember::class, ProjectMemberPolicy::class);
    }

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
