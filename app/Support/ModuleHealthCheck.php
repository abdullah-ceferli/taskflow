<?php

namespace App\Support;

use Modules\Projects\Contracts\ProjectAccessInterface;
use Modules\Projects\Contracts\ProjectMetricsInterface;
use Modules\Projects\Repositories\Contracts\ProjectRepositoryInterface;
use Modules\Tasks\Contracts\TaskMetricsInterface;
use Modules\Tasks\Repositories\Contracts\TaskAttachmentRepositoryInterface;
use Modules\Tasks\Repositories\Contracts\TaskCommentRepositoryInterface;
use Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;
use Nwidart\Modules\Facades\Module;
use Throwable;

final class ModuleHealthCheck
{
    /** @return array{healthy: bool, modules: array<string, bool>, contracts: array<string, bool>} */
    public function inspect(): array
    {
        $modules = collect(['Projects', 'Tasks', 'Activity', 'Dashboard'])
            ->mapWithKeys(fn (string $name): array => [$name => Module::isEnabled($name)])
            ->all();

        $contracts = collect([
            ProjectAccessInterface::class,
            ProjectMetricsInterface::class,
            ProjectRepositoryInterface::class,
            TaskMetricsInterface::class,
            TaskRepositoryInterface::class,
            TaskCommentRepositoryInterface::class,
            TaskAttachmentRepositoryInterface::class,
        ])->mapWithKeys(fn (string $contract): array => [$contract => $this->resolves($contract)])->all();

        return [
            'healthy' => ! in_array(false, [...array_values($modules), ...array_values($contracts)], true),
            'modules' => $modules,
            'contracts' => $contracts,
        ];
    }

    private function resolves(string $contract): bool
    {
        if (! app()->bound($contract)) {
            return false;
        }

        try {
            app()->make($contract);

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
