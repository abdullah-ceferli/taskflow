<?php

namespace Modules\Projects\Data;

final readonly class AddProjectMemberData
{
    public function __construct(
        public int $projectId,
        public int $userId,
        public string $memberRole = 'member',
    ) {}
}
