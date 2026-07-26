<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'trn' => ['required', 'string', 'max:32', 'unique:vendors,trn'],
            'trade_license_no' => ['required', 'string', 'max:64'],
            'trade_license_expiry' => ['nullable', 'date'],
            'moj_registration_no' => ['required', 'string', 'max:64'],
            'email' => ['required', 'email:strict,filter', 'max:128'],
            'phone' => ['nullable', 'string', 'max:24'],
            'logo' => ['nullable', 'file', 'mimes:jpeg,jpg,png', 'max:5000'],
            'translations.en.legal_name' => ['required', 'string', 'max:191'],
            'translations.en.business_name' => ['nullable', 'string', 'max:191'],
            'translations.en.address' => ['nullable', 'string', 'max:1000'],
            'translations.ar.legal_name' => ['required', 'string', 'max:191'],
            'translations.ar.business_name' => ['nullable', 'string', 'max:191'],
            'translations.ar.address' => ['nullable', 'string', 'max:1000'],
            'owner.first_name' => ['required', 'string', 'max:32'],
            'owner.last_name' => ['nullable', 'string', 'max:32'],
            'owner.phone' => ['nullable', 'string', 'max:24'],
            'owner.email' => ['required', 'email:strict,filter', 'max:128', 'unique:vendor_users,email'],
            'owner.password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }
}
