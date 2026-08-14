<?php

namespace Modules\Projects\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class AddProjectMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'member_role' => 'required|in:member,manager',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User is required.',
            'user_id.exists' => 'Selected user does not exist.',
            'member_role.required' => 'Member role is required.',
            'member_role.in' => 'Member role must be member or manager.',
        ];
    }
}
