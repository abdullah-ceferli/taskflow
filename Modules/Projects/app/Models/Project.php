<?php

namespace Modules\Projects\Models;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Workspace;
use App\Services\CurrentWorkspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Projects\Database\Factories\ProjectFactory;
use Modules\Projects\Enums\ProjectMemberRole;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Tasks\Models\RecurringTask;
use Modules\Tasks\Models\Task;

/**
 * @property int $id
 * @property int $workspace_id
 * @property int $owner_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property ProjectStatus $status
 */
class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory(): Factory
    {
        return ProjectFactory::new();
    }

    protected $fillable = ['workspace_id', 'name', 'slug', 'description', 'status', 'owner_id', 'starts_at', 'due_at'];

    protected function casts(): array
    {
        return ['status' => ProjectStatus::class, 'starts_at' => 'date', 'due_at' => 'date'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->withPivot(['member_role', 'joined_at'])->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function recurringTasks(): HasMany
    {
        return $this->hasMany(RecurringTask::class);
    }

    public function scopeVisibleTo(Builder $query, User $actor): Builder
    {
        $query->inCurrentWorkspace($actor);

        if ($actor->hasRole(UserRole::Admin->value)) {
            return $query;
        }

        return $query->where(fn (Builder $projects) => $projects
            ->where('owner_id', $actor->id)
            ->orWhereHas('memberships', fn (Builder $memberships) => $memberships->where('user_id', $actor->id)));
    }

    public function scopeManageableBy(Builder $query, User $actor): Builder
    {
        $query->inCurrentWorkspace($actor);

        if ($actor->hasRole(UserRole::Admin->value)) {
            return $query;
        }

        return $query->where(fn (Builder $projects) => $projects
            ->where('owner_id', $actor->id)
            ->orWhereHas('memberships', fn (Builder $memberships) => $memberships
                ->where('user_id', $actor->id)
                ->where('member_role', ProjectMemberRole::Manager->value)));
    }

    public function scopeInCurrentWorkspace(Builder $query, User $actor): Builder
    {
        $workspaceId = app(CurrentWorkspace::class)->idFor($actor);

        return $workspaceId ? $query->where('workspace_id', $workspaceId) : $query->whereRaw('1 = 0');
    }
}
