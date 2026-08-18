<?php

namespace Modules\Dashboard\Services;

use App\Models\User;
use App\Services\CurrentWorkspace;
use Closure;
use Illuminate\Support\Facades\Cache;

final class DashboardCache
{
    private const PAYLOAD_VERSION = 3;

    public function __construct(private readonly CurrentWorkspace $current) {}

    public function remember(User $actor, Closure $loader): array
    {
        $workspaceId = $this->current->idFor($actor);
        if (! $workspaceId) {
            return $loader();
        }

        $version = Cache::get($this->versionKey($workspaceId), 1);
        $accessHash = hash('sha256', $actor->getRoleNames()->sort()->values()->join('|').'|'.$actor->getAllPermissions()->pluck('name')->sort()->values()->join('|'));
        $key = 'taskflow:dashboard:v'.self::PAYLOAD_VERSION.":workspace:{$workspaceId}:actor:{$actor->id}:access:{$accessHash}:version:{$version}";

        return Cache::remember($key, now()->addSeconds((int) config('taskflow.performance.dashboard_cache_seconds', 30)), $loader);
    }

    public function invalidate(?int $workspaceId): void
    {
        if (! $workspaceId) {
            return;
        }

        $key = $this->versionKey($workspaceId);
        Cache::add($key, 1, now()->addDays(7));
        Cache::increment($key);
    }

    private function versionKey(int $workspaceId): string
    {
        return "taskflow:dashboard:workspace:{$workspaceId}:version";
    }
}
