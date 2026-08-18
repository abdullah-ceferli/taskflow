<?php

namespace App\Jobs;

use App\Services\OrphanAttachmentCleanupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class PruneOrphanTaskAttachmentsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 3600;

    public function __construct(public readonly int $retentionDays = 7) {}

    public function handle(OrphanAttachmentCleanupService $cleanup): void
    {
        $cleanup->delete($cleanup->eligiblePaths($this->retentionDays));
    }
}
