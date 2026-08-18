<?php

namespace Modules\Projects\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Modules\Projects\Contracts\ProjectMetricsInterface;
use Modules\Projects\Data\ProjectMetricsData;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;

final class EloquentProjectMetrics implements ProjectMetricsInterface
{
    public function forUser(User $user): ProjectMetricsData
    {
        $projects = $this->queryFor($user);
        $aggregate = (clone $projects)->toBase()->selectRaw(
            'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as archived',
            [ProjectStatus::Active->value, ProjectStatus::Archived->value],
        )->first();

        return new ProjectMetricsData(
            active: (int) ($aggregate->active ?? 0),
            archived: (int) ($aggregate->archived ?? 0),
            distribution: (clone $projects)
                ->withCount(['tasks' => fn (Builder $tasks) => $tasks->visibleTo($user)])
                ->orderBy('name')
                ->get(),
        );
    }

    private function queryFor(User $user): Builder
    {
        return $user->hasRole(UserRole::ProjectManager->value)
            ? Project::query()->manageableBy($user)
            : Project::query()->visibleTo($user);
    }
}
