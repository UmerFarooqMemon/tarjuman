<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|max:32',
            'last_name' => 'nullable|max:32',
            'phone' => 'nullable|max:24',
            'image' => 'nullable|file|mimes:jpeg,jpg,png|max:5000',
            'password' => 'nullable|min:6|confirmed',
        ];
    }
}
