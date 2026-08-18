<?php

namespace App\Http\Controllers;

use App\Enums\WebhookEvent;
use App\Http\Requests\StoreWebhookSubscriptionRequest;
use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use App\Services\WebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class WebhookController extends Controller
{
    public function index(Request $request, WebhookService $webhooks): View
    {
        return view('webhooks.index', ['subscriptions' => $webhooks->forActor($request->user(), true), 'events' => WebhookEvent::cases()]);
    }

    public function store(StoreWebhookSubscriptionRequest $request, WebhookService $webhooks): RedirectResponse
    {
        $created = $webhooks->create($request->user(), $request->string('name')->toString(), $request->string('url')->toString(), $request->array('events'));

        return back()->with('success', 'Webhook created. Copy the signing secret now.')->with('webhook_secret', $created['secret']);
    }

    public function rotate(Request $request, WebhookSubscription $webhook, WebhookService $webhooks): RedirectResponse
    {
        return back()->with('success', 'Signing secret rotated.')->with('webhook_secret', $webhooks->rotateSecret($webhook, $request->user()));
    }

    public function destroy(Request $request, WebhookSubscription $webhook, WebhookService $webhooks): RedirectResponse
    {
        $webhooks->deactivate($webhook, $request->user());

        return back()->with('success', 'Webhook disabled.');
    }

    public function replay(Request $request, WebhookDelivery $delivery, WebhookService $webhooks): RedirectResponse
    {
        $webhooks->replay($delivery, $request->user());

        return back()->with('success', 'Webhook replay queued.');
    }
}
