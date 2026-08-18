<?php

namespace App\Services;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Collection;

final class NotificationPreferenceService
{
    /** @return Collection<string, NotificationPreference> */
    public function forUser(User $user, int $workspaceId): Collection
    {
        return NotificationPreference::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('event');
    }

    public function update(User $user, int $workspaceId, string $event, bool $inApp, bool $email): NotificationPreference
    {
        return NotificationPreference::query()->updateOrCreate(
            ['workspace_id' => $workspaceId, 'user_id' => $user->id, 'event' => $event],
            ['in_app' => $inApp, 'email' => $email],
        );
    }

    public function allowsInApp(User $user, int $workspaceId, string $event): bool
    {
        return NotificationPreference::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->where('event', $event)
            ->value('in_app') ?? true;
    }

    /** @return list<string> */
    public function channels(User $user, int $workspaceId, string $event): array
    {
        $preference = NotificationPreference::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->where('event', $event)
            ->first();

        if (! $preference) {
            return ['database'];
        }

        return array_values(array_filter([
            $preference->in_app ? 'database' : null,
            $preference->email ? 'mail' : null,
        ]));
    }
}
