<?php

namespace Modules\Projects\Data;

final readonly class CreateProjectData
{
    public function __construct(public string $name, public ?string $description, public ?\DateTimeImmutable $startsAt, public ?\DateTimeImmutable $dueAt) {}

    public static function fromArray(array $data): self
    {
        return new self($data['name'], $data['description'] ?? null, isset($data['starts_at']) ? new \DateTimeImmutable($data['starts_at']) : null, isset($data['due_at']) ? new \DateTimeImmutable($data['due_at']) : null);
    }
}
