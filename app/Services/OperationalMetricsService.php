<?php

namespace App\Services;

use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class OperationalMetricsService
{
    public function __construct(private readonly PerformanceTelemetry $performance) {}

    /** @return array<string, int|float> */
    public function snapshot(): array
    {
        $requestRows = $this->performance->report();

        return [
            'request_samples' => (int) collect($requestRows)->sum('samples'),
            'request_p95_ms' => (float) collect($requestRows)->max('p95_ms'),
            'request_error_rate_percent' => (float) collect($requestRows)->max('error_rate'),
            'queued_jobs' => $this->tableCount('jobs'),
            'oldest_queue_age_seconds' => $this->oldestQueueAge(),
            'failed_jobs' => $this->tableCount('failed_jobs'),
            'failed_notifications' => $this->failedNotificationJobs(),
            'failed_webhooks' => Schema::hasTable('webhook_deliveries')
                ? WebhookDelivery::query()->where('status', 'failed')->count()
                : 0,
            'local_storage_bytes' => $this->localStorageBytes(),
        ];
    }

    private function tableCount(string $table): int
    {
        try {
            return Schema::hasTable($table) ? DB::table($table)->count() : 0;
        } catch (Throwable) {
            return 0;
        }
    }

    private function oldestQueueAge(): int
    {
        try {
            if (! Schema::hasTable('jobs')) {
                return 0;
            }

            $createdAt = DB::table('jobs')->min('created_at');

            return $createdAt === null ? 0 : max(0, now()->timestamp - (int) $createdAt);
        } catch (Throwable) {
            return 0;
        }
    }

    private function failedNotificationJobs(): int
    {
        try {
            return Schema::hasTable('failed_jobs')
                ? DB::table('failed_jobs')->where('payload', 'like', '%Notification%')->count()
                : 0;
        } catch (Throwable) {
            return 0;
        }
    }

    private function localStorageBytes(): int
    {
        try {
            return (int) collect(Storage::disk('local')->allFiles())
                ->sum(fn (string $file): int => Storage::disk('local')->size($file));
        } catch (Throwable) {
            return 0;
        }
    }
}
