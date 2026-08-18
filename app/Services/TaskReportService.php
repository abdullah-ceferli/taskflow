<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Projects\Enums\ProjectMemberRole;
use Modules\Tasks\Models\Task;

final class TaskReportService
{
    public function rows(User $actor, int $workspaceId): Collection
    {
        $tasks = Task::query()->whereHas('project', fn (Builder $projects) => $projects->where('workspace_id', $workspaceId));
        if (! $actor->hasRole(UserRole::Admin->value)) {
            if ($actor->hasRole(UserRole::ProjectManager->value)) {
                $tasks->where(fn (Builder $visible) => $visible->where('assignee_id', $actor->id)->orWhereHas('project', fn (Builder $projects) => $projects
                    ->where('owner_id', $actor->id)
                    ->orWhereHas('memberships', fn (Builder $memberships) => $memberships->where('user_id', $actor->id)->where('member_role', ProjectMemberRole::Manager->value))));
            } else {
                $tasks->where('assignee_id', $actor->id);
            }
        }

        return $tasks->with(['project', 'assignee', 'milestone'])->orderBy('id')->get();
    }
}
