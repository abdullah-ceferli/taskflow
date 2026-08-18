<?php

namespace Modules\Tasks\Services;

use Illuminate\Support\Collection;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskComment;
use Modules\Tasks\Repositories\Contracts\TaskCommentRepositoryInterface;

final class TaskCommentQueryService
{
    public function __construct(private readonly TaskCommentRepositoryInterface $comments) {}

    /** @return Collection<int, TaskComment> */
    public function forTask(Task $task): Collection
    {
        return $this->comments->forTask($task);
    }
}
