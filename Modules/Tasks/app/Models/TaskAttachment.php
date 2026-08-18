<?php

namespace Modules\Tasks\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Tasks\Database\Factories\TaskAttachmentFactory;

/**
 * @property int $id
 * @property int $task_id
 * @property int $uploaded_by
 * @property string $disk
 * @property string $path
 * @property-read Task $task
 */
class TaskAttachment extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return TaskAttachmentFactory::new();
    }

    protected $fillable = ['task_id', 'uploaded_by', 'disk', 'path', 'original_name', 'mime_type', 'size', 'metadata_version', 'checksum', 'scan_status', 'download_count', 'last_downloaded_at'];

    protected function casts(): array
    {
        return ['metadata_version' => 'integer', 'download_count' => 'integer', 'last_downloaded_at' => 'datetime'];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
