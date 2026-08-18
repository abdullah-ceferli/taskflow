<?php

namespace Modules\Tasks\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Tasks\Enums\TaskPriority;

final class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['project_id' => ['required', 'integer', 'exists:projects,id'], 'title' => ['required', 'string', 'min:3', 'max:180'], 'description' => ['nullable', 'string', 'max:10000'], 'assignee_id' => ['nullable', 'integer', 'exists:users,id'], 'priority' => ['required', Rule::enum(TaskPriority::class)], 'due_at' => ['nullable', 'date']];
    }
}
