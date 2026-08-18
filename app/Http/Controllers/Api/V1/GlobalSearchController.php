<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\GlobalSearchInterface;
use App\Http\Requests\GlobalSearchRequest;
use App\Http\Resources\GlobalSearchResultResource;

final class GlobalSearchController
{
    public function __invoke(GlobalSearchRequest $request, GlobalSearchInterface $search)
    {
        return GlobalSearchResultResource::collection($search->search($request->user(), $request->string('q')->trim()->toString()));
    }
}
