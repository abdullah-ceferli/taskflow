<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'event' => ['required', Rule::in(['task.assigned', 'task.status_changed', 'task.due_soon', 'comment.mentioned'])],
            'in_app' => ['nullable', 'boolean'],
            'email' => ['nullable', 'boolean'],
        ];
    }
}
