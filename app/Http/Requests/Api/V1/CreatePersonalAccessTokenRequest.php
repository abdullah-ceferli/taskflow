<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\TokenAbility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreatePersonalAccessTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['email' => ['required', 'string', 'email'], 'password' => ['required', 'string'], 'device_name' => ['required', 'string', 'max:255'], 'abilities' => ['sometimes', 'array', 'min:1'], 'abilities.*' => [Rule::enum(TokenAbility::class)], 'expires_in_days' => ['sometimes', 'integer', 'min:1', 'max:365']];
    }
}
