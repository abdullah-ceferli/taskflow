<?php

namespace Modules\Projects\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:projects,slug',
            'description' => 'nullable|string|max:1000',
            'starts_at' => 'nullable|date_format:Y-m-d H:i:s|before_or_equal:due_at',
            'due_at' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:starts_at',
        ];
    }
}
