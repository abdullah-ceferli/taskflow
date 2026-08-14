<?php

namespace Modules\Projects\Data;

final readonly class CreateProjectData
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description,
        public int $ownerId,
        public ?\DateTimeImmutable $startsAt = null,
        public ?\DateTimeImmutable $dueAt = null,
    ) {}
}
