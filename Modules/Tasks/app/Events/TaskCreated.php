<?php

namespace Modules\Tasks\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Str;
use Modules\Tasks\Models\Task;

final readonly class TaskCreated
{
    use Dispatchable;

    public string $eventId;

    public function __construct(
        public User $actor,
        public Task $task,
        public array $properties,
        ?string $eventId = null,
    ) {
        $this->eventId = $eventId ?? (string) Str::uuid();
    }
}
