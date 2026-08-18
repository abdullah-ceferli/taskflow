<?php

namespace Modules\Tasks\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TaskLabelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'workspace_id' => $this->workspace_id, 'project_id' => $this->project_id, 'name' => $this->name, 'color' => $this->color];
    }
}
