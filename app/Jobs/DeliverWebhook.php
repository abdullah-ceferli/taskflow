<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class DeliverWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public readonly int $deliveryId) {}

    public function backoff(): array
    {
        return [30, 120, 600, 1800];
    }

    public function handle(): void
    {
        $delivery = WebhookDelivery::query()->with('subscription')->findOrFail($this->deliveryId);
        if ($delivery->delivered_at || ! $delivery->subscription->active) {
            return;
        }

        $body = json_encode($delivery->payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $timestamp = (string) now()->timestamp;
        $signature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, $delivery->subscription->secret);
        $delivery->increment('attempts');
        $delivery->update(['status' => 'delivering', 'last_attempt_at' => now()]);
        $response = Http::timeout(10)->withHeaders(['X-TaskFlow-Event' => $delivery->event, 'X-TaskFlow-Delivery' => (string) $delivery->id, 'X-TaskFlow-Timestamp' => $timestamp, 'X-TaskFlow-Signature' => $signature])->withBody($body, 'application/json')->post($delivery->subscription->url);
        $delivery->update(['response_status' => $response->status(), 'response_body' => mb_substr($response->body(), 0, 2000)]);
        if (! $response->successful()) {
            $delivery->update(['status' => 'retrying']);
            throw new RuntimeException('Webhook endpoint returned HTTP '.$response->status().'.');
        }

        $delivery->update(['status' => 'delivered', 'delivered_at' => now()]);
    }

    public function failed(?Throwable $exception): void
    {
        WebhookDelivery::query()->whereKey($this->deliveryId)->update(['status' => 'failed', 'response_body' => mb_substr((string) $exception?->getMessage(), 0, 2000)]);
    }
}
