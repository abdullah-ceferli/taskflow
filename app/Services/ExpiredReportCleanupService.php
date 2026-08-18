<?php

namespace App\Services;

use App\Models\ReportExport;
use Illuminate\Support\Facades\Storage;

final class ExpiredReportCleanupService
{
    public function prune(): int
    {
        $count = 0;
        ReportExport::query()->where('expires_at', '<=', now())->chunkById(200, function ($exports) use (&$count): void {
            foreach ($exports as $export) {
                if ($export->path) {
                    Storage::disk($export->disk)->delete($export->path);
                }
                $export->delete();
                $count++;
            }
        });

        return $count;
    }
}
