<?php

namespace Modules\Projects\Models;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\WorkspaceMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Projects\Database\Factories\ProjectMemberFactory;
use Modules\Projects\Enums\ProjectMemberRole;

class ProjectMember extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return ProjectMemberFactory::new();
    }

    protected $fillable = ['project_id', 'user_id', 'member_role', 'joined_at'];

    protected static function booted(): void
    {
        static::created(function (ProjectMember $membership): void {
            WorkspaceMember::query()->firstOrCreate(
                ['workspace_id' => $membership->project->workspace_id, 'user_id' => $membership->user_id],
                [
                    'role' => $membership->member_role === ProjectMemberRole::Manager ? WorkspaceRole::Manager : WorkspaceRole::Member,
                    'joined_at' => $membership->joined_at ?? now(),
                ],
            );
        });
    }

    protected function casts(): array
    {
        return ['member_role' => ProjectMemberRole::class, 'joined_at' => 'datetime'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
