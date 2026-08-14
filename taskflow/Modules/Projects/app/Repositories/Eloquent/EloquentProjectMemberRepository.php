<?php

namespace Modules\Projects\Repositories\Eloquent;

use Modules\Projects\Models\ProjectMember;
use Modules\Projects\Repositories\Contracts\ProjectMemberRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentProjectMemberRepository implements ProjectMemberRepositoryInterface
{
    public function getProjectMembers(int $projectId, int $perPage = 15): LengthAwarePaginator
    {
        return ProjectMember::query()
            ->where('project_id', $projectId)
            ->with(['user', 'project'])
            ->paginate($perPage);
    }

    public function findOrFail(int $id): ProjectMember
    {
        return ProjectMember::with(['user', 'project'])
            ->findOrFail($id);
    }

    public function isMember(int $projectId, int $userId): bool
    {
        return ProjectMember::query()
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function addMember(int $projectId, int $userId, string $role = 'member'): ProjectMember
    {
        return ProjectMember::query()->create([
            'project_id' => $projectId,
            'user_id' => $userId,
            'member_role' => $role,
            'joined_at' => now(),
        ]);
    }

    public function removeMember(int $projectId, int $userId): bool
    {
        return ProjectMember::query()
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function updateRole(ProjectMember $member, string $role): ProjectMember
    {
        $member->update(['member_role' => $role]);
        return $member;
    }

    public function getMember(int $projectId, int $userId): ?ProjectMember
    {
        return ProjectMember::query()
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->with(['user', 'project'])
            ->first();
    }
}
