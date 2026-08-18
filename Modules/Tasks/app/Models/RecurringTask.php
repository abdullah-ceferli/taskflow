<?php

namespace Modules\Tasks\Models;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectMilestone;
use Modules\Tasks\Enums\RecurrenceFrequency;
use Modules\Tasks\Enums\TaskPriority;

class RecurringTask extends Model
{
    protected $fillable = ['workspace_id', 'project_id', 'created_by', 'assignee_id', 'milestone_id', 'title', 'description', 'priority', 'estimate_hours', 'frequency', 'interval', 'timezone', 'due_offset_days', 'next_run_at', 'last_generated_at', 'active'];

    protected function casts(): array
    {
        return ['priority' => TaskPriority::class, 'frequency' => RecurrenceFrequency::class, 'estimate_hours' => 'decimal:2', 'next_run_at' => 'datetime', 'last_generated_at' => 'datetime', 'active' => 'boolean'];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class);
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(RecurringTaskOccurrence::class);
    }
}
