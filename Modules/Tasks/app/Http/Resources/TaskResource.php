<?php

namespace Modules\Tasks\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'number' => $this->number, 'project_id' => $this->project_id, 'creator_id' => $this->creator_id, 'assignee_id' => $this->assignee_id, 'milestone_id' => $this->milestone_id, 'title' => $this->title, 'description' => $this->description, 'status' => $this->status->value, 'priority' => $this->priority->value, 'estimate_hours' => (float) $this->estimate_hours, 'labels' => TaskLabelResource::collection($this->whenLoaded('labels')), 'dependencies' => TaskResource::collection($this->whenLoaded('dependencies')), 'due_at' => $this->due_at?->toDateString(), 'started_at' => $this->started_at?->toISOString(), 'completed_at' => $this->completed_at?->toISOString(), 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
