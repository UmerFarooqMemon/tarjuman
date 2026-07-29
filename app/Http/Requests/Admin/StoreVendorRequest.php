<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
        $rules = [
            'trn' => ['required', 'string', 'max:32', 'unique:vendors,trn'],
            'trade_license_no' => ['required', 'string', 'max:64'],
            'trade_license_expiry' => ['nullable', 'date'],
            'moj_registration_no' => ['required', 'string', 'max:64'],
            'email' => ['required', 'email:strict,filter', 'max:128'],
            'phone' => ['nullable', 'string', 'max:24'],
            'logo' => ['nullable', 'file', 'mimes:jpeg,jpg,png', 'max:5000'],
            'owner.first_name' => ['required', 'string', 'max:32'],
            'owner.last_name' => ['nullable', 'string', 'max:32'],
            'owner.phone' => ['nullable', 'string', 'max:24'],
            'owner.email' => ['required', 'email:strict,filter', 'max:128', 'unique:vendor_users,email'],
            'owner.password' => ['required', 'string', 'min:6', 'confirmed'],
        ];

        foreach (crudLocaleCodes() as $locale) {
            $rules["translations.{$locale}.legal_name"] = ['required', 'string', 'max:191'];
            $rules["translations.{$locale}.business_name"] = ['nullable', 'string', 'max:191'];
            $rules["translations.{$locale}.address"] = ['nullable', 'string', 'max:1000'];
        }

        return $rules;
    }
}
