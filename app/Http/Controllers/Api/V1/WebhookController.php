<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreWebhookSubscriptionRequest;
use App\Http\Resources\WebhookSubscriptionResource;
use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WebhookController
{
    public function index(Request $request, WebhookService $webhooks)
    {
        return WebhookSubscriptionResource::collection($webhooks->forActor($request->user()));
    }

    public function store(StoreWebhookSubscriptionRequest $request, WebhookService $webhooks): JsonResponse
    {
        $created = $webhooks->create($request->user(), $request->string('name')->toString(), $request->string('url')->toString(), $request->array('events'));

        return response()->json(['data' => [...(new WebhookSubscriptionResource($created['subscription']))->resolve($request), 'signing_secret' => $created['secret']]], 201);
    }

    public function rotate(Request $request, WebhookSubscription $webhook, WebhookService $webhooks): JsonResponse
    {
        return response()->json(['data' => ['signing_secret' => $webhooks->rotateSecret($webhook, $request->user())]]);
    }

    public function destroy(Request $request, WebhookSubscription $webhook, WebhookService $webhooks)
    {
        $webhooks->deactivate($webhook, $request->user());

        return response()->noContent();
    }

    public function replay(Request $request, WebhookDelivery $delivery, WebhookService $webhooks): JsonResponse
    {
        return response()->json(['data' => ['delivery_id' => $webhooks->replay($delivery, $request->user())->id]], 202);
    }
}
