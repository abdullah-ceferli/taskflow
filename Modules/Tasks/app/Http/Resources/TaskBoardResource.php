<?php

namespace Modules\Tasks\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TaskBoardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['columns' => $this->resource->map(fn (array $column) => ['status' => $column['status']->value, 'tasks' => TaskResource::collection($column['tasks'])])->values()];
    }
}
