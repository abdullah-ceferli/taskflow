<?php

namespace Modules\Tasks\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class SyncTaskLabelsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['label_ids' => ['present', 'array', 'max:20'], 'label_ids.*' => ['integer', 'distinct']];
    }
}
