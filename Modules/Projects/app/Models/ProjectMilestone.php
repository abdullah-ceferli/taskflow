<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Models\Task;

class ProjectMilestone extends Model
{
    protected $fillable = ['project_id', 'name', 'description', 'due_at', 'completed_at'];

    protected function casts(): array
    {
        return ['due_at' => 'date', 'completed_at' => 'datetime'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'milestone_id');
    }

    public function getProgressAttribute(): int
    {
        $total = (int) ($this->tasks_count ?? $this->tasks()->count());
        $done = (int) ($this->done_tasks_count ?? $this->tasks()->where('status', TaskStatus::Done->value)->count());

        return $total === 0 ? 0 : (int) round(($done / $total) * 100);
    }

    public function getAtRiskAttribute(): bool
    {
        return $this->completed_at === null && $this->due_at->isPast() && $this->progress < 100;
    }
}
