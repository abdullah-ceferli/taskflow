<?php

namespace Modules\Tasks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Tasks\Enums\RecurrenceFrequency;
use Modules\Tasks\Enums\TaskPriority;

final class StoreRecurringTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:180'],
            'description' => ['nullable', 'string', 'max:10000'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'milestone_id' => ['nullable', 'integer', 'exists:project_milestones,id'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'estimate_hours' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'frequency' => ['required', Rule::enum(RecurrenceFrequency::class)],
            'interval' => ['required', 'integer', 'min:1', 'max:365'],
            'timezone' => ['required', 'timezone'],
            'due_offset_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'starts_at' => ['required', 'date'],
        ];
    }
}
