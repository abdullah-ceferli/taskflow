<?php

namespace Modules\Projects\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:projects,slug,' . $this->route('project')?->id,
            'description' => 'nullable|string|max:1000',
            'status' => 'sometimes|in:draft,active,completed,archived',
            'starts_at' => 'nullable|date_format:Y-m-d|before_or_equal:due_at',
            'due_at' => 'nullable|date_format:Y-m-d|after_or_equal:starts_at',
        ];
    }
}
