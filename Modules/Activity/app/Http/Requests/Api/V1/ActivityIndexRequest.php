<?php

namespace Modules\Activity\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class ActivityIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['event' => ['nullable', 'string', 'max:100'], 'project_id' => ['nullable', 'integer'], 'task_id' => ['nullable', 'integer'], 'actor_id' => ['nullable', 'integer'], 'date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date', 'after_or_equal:date_from']];
    }
}
