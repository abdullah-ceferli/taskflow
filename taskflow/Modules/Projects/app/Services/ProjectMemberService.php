<?php

namespace Modules\Projects\Services;

use Modules\Projects\Data\AddProjectMemberData;
use Modules\Projects\Models\ProjectMember;
use Modules\Projects\Repositories\Contracts\ProjectMemberRepositoryInterface;

class ProjectMemberService
{
    public function __construct(
        private ProjectMemberRepositoryInterface $memberRepository,
    ) {}

    /**
     * Add a member to a project
     */
    public function addMember(AddProjectMemberData $data): ProjectMember
    {
        // Check if already a member
        if ($this->memberRepository->isMember($data->projectId, $data->userId)) {
            throw new \Exception('User is already a member of this project.');
        }

        return $this->memberRepository->addMember(
            $data->projectId,
            $data->userId,
            $data->memberRole,
        );
    }

    /**
     * Remove a member from a project
     */
    public function removeMember(int $projectId, int $userId): bool
    {
        return $this->memberRepository->removeMember($projectId, $userId);
    }

    /**
     * Update member role
     */
    public function updateRole(ProjectMember $member, string $role): ProjectMember
    {
        return $this->memberRepository->updateRole($member, $role);
    }

    /**
     * Check if user is a member
     */
    public function isMember(int $projectId, int $userId): bool
    {
        return $this->memberRepository->isMember($projectId, $userId);
    }

    /**
     * Get member details
     */
    public function getMember(int $projectId, int $userId): ?ProjectMember
    {
        return $this->memberRepository->getMember($projectId, $userId);
    }
}
