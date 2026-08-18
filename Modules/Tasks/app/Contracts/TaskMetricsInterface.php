<?php

namespace Modules\Tasks\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Tasks\Data\TaskMetricsData;

interface TaskMetricsInterface
{
    /** @return array{total: int, todo: int, in_progress: int, review: int, overdue: int, completed_today: int} */
    public function countsFor(User $user): array;

    public function forUser(User $user): TaskMetricsData;

    public function myTasks(User $user): Collection;

    public function overdueTasks(User $user): Collection;

    public function workloadFor(User $user): Collection;
}
