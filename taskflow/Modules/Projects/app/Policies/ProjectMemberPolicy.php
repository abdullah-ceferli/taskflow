<?php

namespace Modules\Projects\Policies;

use App\Models\User;
use Modules\Projects\Models\ProjectMember;

class ProjectMemberPolicy
{
    /**
     * Determine if user can view members of a project
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if user can add a member
     */
    public function add(User $user, ProjectMember $member): bool
    {
        // Only project owner can add members
        return $member->project->owner_id === $user->id;
    }

    /**
     * Determine if user can remove a member
     */
    public function remove(User $user, ProjectMember $member): bool
    {
        // Only project owner can remove members
        return $member->project->owner_id === $user->id;
    }

    /**
     * Determine if user can update member role
     */
    public function updateRole(User $user, ProjectMember $member): bool
    {
        // Only project owner can update roles
        return $member->project->owner_id === $user->id;
    }
}
