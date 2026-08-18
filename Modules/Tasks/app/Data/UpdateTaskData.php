<?php

namespace Modules\Tasks\Data;

use Modules\Tasks\Enums\TaskPriority;

final readonly class UpdateTaskData
{
    public function __construct(public string $title, public ?string $description, public TaskPriority $priority, public ?\DateTimeImmutable $dueAt, public ?int $milestoneId = null, public float $estimateHours = 0) {}

    public static function fromArray(array $data): self
    {
        return new self($data['title'], $data['description'] ?? null, TaskPriority::from($data['priority']), isset($data['due_at']) ? new \DateTimeImmutable($data['due_at']) : null, $data['milestone_id'] ?? null, (float) ($data['estimate_hours'] ?? 0));
    }
}
