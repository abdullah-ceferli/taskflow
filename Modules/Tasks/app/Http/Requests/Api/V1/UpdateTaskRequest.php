<?php

namespace Modules\Tasks\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Tasks\Enums\TaskPriority;

final class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'min:3', 'max:180'], 'description' => ['nullable', 'string', 'max:10000'], 'milestone_id' => ['nullable', 'integer', 'exists:project_milestones,id'], 'priority' => ['required', Rule::enum(TaskPriority::class)], 'estimate_hours' => ['nullable', 'numeric', 'min:0', 'max:9999'], 'due_at' => ['nullable', 'date']];
    }
}
