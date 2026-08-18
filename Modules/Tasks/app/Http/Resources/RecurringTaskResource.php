<?php

namespace Modules\Tasks\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class RecurringTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'project_id' => $this->project_id, 'title' => $this->title, 'assignee_id' => $this->assignee_id, 'milestone_id' => $this->milestone_id, 'priority' => $this->priority->value, 'estimate_hours' => (float) $this->estimate_hours, 'frequency' => $this->frequency->value, 'interval' => $this->interval, 'timezone' => $this->timezone, 'due_offset_days' => $this->due_offset_days, 'next_run_at' => $this->next_run_at->toISOString(), 'last_generated_at' => $this->last_generated_at?->toISOString(), 'active' => $this->active];
    }
}
