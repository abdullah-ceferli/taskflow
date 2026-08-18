<?php

namespace App\Console\Commands;

use App\Services\MigrationCompatibilityInspector;
use Illuminate\Console\Command;

final class CheckMigrationCompatibility extends Command
{
    protected $signature = 'taskflow:migrations:check';

    protected $description = 'Reject destructive operations in migration up methods before deployment';

    public function handle(MigrationCompatibilityInspector $inspector): int
    {
        $paths = [database_path('migrations'), ...glob(base_path('Modules/*/database/migrations'), GLOB_ONLYDIR)];
        $issues = collect($paths)->flatMap(fn (string $path): array => $inspector->inspect($path))->all();

        if ($issues !== []) {
            $this->error('Migration compatibility check failed. Split destructive changes into an approved expand/contract release.');
            $this->table(['Migration', 'Rule'], array_map(fn (array $issue): array => [$issue['file'], $issue['rule']], $issues));

            return self::FAILURE;
        }

        $this->info('Migration compatibility check passed: all up methods are additive.');

        return self::SUCCESS;
    }
}
