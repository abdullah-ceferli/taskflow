<?php

namespace Modules\Projects\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'workspace_id' => $this->workspace_id, 'name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'status' => $this->status->value, 'owner_id' => $this->owner_id, 'milestones' => ProjectMilestoneResource::collection($this->whenLoaded('milestones')), 'starts_at' => $this->starts_at?->toDateString(), 'due_at' => $this->due_at?->toDateString(), 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
