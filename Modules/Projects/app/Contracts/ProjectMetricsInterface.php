<?php

namespace Modules\Projects\Contracts;

use App\Models\User;
use Modules\Projects\Data\ProjectMetricsData;

interface ProjectMetricsInterface
{
    public function forUser(User $user): ProjectMetricsData;
}
