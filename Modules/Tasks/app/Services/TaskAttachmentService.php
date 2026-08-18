<?php

namespace Modules\Tasks\Services;

use App\Exceptions\DomainRuleViolation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Activity\Enums\ActivityEvent;
use Modules\Activity\Services\ActivityRecorder;
use Modules\Tasks\Contracts\MalwareScannerInterface;
use Modules\Tasks\Enums\AttachmentScanStatus;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAttachment;
use Modules\Tasks\Repositories\Contracts\TaskAttachmentRepositoryInterface;

class TaskAttachmentService
{
    private readonly MalwareScannerInterface $scanner;

    public function __construct(private readonly TaskAttachmentRepositoryInterface $attachments, private readonly ActivityRecorder $activity, ?MalwareScannerInterface $scanner = null)
    {
        $this->scanner = $scanner ?? app(MalwareScannerInterface::class);
    }

    public function upload(Task $task, User $actor, UploadedFile $file): TaskAttachment
    {
        $used = TaskAttachment::query()->whereHas('task.project', fn ($projects) => $projects->where('workspace_id', $task->project->workspace_id))->sum('size');
        if ($used + $file->getSize() > (int) config('taskflow.attachments.workspace_quota_bytes')) {
            throw new DomainRuleViolation('The workspace attachment storage quota has been exceeded.');
        }

        $scanStatus = $this->scanner->scan($file);
        if ($scanStatus === AttachmentScanStatus::Infected) {
            throw new DomainRuleViolation('The attachment failed malware scanning.');
        }

        $disk = 'local';
        $path = $file->store("task-attachments/{$task->id}", $disk);
        $attachment = null;
        try {
            $attachment = DB::transaction(function () use ($task, $actor, $file, $disk, $path) {
                $attachment = $this->attachments->save(new TaskAttachment(['task_id' => $task->id, 'uploaded_by' => $actor->id, 'disk' => $disk, 'path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType() ?? 'application/octet-stream', 'size' => $file->getSize()]));
                $this->activity->record(ActivityEvent::AttachmentUploaded, $actor, $attachment, ['project_id' => $task->project_id, 'task_id' => $task->id, 'attachment_id' => $attachment->id, 'filename' => $attachment->original_name]);

                return $attachment;
            });
            $attachment->forceFill([
                'metadata_version' => 1,
                'checksum' => hash_file('sha256', Storage::disk($disk)->path($path)),
                'scan_status' => $scanStatus->value,
            ])->save();

            return $attachment;
        } catch (\Throwable $e) {
            Storage::disk($disk)->delete($path);
            $attachment?->delete();
            throw $e;
        }
    }

    /** @param list<UploadedFile> $files
     * @return list<TaskAttachment>
     */
    public function uploadMany(Task $task, User $actor, array $files): array
    {
        if (count($files) > (int) config('taskflow.attachments.max_files_per_request')) {
            throw new DomainRuleViolation('Too many attachments were supplied in one request.');
        }

        return array_map(fn (UploadedFile $file): TaskAttachment => $this->upload($task, $actor, $file), $files);
    }

    public function download(TaskAttachment $attachment, User $actor)
    {
        $attachment->forceFill(['download_count' => $attachment->download_count + 1, 'last_downloaded_at' => now()])->save();
        $this->activity->record(ActivityEvent::AttachmentDownloaded, $actor, $attachment, ['project_id' => $attachment->task->project_id, 'task_id' => $attachment->task_id, 'attachment_id' => $attachment->id, 'filename' => $attachment->original_name]);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    public function preview(TaskAttachment $attachment)
    {
        abort_unless(in_array($attachment->mime_type, ['application/pdf', 'image/png', 'image/jpeg', 'image/webp', 'text/plain'], true), 415);

        return Storage::disk($attachment->disk)->response($attachment->path, $attachment->original_name, ['Content-Type' => $attachment->mime_type]);
    }

    public function delete(TaskAttachment $attachment, User $actor): void
    {
        $disk = Storage::disk($attachment->disk);
        $path = $attachment->path;
        $contents = $disk->fileExists($path) ? $disk->get($path) : null;

        try {
            DB::transaction(function () use ($attachment, $actor, $disk, $path): void {
                $properties = ['project_id' => $attachment->task->project_id, 'task_id' => $attachment->task_id, 'attachment_id' => $attachment->id, 'filename' => $attachment->original_name];
                $disk->delete($path);
                $this->attachments->delete($attachment);
                $this->activity->record(ActivityEvent::AttachmentDeleted, $actor, $attachment, $properties);
            });
        } catch (\Throwable $exception) {
            if ($contents !== null && ! $disk->fileExists($path)) {
                $disk->put($path, $contents);
            }

            throw $exception;
        }
    }
}
