<?php

namespace Modules\Projects\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
            'starts_at' => 'nullable|date_format:Y-m-d|before_or_equal:due_at',
            'due_at' => 'nullable|date_format:Y-m-d|after_or_equal:starts_at',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Project name is required.',
            'slug.unique' => 'This slug is already taken.',
            'starts_at.before_or_equal' => 'Start date must be before or equal to due date.',
            'due_at.after_or_equal' => 'Due date must be after or equal to start date.',
        ];
    }
}
