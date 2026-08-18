<?php

namespace Modules\Tasks\Policies;

use App\Enums\PermissionName;
use App\Models\User;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAttachment;

class TaskAttachmentPolicy
{
    public function create(User $user, Task $task): bool
    {
        return $user->can('uploadAttachment', $task);
    }

    public function delete(User $user, TaskAttachment $attachment): bool
    {
        return $user->hasPermissionTo(PermissionName::AttachmentsDelete->value)
            && ($user->id === $attachment->uploaded_by || $user->can('update', $attachment->task));
    }
}
