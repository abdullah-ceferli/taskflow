<?php

namespace Modules\Activity\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $properties = $this->properties?->toArray() ?? [];
        foreach (['password', 'token', 'secret', 'plain_text_token'] as $key) {
            unset($properties[$key]);
        }

        return ['id' => $this->id, 'event' => $this->event, 'description' => $this->description, 'causer_id' => $this->causer_id, 'subject_id' => $this->subject_id, 'subject_type' => $this->subject_type, 'properties' => $properties, 'created_at' => $this->created_at?->toISOString()];
    }
}
