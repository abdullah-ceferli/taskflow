<?php

namespace App\Http\Requests;

use App\Enums\WebhookEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreWebhookSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'min:2', 'max:120'], 'url' => ['required', 'url', 'max:2048', 'starts_with:https://'], 'events' => ['required', 'array', 'min:1'], 'events.*' => [Rule::enum(WebhookEvent::class)]];
    }
}
