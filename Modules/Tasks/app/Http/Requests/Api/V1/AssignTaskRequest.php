<?php

namespace Modules\Tasks\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class AssignTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['assignee_id' => ['nullable', 'integer', 'exists:users,id']];
    }
}
