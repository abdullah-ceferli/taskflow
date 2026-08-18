<?php

namespace Modules\Projects\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Projects\Models\Project;

interface ProjectRepositoryInterface
{
    public function paginateFor(User $actor, ?string $search, ?string $status): LengthAwarePaginator;

    public function save(Project $project): Project;

    public function slugExists(string $slug, ?int $excludingProjectId = null): bool;
}
