<?php

namespace Modules\Projects\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Projects\Enums\ProjectMemberRole;

final class StoreProjectMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['user_id' => ['required', 'integer', 'exists:users,id'], 'member_role' => ['required', Rule::enum(ProjectMemberRole::class)]];
    }
}
