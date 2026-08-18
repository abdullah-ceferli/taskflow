<?php

namespace App\Listeners;

use App\Jobs\DeliverWebhook;
use App\Models\WebhookSubscription;
use Modules\Projects\Events\ProjectMemberAdded;
use Modules\Tasks\Events\TaskAssigned;
use Modules\Tasks\Events\TaskCreated;
use Modules\Tasks\Events\TaskStatusChanged;

final class DispatchWebhookDeliveries
{
    public function handle(TaskCreated|TaskAssigned|TaskStatusChanged|ProjectMemberAdded $event): void
    {
        [$name, $workspaceId, $subjectType, $subjectId] = match (true) {
            $event instanceof TaskCreated => ['task.created', $event->task->project()->value('workspace_id'), $event->task->getMorphClass(), $event->task->id],
            $event instanceof TaskAssigned => ['task.assigned', $event->task->project()->value('workspace_id'), $event->task->getMorphClass(), $event->task->id],
            $event instanceof TaskStatusChanged => ['task.status_changed', $event->task->project()->value('workspace_id'), $event->task->getMorphClass(), $event->task->id],
            $event instanceof ProjectMemberAdded => ['project.member_added', $event->project->workspace_id, $event->project->getMorphClass(), $event->project->id],
        };
        $payload = ['id' => $event->eventId, 'event' => $name, 'occurred_at' => now()->toISOString(), 'actor_id' => $event->actor->id, 'subject_type' => $subjectType, 'subject_id' => $subjectId, 'data' => $event->properties];

        WebhookSubscription::query()->where('workspace_id', $workspaceId)->where('active', true)->whereJsonContains('events', $name)->each(function (WebhookSubscription $subscription) use ($name, $payload): void {
            $delivery = $subscription->deliveries()->create(['event' => $name, 'payload' => $payload, 'status' => 'pending']);
            DeliverWebhook::dispatch($delivery->id);
        });
    }
}
