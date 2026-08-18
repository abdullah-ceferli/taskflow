<?php

namespace Modules\Activity\Listeners;

use Modules\Activity\Enums\ActivityEvent;
use Modules\Activity\Services\ActivityRecorder;
use Modules\Projects\Events\ProjectMemberAdded;
use Modules\Tasks\Events\TaskAssigned;
use Modules\Tasks\Events\TaskCreated;
use Modules\Tasks\Events\TaskStatusChanged;
use Spatie\Activitylog\Models\Activity;

final class RecordDomainActivity
{
    public function __construct(private readonly ActivityRecorder $activity) {}

    public function handle(TaskCreated|TaskAssigned|TaskStatusChanged|ProjectMemberAdded $event): void
    {
        if (Activity::query()->where('properties->event_id', $event->eventId)->exists()) {
            return;
        }

        [$name, $subject] = match (true) {
            $event instanceof TaskCreated => [ActivityEvent::TaskCreated, $event->task],
            $event instanceof TaskAssigned => [ActivityEvent::TaskAssigned, $event->task],
            $event instanceof TaskStatusChanged => [ActivityEvent::TaskStatusChanged, $event->task],
            $event instanceof ProjectMemberAdded => [ActivityEvent::ProjectMemberAdded, $event->project],
        };

        $this->activity->record($name, $event->actor, $subject, [
            'event_id' => $event->eventId,
            ...$event->properties,
        ]);
    }
}
