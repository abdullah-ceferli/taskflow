<?php

namespace Modules\Tasks\Policies;

use App\Enums\PermissionName;
use App\Models\User;
use Modules\Projects\Contracts\ProjectAccessInterface;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAttachment;
use Modules\Tasks\Models\TaskComment;

class TaskPolicy
{
    public function __construct(private readonly ProjectAccessInterface $projects) {}

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::TasksView->value);
    }

    public function create(User $user, int $projectId): bool
    {
        return $user->hasPermissionTo(PermissionName::TasksCreate->value) && $this->projects->forActor($projectId, $user)->manager;
    }

    public function view(User $user, Task $task): bool
    {
        return $user->hasPermissionTo(PermissionName::TasksView->value) && ($this->projects->forActor($task->project_id, $user)->manager || $task->assignee_id === $user->id);
    }

    public function update(User $user, Task $task): bool
    {
        return $user->hasPermissionTo(PermissionName::TasksUpdate->value) && $this->projects->forActor($task->project_id, $user)->manager;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasPermissionTo(PermissionName::TasksDelete->value) && $this->projects->forActor($task->project_id, $user)->manager;
    }

    public function assign(User $user, Task $task): bool
    {
        return $user->hasPermissionTo(PermissionName::TasksAssign->value) && $this->projects->forActor($task->project_id, $user)->manager;
    }

    public function changeStatus(User $user, Task $task): bool
    {
        return $user->hasPermissionTo(PermissionName::TasksStatusChange->value) && ($this->projects->forActor($task->project_id, $user)->manager || $task->assignee_id === $user->id);
    }

    public function comment(User $user, Task $task): bool
    {
        return $user->hasPermissionTo(PermissionName::CommentsCreate->value) && $this->view($user, $task);
    }

    public function deleteComment(User $user, TaskComment $comment, Task $task): bool
    {
        return $user->hasPermissionTo(PermissionName::CommentsDelete->value) && ($user->id === $comment->user_id || $this->projects->forActor($task->project_id, $user)->manager);
    }

    public function uploadAttachment(User $user, Task $task): bool
    {
        return $user->hasPermissionTo(PermissionName::AttachmentsUpload->value) && $this->view($user, $task);
    }

    public function deleteAttachment(User $user, TaskAttachment $attachment, Task $task): bool
    {
        return $user->hasPermissionTo(PermissionName::AttachmentsDelete->value) && ($attachment->uploaded_by === $user->id || $this->projects->forActor($task->project_id, $user)->manager);
    }
}
