<?php

namespace Modules\Tasks\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTaskLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:80'], 'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'project_id' => ['nullable', 'integer']];
    }
}
