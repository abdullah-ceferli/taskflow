<?php

namespace Modules\Tasks\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TaskAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'task_id' => $this->task_id, 'uploaded_by' => $this->uploaded_by, 'original_name' => $this->original_name, 'mime_type' => $this->mime_type, 'size' => $this->size, 'metadata_version' => $this->metadata_version, 'scan_status' => $this->scan_status, 'download_count' => $this->download_count, 'last_downloaded_at' => $this->last_downloaded_at?->toISOString(), 'created_at' => $this->created_at?->toISOString()];
    }
}
