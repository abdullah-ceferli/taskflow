<?php

namespace App\Http\Resources;

use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ReportExportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'type' => $this->type, 'status' => $this->status, 'expires_at' => $this->expires_at->toISOString(), 'download_url' => app(ReportExportService::class)->downloadUrl($this->resource), 'failure_message' => $this->failure_message, 'created_at' => $this->created_at?->toISOString()];
    }
}
