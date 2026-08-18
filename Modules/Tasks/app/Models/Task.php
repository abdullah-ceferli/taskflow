<?php

namespace Modules\Tasks\Models;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectMilestone;
use Modules\Tasks\Database\Factories\TaskFactory;
use Modules\Tasks\Enums\TaskPriority;
use Modules\Tasks\Enums\TaskStatus;

/**
 * @property int $id
 * @property int $project_id
 * @property int $creator_id
 * @property int|null $assignee_id
 * @property string|null $number
 * @property string $title
 * @property TaskStatus $status
 * @property TaskPriority $priority
 * @property Carbon|null $updated_at
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property-read Project $project
 * @property-read User|null $assignee
 */
class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory(): Factory
    {
        return TaskFactory::new();
    }

    protected $fillable = ['number', 'project_id', 'creator_id', 'assignee_id', 'milestone_id', 'title', 'description', 'status', 'priority', 'estimate_hours', 'due_at', 'started_at', 'completed_at'];

    protected function casts(): array
    {
        return ['status' => TaskStatus::class, 'priority' => TaskPriority::class, 'estimate_hours' => 'decimal:2', 'due_at' => 'date', 'started_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(TaskLabel::class, 'task_label');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class);
    }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'task_dependencies', 'task_id', 'depends_on_task_id')->withTimestamps();
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'task_dependencies', 'depends_on_task_id', 'task_id')->withTimestamps();
    }

    public function scopeVisibleTo(Builder $query, User $actor): Builder
    {
        $query->whereHas('project', fn (Builder $projects) => $projects->inCurrentWorkspace($actor));

        if ($actor->hasRole(UserRole::Admin->value)) {
            return $query;
        }

        if ($actor->hasRole(UserRole::ProjectManager->value)) {
            return $query->where(fn (Builder $tasks) => $tasks
                ->where('assignee_id', $actor->id)
                ->orWhereHas('project', fn (Builder $projects) => $projects->manageableBy($actor)));
        }

        return $query->where('assignee_id', $actor->id);
    }
}
