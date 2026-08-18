<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Auth\Access\AuthorizationException;

final class CurrentWorkspace
{
    private ?Workspace $workspace = null;

    public function resolve(User $user, ?int $requestedWorkspaceId = null): ?Workspace
    {
        $query = Workspace::query();

        if (! $user->hasRole(UserRole::Admin->value)) {
            $query->whereHas('memberships', fn ($memberships) => $memberships->where('user_id', $user->id));
        }

        if ($requestedWorkspaceId) {
            $workspace = (clone $query)->find($requestedWorkspaceId);

            if (! $workspace) {
                throw new AuthorizationException('You do not have access to the requested workspace.');
            }

            return $this->workspace = $workspace;
        }

        return $this->workspace = $query->orderBy('name')->first();
    }

    public function get(): ?Workspace
    {
        return $this->workspace;
    }

    public function idFor(User $user): ?int
    {
        return $this->workspace?->id ?? $this->resolve($user)?->id;
    }

    public function belongsToCurrent(User $user, ?int $workspaceId): bool
    {
        return $workspaceId !== null && $this->idFor($user) === $workspaceId;
    }

    public function canManage(User $user): bool
    {
        if ($user->hasRole(UserRole::Admin->value)) {
            return true;
        }

        $workspaceId = $this->idFor($user);

        return $workspaceId !== null && $user->workspaceMemberships()
            ->where('workspace_id', $workspaceId)
            ->whereIn('role', [WorkspaceRole::Owner->value, WorkspaceRole::Manager->value])
            ->exists();
    }
}
