<?php

namespace Modules\Projects\Repositories\Contracts;

use Modules\Projects\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProjectRepositoryInterface
{
    /**
     * Get all projects with pagination
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find project by ID or fail
     */
    public function findOrFail(int $id): Project;

    /**
     * Get projects by owner ID
     */
    public function getByOwnerId(int $ownerId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get active projects
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator;

    /**
     * Create new project
     */
    public function create(array $attributes): Project;

    /**
     * Update project
     */
    public function update(Project $project, array $attributes): Project;

    /**
     * Delete project (soft delete)
     */
    public function delete(Project $project): bool;

    /**
     * Check if user is owner or member
     */
    public function userHasAccess(Project $project, int $userId): bool;
}
