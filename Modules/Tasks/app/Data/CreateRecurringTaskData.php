<?php

namespace Modules\Tasks\Data;

use DateTimeImmutable;
use Modules\Tasks\Enums\RecurrenceFrequency;
use Modules\Tasks\Enums\TaskPriority;

final readonly class CreateRecurringTaskData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public ?int $assigneeId,
        public ?int $milestoneId,
        public TaskPriority $priority,
        public float $estimateHours,
        public RecurrenceFrequency $frequency,
        public int $interval,
        public string $timezone,
        public int $dueOffsetDays,
        public DateTimeImmutable $startsAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['title'],
            $data['description'] ?? null,
            $data['assignee_id'] ?? null,
            $data['milestone_id'] ?? null,
            TaskPriority::from($data['priority']),
            (float) ($data['estimate_hours'] ?? 0),
            RecurrenceFrequency::from($data['frequency']),
            (int) $data['interval'],
            $data['timezone'],
            (int) ($data['due_offset_days'] ?? 0),
            new DateTimeImmutable($data['starts_at'], new \DateTimeZone($data['timezone'])),
        );
    }
}
