<?php

namespace Modules\Projects\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProjectMilestoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'project_id' => $this->project_id, 'name' => $this->name, 'description' => $this->description, 'due_at' => $this->due_at->toDateString(), 'completed_at' => $this->completed_at?->toISOString(), 'tasks_count' => (int) ($this->tasks_count ?? 0), 'done_tasks_count' => (int) ($this->done_tasks_count ?? 0), 'progress' => $this->progress, 'at_risk' => $this->at_risk];
    }
}
