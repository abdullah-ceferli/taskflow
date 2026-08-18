<?php

namespace Modules\Tasks\Policies;

use App\Enums\PermissionName;
use App\Models\User;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskComment;

class TaskCommentPolicy
{
    public function create(User $user, Task $task): bool
    {
        return $user->can('comment', $task);
    }

    public function delete(User $user, TaskComment $comment): bool
    {
        return $user->hasPermissionTo(PermissionName::CommentsDelete->value)
            && $user->can('view', $comment->task)
            && ($user->id === $comment->user_id || $user->can('update', $comment->task));
    }
}
