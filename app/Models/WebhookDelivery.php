<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $fillable = ['webhook_subscription_id', 'event', 'payload', 'status', 'attempts', 'response_status', 'response_body', 'last_attempt_at', 'delivered_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'last_attempt_at' => 'datetime', 'delivered_at' => 'datetime'];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(WebhookSubscription::class, 'webhook_subscription_id');
    }
}
