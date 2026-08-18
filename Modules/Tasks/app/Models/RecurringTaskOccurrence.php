<?php

namespace Modules\Tasks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringTaskOccurrence extends Model
{
    protected $fillable = ['recurring_task_id', 'scheduled_for', 'task_id'];

    protected function casts(): array
    {
        return ['scheduled_for' => 'datetime'];
    }

    public function recurringTask(): BelongsTo
    {
        return $this->belongsTo(RecurringTask::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
