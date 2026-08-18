<?php

namespace Modules\Tasks\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTaskCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:5000']];
    }
}
