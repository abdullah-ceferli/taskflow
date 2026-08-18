<?php

namespace Modules\Projects\Data;

use Illuminate\Support\Collection;

final readonly class ProjectMetricsData
{
    /** @param Collection<int, object> $distribution */
    public function __construct(
        public int $active,
        public int $archived,
        public Collection $distribution,
    ) {}
}
