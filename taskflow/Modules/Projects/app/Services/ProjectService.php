<?php

namespace Modules\Projects\Services;

use Modules\Projects\Data\CreateProjectData;
use Modules\Projects\Data\UpdateProjectData;
use Modules\Projects\Models\Project;
use Modules\Projects\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Support\Str;

class ProjectService
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepository,
    ) {}

    /**
     * Create a new project
     */
    public function create(CreateProjectData $data): Project
    {
        return $this->projectRepository->create([
            'name' => $data->name,
            'slug' => $data->slug ?? Str::slug($data->name),
            'description' => $data->description,
            'owner_id' => $data->ownerId,
            'starts_at' => $data->startsAt,
            'due_at' => $data->dueAt,
            'status' => 'draft',
        ]);
    }

    /**
     * Update an existing project
     */
    public function update(Project $project, UpdateProjectData $data): Project
    {
        $attributes = [];

        if ($data->name !== null) {
            $attributes['name'] = $data->name;
        }

        if ($data->slug !== null) {
            $attributes['slug'] = $data->slug;
        }

        if ($data->description !== null) {
            $attributes['description'] = $data->description;
        }

        if ($data->status !== null) {
            $attributes['status'] = $data->status;
        }

        if ($data->startsAt !== null) {
            $attributes['starts_at'] = $data->startsAt;
        }

        if ($data->dueAt !== null) {
            $attributes['due_at'] = $data->dueAt;
        }

        return $this->projectRepository->update($project, $attributes);
    }

    /**
     * Archive a project
     */
    public function archive(Project $project): Project
    {
        return $this->update($project, new UpdateProjectData(status: 'archived'));
    }

    /**
     * Activate a project
     */
    public function activate(Project $project): Project
    {
        return $this->update($project, new UpdateProjectData(status: 'active'));
    }

    /**
     * Delete a project
     */
    public function delete(Project $project): bool
    {
        return $this->projectRepository->delete($project);
    }
}
