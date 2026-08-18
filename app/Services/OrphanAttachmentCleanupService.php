<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Modules\Tasks\Models\TaskAttachment;

final class OrphanAttachmentCleanupService
{
    /** @return Collection<int, string> */
    public function eligiblePaths(int $retentionDays): Collection
    {
        $disk = Storage::disk('local');
        $knownPaths = TaskAttachment::query()->where('disk', 'local')->pluck('path')->all();
        $cutoff = now()->subDays($retentionDays)->getTimestamp();

        return collect($disk->allFiles('task-attachments'))
            ->reject(fn (string $path): bool => in_array($path, $knownPaths, true))
            ->filter(fn (string $path): bool => $disk->lastModified($path) <= $cutoff)
            ->values();
    }

    /** @param iterable<string> $paths */
    public function delete(iterable $paths): int
    {
        $count = 0;
        foreach ($paths as $path) {
            if (Storage::disk('local')->delete($path)) {
                $count++;
            }
        }

        return $count;
    }
}
