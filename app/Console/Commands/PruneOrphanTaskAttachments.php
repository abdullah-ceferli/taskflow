<?php

namespace App\Console\Commands;

use App\Services\OrphanAttachmentCleanupService;
use Illuminate\Console\Command;

class PruneOrphanTaskAttachments extends Command
{
    protected $signature = 'taskflow:attachments:prune-orphans {--retention-days=7 : Keep unreferenced files newer than this many days} {--force : Permanently delete eligible orphan files}';

    protected $description = 'Report or delete private task attachment files with no database record.';

    public function handle(OrphanAttachmentCleanupService $cleanup): int
    {
        $retentionDays = (int) $this->option('retention-days');
        if ($retentionDays < 0) {
            $this->error('The retention period cannot be negative.');

            return self::FAILURE;
        }

        $orphanPaths = $cleanup->eligiblePaths($retentionDays);

        if ($orphanPaths->isEmpty()) {
            $this->info('No eligible orphan task attachments were found.');

            return self::SUCCESS;
        }

        foreach ($orphanPaths as $path) {
            if ($this->option('force')) {
                $cleanup->delete([$path]);
                $this->line("Deleted: {$path}");
            } else {
                $this->line("Would delete: {$path}");
            }
        }

        $this->info($this->option('force') ? 'Orphan attachment cleanup completed.' : 'Dry run completed. Re-run with --force to delete these files.');

        return self::SUCCESS;
    }
}
