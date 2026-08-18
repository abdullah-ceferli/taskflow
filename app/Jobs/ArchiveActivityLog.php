<?php

namespace App\Jobs;

use DateTimeImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\Activitylog\Models\Activity;

final class ArchiveActivityLog implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function __construct(public readonly DateTimeImmutable $before) {}

    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(): void
    {
        $disk = Storage::disk((string) config('taskflow.activity_retention.disk', 'local'));
        $chunkSize = (int) config('taskflow.activity_retention.chunk_size', 1000);

        do {
            $activities = Activity::query()->where('created_at', '<', $this->before)->orderBy('id')->limit($chunkSize)->get();
            if ($activities->isEmpty()) {
                break;
            }

            $firstId = $activities->first()->id;
            $lastId = $activities->last()->id;
            $path = 'activity-archive/'.$this->before->format('Y-m-d')."/activities-{$firstId}-{$lastId}.jsonl";
            $payload = $activities->map(fn (Activity $activity): string => json_encode([
                'id' => $activity->id,
                'log_name' => $activity->log_name,
                'description' => $activity->description,
                'event' => $activity->event,
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'causer_type' => $activity->causer_type,
                'causer_id' => $activity->causer_id,
                'properties' => $activity->properties->all(),
                'created_at' => $activity->created_at?->toIso8601String(),
            ], JSON_THROW_ON_ERROR))->implode("\n")."\n";

            if (! $disk->put($path, $payload)) {
                throw new RuntimeException('The activity archive could not be persisted.');
            }

            DB::transaction(fn () => Activity::query()->whereKey($activities->modelKeys())->where('created_at', '<', $this->before)->delete());
        } while ($activities->count() === $chunkSize);
    }
}
