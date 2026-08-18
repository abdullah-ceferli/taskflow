<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class GlobalSearchResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['type' => $this['type'], 'id' => $this['id'], 'title' => $this['title'], 'excerpt' => $this['excerpt'], 'url' => $this['url']];
    }
}
