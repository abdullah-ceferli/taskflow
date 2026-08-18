<?php

namespace App\Console\Commands;

use App\Support\ModuleHealthCheck;
use Illuminate\Console\Command;

final class CheckTaskFlowModules extends Command
{
    protected $signature = 'taskflow:modules:check';

    protected $description = 'Verify required TaskFlow modules and their public contract bindings';

    public function handle(ModuleHealthCheck $health): int
    {
        $result = $health->inspect();

        foreach ($result['modules'] as $name => $enabled) {
            $this->line(sprintf('Module %-12s %s', $name, $enabled ? 'enabled' : 'MISSING/DISABLED'));
        }

        foreach ($result['contracts'] as $contract => $resolved) {
            $this->line(sprintf('Contract %-70s %s', $contract, $resolved ? 'resolved' : 'UNRESOLVED'));
        }

        if (! $result['healthy']) {
            $this->error('TaskFlow module health check failed. Enable required modules and restore their service-provider bindings.');

            return self::FAILURE;
        }

        $this->info('TaskFlow module health check passed.');

        return self::SUCCESS;
    }
}
