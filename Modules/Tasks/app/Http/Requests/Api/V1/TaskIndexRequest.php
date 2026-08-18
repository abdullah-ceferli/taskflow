<?php

namespace Modules\Tasks\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Tasks\Enums\TaskPriority;
use Modules\Tasks\Enums\TaskStatus;

final class TaskIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:180'], 'status' => ['nullable', Rule::enum(TaskStatus::class)], 'priority' => ['nullable', Rule::enum(TaskPriority::class)], 'project_id' => ['nullable', 'integer'], 'assignee_id' => ['nullable', 'integer'], 'due_before' => ['nullable', 'date'], 'sort' => ['nullable', 'string', Rule::in(['created_at', 'due_at', 'priority', 'status', 'number', '-created_at', '-due_at', '-priority', '-status', '-number'])], 'label_ids' => ['nullable', 'array', 'max:20'], 'label_ids.*' => ['integer', 'distinct'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']];
    }
}
