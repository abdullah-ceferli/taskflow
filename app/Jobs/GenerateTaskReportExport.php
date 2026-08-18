<?php

namespace App\Jobs;

use App\Models\ReportExport;
use App\Services\TaskReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class GenerateTaskReportExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $exportId) {}

    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(TaskReportService $reports): void
    {
        $export = ReportExport::query()->with('user')->findOrFail($this->exportId);
        if ($export->status === 'ready') {
            return;
        }
        $export->update(['status' => 'processing', 'failure_message' => null]);
        $stream = fopen('php://temp', 'w+b');
        fputcsv($stream, ['number', 'project', 'title', 'status', 'priority', 'assignee', 'milestone', 'estimate_hours', 'due_at'], ',', '"', '');
        foreach ($reports->rows($export->user, $export->workspace_id) as $task) {
            fputcsv($stream, [$task->number, $task->project->name, $task->title, $task->status->value, $task->priority->value, $task->assignee?->email, $task->milestone?->name, $task->estimate_hours, $task->due_at?->toDateString()], ',', '"', '');
        }
        rewind($stream);
        $path = 'report-exports/'.$export->workspace_id.'/tasks-'.$export->id.'.csv';
        Storage::disk($export->disk)->put($path, stream_get_contents($stream));
        fclose($stream);
        $export->update(['status' => 'ready', 'path' => $path]);
    }

    public function failed(?Throwable $exception): void
    {
        ReportExport::query()->whereKey($this->exportId)->update(['status' => 'failed', 'failure_message' => mb_substr((string) $exception?->getMessage(), 0, 2000)]);
    }
}
