<?php

namespace Modules\Projects\Data;

final readonly class UpdateProjectData
{
    public function __construct(
        public ?string $name = null,
        public ?string $slug = null,
        public ?string $description = null,
        public ?string $status = null,
        public ?\DateTimeImmutable $startsAt = null,
        public ?\DateTimeImmutable $dueAt = null,
    ) {}
}
