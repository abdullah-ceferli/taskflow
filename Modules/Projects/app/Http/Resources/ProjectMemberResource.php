<?php

namespace Modules\Projects\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProjectMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'project_id' => $this->project_id, 'user_id' => $this->user_id, 'member_role' => $this->member_role->value, 'joined_at' => $this->joined_at?->toISOString(), 'created_at' => $this->created_at?->toISOString()];
    }
}
