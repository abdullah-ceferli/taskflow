<?php

namespace Modules\Activity\Services;

use App\Models\User;
use App\Services\CurrentWorkspace;
use Illuminate\Database\Eloquent\Model;
use Modules\Activity\Enums\ActivityEvent;
use Modules\Projects\Models\Project;

class ActivityRecorder
{
    private const PAYLOAD_VERSION = 1;

    /** @var list<string> */
    private const SENSITIVE_KEYS = ['password', 'password_confirmation', 'token', 'plain_text_token', 'secret', 'api_key', 'authorization', 'path', 'disk'];

    public function record(ActivityEvent $event, User $actor, Model $subject, array $properties = []): void
    {
        $workspaceId = $properties['workspace_id'] ?? $this->workspaceId($actor, $properties);
        $properties = [
            'schema_version' => self::PAYLOAD_VERSION,
            ...($workspaceId ? ['workspace_id' => $workspaceId] : []),
            ...$this->withoutSensitiveValues($properties),
        ];

        activity($event->value)
            ->causedBy($actor)
            ->performedOn($subject)
            ->withProperties($properties)
            ->event($event->value)
            ->log($event->value);
    }

    private function workspaceId(User $actor, array $properties): ?int
    {
        if (isset($properties['project_id'])) {
            $workspaceId = Project::query()->withTrashed()->whereKey($properties['project_id'])->value('workspace_id');

            if ($workspaceId) {
                return (int) $workspaceId;
            }
        }

        return app(CurrentWorkspace::class)->idFor($actor);
    }

    private function withoutSensitiveValues(array $properties): array
    {
        $safe = [];

        foreach ($properties as $key => $value) {
            if (in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                continue;
            }

            $safe[$key] = is_array($value) ? $this->withoutSensitiveValues($value) : $value;
        }

        return $safe;
    }
}
