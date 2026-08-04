<?php

namespace App\Http\Requests\Admin;

use App\Rules\E164Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorRequest extends FormRequest
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
        $vendor = $this->route('vendor');
        $vendorId = $vendor instanceof \App\Models\Vendor ? $vendor->id : $vendor;
        $ownerId = $this->input('owner.id');

        $rules = [
            'trn' => [
                'required',
                'string',
                'max:32',
                Rule::unique('vendors', 'trn')->ignore($vendorId),
            ],
            'trade_license_no' => ['required', 'string', 'max:64'],
            'trade_license_expiry' => ['nullable', 'date'],
            'moj_registration_no' => ['required', 'string', 'max:64'],
            'email' => ['required', 'email:strict,filter', 'max:128'],
            'phone' => ['nullable', 'string', 'max:20', new E164Phone],
            'logo' => ['nullable', 'file', 'mimes:jpeg,jpg,png', 'max:5000'],
            'previous_logo' => ['nullable', 'string', 'max:65'],
            'owner.id' => ['required', 'integer', Rule::exists('vendor_users', 'id')->where('vendor_id', $vendorId)],
            'owner.first_name' => ['required', 'string', 'max:32'],
            'owner.last_name' => ['nullable', 'string', 'max:32'],
            'owner.phone' => ['nullable', 'string', 'max:20', new E164Phone],
            'owner.email' => [
                'required',
                'email:strict,filter',
                'max:128',
                Rule::unique('vendor_users', 'email')->ignore($ownerId),
            ],
            'owner.password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ];

        foreach (crudLocaleCodes() as $locale) {
            $rules["translations.{$locale}.legal_name"] = ['required', 'string', 'max:191'];
            $rules["translations.{$locale}.business_name"] = ['nullable', 'string', 'max:191'];
            $rules["translations.{$locale}.address"] = ['nullable', 'string', 'max:1000'];
        }

        return $rules;
    }
}
