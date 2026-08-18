<?php

namespace Modules\Dashboard\Services;

use App\Models\User;
use Modules\Activity\Services\ActivityQueryService;
use Modules\Projects\Contracts\ProjectMetricsInterface;
use Modules\Tasks\Contracts\TaskMetricsInterface;

class DashboardService
{
    public function __construct(
        private readonly ActivityQueryService $activity,
        private readonly ProjectMetricsInterface $projects,
        private readonly TaskMetricsInterface $tasks,
        private readonly DashboardCache $cache,
    ) {}

    public function summary(User $user): array
    {
        $metrics = $this->cache->remember($user, function () use ($user): array {
            $projects = $this->projects->countsFor($user);
            $tasks = $this->tasks->countsFor($user);

            return [
                'activeProjects' => $projects['active'],
                'archivedProjects' => $projects['archived'],
                'totalTasks' => $tasks['total'],
                'todo' => $tasks['todo'],
                'inProgress' => $tasks['in_progress'],
                'review' => $tasks['review'],
                'overdue' => $tasks['overdue'],
                'completedToday' => $tasks['completed_today'],
                'workload' => $this->tasks->workloadFor($user)->map(fn (object $row): array => [
                    'user_id' => $row->user->id,
                    'name' => $row->user->name,
                    'email' => $row->user->email,
                    'membership_id' => $row->membership_id,
                    'capacity_hours' => $row->capacity_hours,
                    'allocated_hours' => $row->allocated_hours,
                    'open_tasks' => $row->open_tasks,
                    'utilization' => $row->utilization,
                ])->values()->all(),
                'projectDistribution' => $this->projects->distributionFor($user)->map(fn (object $project): array => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'tasks_count' => $project->tasks_count,
                ])->values()->all(),
            ];
        });

        return [
            ...$metrics,
            'myTasks' => $this->tasks->myTasks($user),
            'workload' => collect($metrics['workload']),
            'recentActivity' => $this->activity->recentForUser($user, 8),
            'projectDistribution' => collect($metrics['projectDistribution'])->map(fn (array $project): object => (object) $project),
        ];
    }

    public function overdueTasks(User $user)
    {
        return $this->tasks->overdueTasks($user);
    }

    public function myTasks(User $user)
    {
        return $this->tasks->myTasks($user);
    }
}
