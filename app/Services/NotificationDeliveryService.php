<?php

namespace App\Services;

use App\Models\NotificationDelivery;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Notifications\Notification;
use Throwable;

final class NotificationDeliveryService
{
    /** @param list<string> $channels */
    public function sendOnce(
        User $recipient,
        int $workspaceId,
        string $event,
        string $subjectType,
        int $subjectId,
        Notification $notification,
        array $channels,
    ): bool {
        if ($channels === []) {
            return false;
        }

        try {
            $delivery = NotificationDelivery::query()->create([
                'workspace_id' => $workspaceId,
                'user_id' => $recipient->id,
                'event' => $event,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'delivery_date' => today(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return false;
        }

        try {
            $recipient->notify($notification);
        } catch (Throwable $exception) {
            $delivery->delete();

            throw $exception;
        }

        return true;
    }
}
