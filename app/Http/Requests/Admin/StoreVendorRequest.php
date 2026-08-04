<?php

namespace App\Http\Requests\Admin;

use App\Rules\E164Phone;
use Illuminate\Foundation\Http\FormRequest;

class StoreVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $owner = $this->input('owner', []);
        if (is_array($owner) && array_key_exists('phone', $owner) && $owner['phone'] === '') {
            $owner['phone'] = null;
        }

        $this->merge([
            'phone' => $this->phone === '' ? null : $this->phone,
            'owner' => $owner,
        ]);
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
            'phone' => ['nullable', 'string', 'max:20', new E164Phone],
            'logo' => ['nullable', 'file', 'mimes:jpeg,jpg,png', 'max:5000'],
            'owner.first_name' => ['required', 'string', 'max:32'],
            'owner.last_name' => ['nullable', 'string', 'max:32'],
            'owner.phone' => ['nullable', 'string', 'max:20', new E164Phone],
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
