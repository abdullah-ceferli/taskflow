<?php

namespace Modules\Projects\Services;

use App\Enums\UserRole;
use App\Models\User;
use Modules\Projects\Contracts\ProjectAccessInterface;
use Modules\Projects\Data\ProjectAccessData;
use Modules\Projects\Enums\ProjectMemberRole;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;

final class EloquentProjectAccess implements ProjectAccessInterface
{
    public function forActor(int $projectId, User $actor): ProjectAccessData
    {
        $project = Project::query()->inCurrentWorkspace($actor)->findOrFail($projectId);
        $membership = $project->memberships()->where('user_id', $actor->id)->first();
        $isOwner = $project->owner_id === $actor->id;
        $isAdmin = $actor->hasRole(UserRole::Admin->value);
        $isManager = $membership?->member_role === ProjectMemberRole::Manager;

        return new ProjectAccessData(
            projectId: $project->id,
            ownerId: $project->owner_id,
            active: $project->status === ProjectStatus::Active,
            member: $isOwner || $membership !== null,
            manager: $isAdmin || $isOwner || $isManager,
        );
    }
}
