<?php

namespace Modules\Projects\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Str;
use Modules\Projects\Models\Project;

final readonly class ProjectMemberAdded
{
    use Dispatchable;

    public string $eventId;

    public function __construct(
        public User $actor,
        public Project $project,
        public array $properties,
        ?string $eventId = null,
    ) {
        $this->eventId = $eventId ?? (string) Str::uuid();
    }
}
