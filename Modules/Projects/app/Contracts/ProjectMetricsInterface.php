<?php

namespace Modules\Projects\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Projects\Data\ProjectMetricsData;

interface ProjectMetricsInterface
{
    /** @return array{active: int, archived: int} */
    public function countsFor(User $user): array;

    /** @return Collection<int, object> */
    public function distributionFor(User $user): Collection;

    public function forUser(User $user): ProjectMetricsData;
}
