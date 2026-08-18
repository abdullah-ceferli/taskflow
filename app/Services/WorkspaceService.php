<?php

namespace App\Services;

use App\Enums\WorkspaceRole;
use App\Exceptions\DomainRuleViolation;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class WorkspaceService
{
    public function createFor(User $owner, string $name): Workspace
    {
        return DB::transaction(function () use ($owner, $name): Workspace {
            $base = Str::slug($name) ?: 'workspace';
            $slug = $base.'-'.Str::lower(Str::random(6));
            $workspace = Workspace::query()->create(['name' => $name, 'slug' => $slug, 'owner_id' => $owner->id]);
            WorkspaceMember::query()->create([
                'workspace_id' => $workspace->id,
                'user_id' => $owner->id,
                'role' => WorkspaceRole::Owner,
                'joined_at' => now(),
            ]);

            return $workspace;
        });
    }

    /** @return array{invitation: WorkspaceInvitation, token: string} */
    public function invite(Workspace $workspace, User $actor, string $email, WorkspaceRole $role): array
    {
        $this->ensureManager($workspace, $actor);
        $email = Str::lower(trim($email));

        if ($workspace->members()->where('email', $email)->exists()) {
            throw new DomainRuleViolation('This user already belongs to the workspace.');
        }

        $token = Str::random(64);
        $invitation = WorkspaceInvitation::query()->create([
            'workspace_id' => $workspace->id,
            'email' => $email,
            'role' => $role,
            'token_hash' => hash('sha256', $token),
            'invited_by' => $actor->id,
            'expires_at' => now()->addDays(7),
        ]);

        return compact('invitation', 'token');
    }

    public function accept(User $user, string $token): WorkspaceMember
    {
        $invitation = WorkspaceInvitation::query()
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('accepted_at')
            ->firstOrFail();

        if ($invitation->expires_at->isPast() || strcasecmp($invitation->email, $user->email) !== 0) {
            throw new DomainRuleViolation('This workspace invitation is invalid or expired.');
        }

        return DB::transaction(function () use ($invitation, $user): WorkspaceMember {
            $membership = WorkspaceMember::query()->firstOrCreate(
                ['workspace_id' => $invitation->workspace_id, 'user_id' => $user->id],
                ['role' => $invitation->role, 'joined_at' => now()],
            );
            $invitation->update(['accepted_at' => now()]);

            return $membership;
        });
    }

    public function ensureManager(Workspace $workspace, User $actor): void
    {
        $allowed = $workspace->memberships()
            ->where('user_id', $actor->id)
            ->whereIn('role', [WorkspaceRole::Owner->value, WorkspaceRole::Manager->value])
            ->exists();

        if (! $allowed) {
            throw new DomainRuleViolation('Only workspace owners and managers can perform this action.');
        }
    }
}
