<?php

namespace Modules\Projects\Repositories\Eloquent;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;
use Modules\Projects\Repositories\Contracts\ProjectRepositoryInterface;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
    public function paginateFor(User $actor, ?string $search, ?string $status): LengthAwarePaginator
    {
        return Project::query()->visibleTo($actor)->with('owner')->when(filled($search), fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")))->when(ProjectStatus::tryFrom((string) $status), fn ($q, ProjectStatus $s) => $q->where('status', $s->value))->latest()->paginate(12)->withQueryString();
    }

    public function save(Project $project): Project
    {
        $project->save();

        return $project;
    }

    public function slugExists(string $slug, ?int $excludingProjectId = null): bool
    {
        return Project::query()->where('slug', $slug)->when($excludingProjectId, fn ($q) => $q->whereKeyNot($excludingProjectId))->exists();
    }
}
