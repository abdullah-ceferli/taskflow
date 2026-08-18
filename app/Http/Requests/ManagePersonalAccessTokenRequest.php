<?php

namespace App\Http\Requests;

use App\Enums\TokenAbility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ManagePersonalAccessTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['current_password' => ['required', 'string'], 'device_name' => ['required', 'string', 'max:255'], 'abilities' => ['required', 'array', 'min:1'], 'abilities.*' => [Rule::enum(TokenAbility::class)], 'expires_in_days' => ['required', 'integer', 'min:1', 'max:365']];
    }
}
