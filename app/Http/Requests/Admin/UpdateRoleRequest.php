<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $guard = config('admin_permissions.guard', 'admin');
        $roleId = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:125',
                Rule::unique('roles', 'name')
                    ->where(fn ($query) => $query->where('guard_name', $guard))
                    ->ignore($roleId),
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where(fn ($query) => $query->where('guard_name', $guard)),
            ],
        ];
    }
}
