<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdministartorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $guard = config('admin_permissions.guard', 'admin');

        return [
            'first_name' => 'required|max:32',
            'last_name' => 'max:32',
            'phone' => 'max:24',
            'image' => 'file|mimes:jpeg,jpg,png|max:5000',
            'password' => 'nullable|min:6|confirmed',
            'role' => [
                'nullable',
                Rule::exists('roles', 'name')->where(fn ($query) => $query->where('guard_name', $guard)),
            ],
        ];
    }
}
