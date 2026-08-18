<?php

namespace Modules\Tasks\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Tasks\Data\TaskMetricsData;

interface TaskMetricsInterface
{
    public function forUser(User $user): TaskMetricsData;

    public function myTasks(User $user): Collection;

    public function overdueTasks(User $user): Collection;

    public function workloadFor(User $user): Collection;
}
