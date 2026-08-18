<?php

namespace App\Services;

use App\Enums\PermissionName;
use App\Enums\WebhookEvent;
use App\Exceptions\DomainRuleViolation;
use App\Jobs\DeliverWebhook;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class WebhookService
{
    public function __construct(private readonly CurrentWorkspace $current) {}

    /** @param list<string> $events
     * @return array{subscription: WebhookSubscription, secret: string}
     */
    public function create(User $actor, string $name, string $url, array $events): array
    {
        $this->authorizeManager($actor);
        $allowed = array_column(WebhookEvent::cases(), 'value');
        $events = array_values(array_unique(array_intersect($events, $allowed)));
        if ($events === [] || ! str_starts_with(strtolower($url), 'https://')) {
            throw new DomainRuleViolation('Webhook events and an HTTPS endpoint are required.');
        }

        $secret = Str::random(64);
        $subscription = WebhookSubscription::query()->create(['workspace_id' => $this->current->idFor($actor), 'created_by' => $actor->id, 'name' => trim($name), 'url' => $url, 'events' => $events, 'secret' => $secret, 'active' => true]);

        return compact('subscription', 'secret');
    }

    /** @return Collection<int, WebhookSubscription> */
    public function forActor(User $actor, bool $limitDeliveries = false): Collection
    {
        $this->authorizeManager($actor);

        return WebhookSubscription::query()
            ->where('workspace_id', $this->current->idFor($actor))
            ->with(['deliveries' => fn ($deliveries) => $limitDeliveries
                ? $deliveries->latest()->limit(10)
                : $deliveries->latest()])
            ->latest()
            ->get();
    }

    public function rotateSecret(WebhookSubscription $subscription, User $actor): string
    {
        $this->authorizeSubscription($subscription, $actor);
        $secret = Str::random(64);
        $subscription->update(['secret' => $secret]);

        return $secret;
    }

    public function deactivate(WebhookSubscription $subscription, User $actor): void
    {
        $this->authorizeSubscription($subscription, $actor);
        $subscription->update(['active' => false]);
    }

    public function replay(WebhookDelivery $delivery, User $actor): WebhookDelivery
    {
        $delivery->loadMissing('subscription');
        $this->authorizeSubscription($delivery->subscription, $actor);
        $replay = $delivery->subscription->deliveries()->create(['event' => $delivery->event, 'payload' => $delivery->payload, 'status' => 'pending']);
        DeliverWebhook::dispatch($replay->id);

        return $replay;
    }

    private function authorizeSubscription(WebhookSubscription $subscription, User $actor): void
    {
        $this->authorizeManager($actor);
        if ($subscription->workspace_id !== $this->current->idFor($actor)) {
            throw new DomainRuleViolation('The webhook does not belong to the current workspace.');
        }
    }

    private function authorizeManager(User $actor): void
    {
        if (! $actor->hasPermissionTo(PermissionName::IntegrationsManage->value) || ! $this->current->canManage($actor)) {
            throw new DomainRuleViolation('Only workspace managers may manage webhooks.');
        }
    }
}
