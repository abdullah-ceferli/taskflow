<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class WebhookSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'url' => $this->url, 'events' => $this->events, 'active' => $this->active, 'created_at' => $this->created_at?->toISOString(), 'deliveries' => $this->whenLoaded('deliveries', fn () => $this->deliveries->map(fn ($delivery) => ['id' => $delivery->id, 'event' => $delivery->event, 'status' => $delivery->status, 'attempts' => $delivery->attempts, 'response_status' => $delivery->response_status, 'created_at' => $delivery->created_at?->toISOString()]))];
    }
}
