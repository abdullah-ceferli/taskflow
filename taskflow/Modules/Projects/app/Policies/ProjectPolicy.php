<?php

namespace Modules\Projects\Policies;

use App\Models\User;
use Modules\Projects\Models\Project;

class ProjectPolicy
{
    /**
     * Determine if user can view project list
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view projects they own or are members of
        return true;
    }

    /**
     * Determine if user can view a specific project
     */
    public function view(User $user, Project $project): bool
    {
        // User can view if they are the owner or a member
        return $project->owner_id === $user->id || $project->hasMember($user);
    }

    /**
     * Determine if user can create projects
     */
    public function create(User $user): bool
    {
        // For now, any authenticated user can create projects
        // Later: check for specific role/permission
        return true;
    }

    /**
     * Determine if user can update project
     */
    public function update(User $user, Project $project): bool
    {
        // Only project owner can update
        return $project->owner_id === $user->id;
    }

    /**
     * Determine if user can delete project
     */
    public function delete(User $user, Project $project): bool
    {
        // Only project owner can delete
        return $project->owner_id === $user->id;
    }

    /**
     * Determine if user can archive project
     */
    public function archive(User $user, Project $project): bool
    {
        // Only project owner or admin can archive
        return $project->owner_id === $user->id;
    }

    /**
     * Determine if user can manage members
     */
    public function manageMember(User $user, Project $project): bool
    {
        // Only project owner can manage members
        return $project->owner_id === $user->id;
    }
}
