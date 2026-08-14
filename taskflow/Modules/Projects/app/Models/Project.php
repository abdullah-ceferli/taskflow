<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'projects';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'owner_id',
        'starts_at',
        'due_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'due_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Project owner (creator)
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Project members
     */
    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    /**
     * Check if user is a member of this project
     */
    public function hasMember(User $user): bool
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->exists();
    }
}
