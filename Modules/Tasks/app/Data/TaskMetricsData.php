<?php

namespace Modules\Tasks\Data;

use Illuminate\Support\Collection;

final readonly class TaskMetricsData
{
    /** @param Collection<int, object> $myTasks */
    public function __construct(
        public int $total,
        public int $todo,
        public int $inProgress,
        public int $review,
        public int $overdue,
        public int $completedToday,
        public Collection $myTasks,
        public Collection $workload,
    ) {}
}
