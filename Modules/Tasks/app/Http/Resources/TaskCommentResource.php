<?php

namespace Modules\Tasks\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TaskCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'task_id' => $this->task_id, 'user_id' => $this->user_id, 'body' => $this->body, 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
