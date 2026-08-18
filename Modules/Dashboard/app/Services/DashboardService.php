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
        return $this->cache->remember($user, function () use ($user): array {
            $projects = $this->projects->forUser($user);
            $tasks = $this->tasks->forUser($user);

            return [
                'activeProjects' => $projects->active,
                'archivedProjects' => $projects->archived,
                'totalTasks' => $tasks->total,
                'todo' => $tasks->todo,
                'inProgress' => $tasks->inProgress,
                'review' => $tasks->review,
                'overdue' => $tasks->overdue,
                'completedToday' => $tasks->completedToday,
                'myTasks' => $tasks->myTasks,
                'workload' => $tasks->workload,
                'recentActivity' => $this->activity->recentForUser($user, 8),
                'projectDistribution' => $projects->distribution,
            ];
        });
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
