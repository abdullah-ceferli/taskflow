<?php

namespace Modules\Projects\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProjectCollection extends ResourceCollection
{
    public $collects = ProjectResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage(),
            ],
        ];
    }
}
