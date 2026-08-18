<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateWorkspaceCapacityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['weekly_capacity_hours' => ['required', 'numeric', 'min:0', 'max:168']];
    }
}
