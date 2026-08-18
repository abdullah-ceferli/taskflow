<?php

namespace Modules\Projects\Data;

final readonly class ProjectAccessData
{
    public function __construct(
        public int $projectId,
        public int $ownerId,
        public bool $active,
        public bool $member,
        public bool $manager,
    ) {}
}
