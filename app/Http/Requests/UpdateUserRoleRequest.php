<?php

namespace App\Http\Requests;

use App\Enums\PermissionName;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(PermissionName::UserRolesManage->value) === true;
    }

    public function rules(): array
    {
        return ['role' => ['required', Rule::enum(UserRole::class)]];
    }
}
