<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ProjectMember extends Model
{
    use HasFactory;

    protected $table = 'project_members';

    protected $fillable = [
        'project_id',
        'user_id',
        'member_role',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Project relationship
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
