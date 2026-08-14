<?php

namespace Modules\Projects\Repositories\Contracts;

use Modules\Projects\Models\ProjectMember;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProjectMemberRepositoryInterface
{
    /**
     * Get all members of a project
     */
    public function getProjectMembers(int $projectId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find member by ID
     */
    public function findOrFail(int $id): ProjectMember;

    /**
     * Check if user is member of project
     */
    public function isMember(int $projectId, int $userId): bool;

    /**
     * Add member to project
     */
    public function addMember(int $projectId, int $userId, string $role = 'member'): ProjectMember;

    /**
     * Remove member from project
     */
    public function removeMember(int $projectId, int $userId): bool;

    /**
     * Update member role
     */
    public function updateRole(ProjectMember $member, string $role): ProjectMember;

    /**
     * Get member by project and user
     */
    public function getMember(int $projectId, int $userId): ?ProjectMember;
}
