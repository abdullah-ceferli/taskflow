<?php

namespace Modules\Projects\Contracts;

use App\Models\User;
use Modules\Projects\Data\ProjectAccessData;

interface ProjectAccessInterface
{
    public function forActor(int $projectId, User $actor): ProjectAccessData;
}
