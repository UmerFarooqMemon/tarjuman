<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePlatformSettingRequest extends FormRequest
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
            'order_payment_mode' => ['required', Rule::in(['quick', 'later'])],
            'order_assignment_mode' => ['required', Rule::in(['manual', 'open'])],
            'order_source_retention_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'order_delivery_retention_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'vendor_document_download_allowed' => ['sometimes', 'boolean'],
            'vendor_payout_schedule' => ['required', Rule::in(['weekly', 'monthly'])],
            'platform_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'platform_fee_fixed' => ['nullable', 'numeric', 'min:0'],

            'default_payment_gateway' => ['nullable', Rule::in(['paytabs', 'tap', 'noon', 'amazon_ps'])],

            'paytabs_enabled' => ['sometimes', 'boolean'],
            'paytabs_profile_id' => ['nullable', 'string', 'max:190'],
            'paytabs_server_key' => ['nullable', 'string', 'max:2000'],
            'paytabs_client_key' => ['nullable', 'string', 'max:2000'],
            'paytabs_test_mode' => ['sometimes', 'boolean'],

            'tap_enabled' => ['sometimes', 'boolean'],
            'tap_secret_key' => ['nullable', 'string', 'max:2000'],
            'tap_public_key' => ['nullable', 'string', 'max:2000'],
            'tap_test_mode' => ['sometimes', 'boolean'],

            'noon_enabled' => ['sometimes', 'boolean'],
            'noon_business_id' => ['nullable', 'string', 'max:190'],
            'noon_app_key' => ['nullable', 'string', 'max:2000'],
            'noon_app_secret' => ['nullable', 'string', 'max:2000'],
            'noon_test_mode' => ['sometimes', 'boolean'],

            'amazon_ps_enabled' => ['sometimes', 'boolean'],
            'amazon_ps_merchant_identifier' => ['nullable', 'string', 'max:190'],
            'amazon_ps_access_code' => ['nullable', 'string', 'max:2000'],
            'amazon_ps_sha_request' => ['nullable', 'string', 'max:2000'],
            'amazon_ps_sha_response' => ['nullable', 'string', 'max:2000'],
            'amazon_ps_test_mode' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'paytabs_enabled' => $this->boolean('paytabs_enabled'),
            'paytabs_test_mode' => $this->boolean('paytabs_test_mode'),
            'tap_enabled' => $this->boolean('tap_enabled'),
            'tap_test_mode' => $this->boolean('tap_test_mode'),
            'noon_enabled' => $this->boolean('noon_enabled'),
            'noon_test_mode' => $this->boolean('noon_test_mode'),
            'amazon_ps_enabled' => $this->boolean('amazon_ps_enabled'),
            'amazon_ps_test_mode' => $this->boolean('amazon_ps_test_mode'),
            'vendor_document_download_allowed' => $this->boolean('vendor_document_download_allowed'),
            'platform_fee_fixed' => 0,
            'default_payment_gateway' => $this->filled('default_payment_gateway')
                ? $this->input('default_payment_gateway')
                : null,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $default = $this->input('default_payment_gateway');
            if (! is_string($default) || $default === '') {
                return;
            }

            $enabledKey = match ($default) {
                'paytabs' => 'paytabs_enabled',
                'tap' => 'tap_enabled',
                'noon' => 'noon_enabled',
                'amazon_ps' => 'amazon_ps_enabled',
                default => null,
            };

            if ($enabledKey === null || ! $this->boolean($enabledKey)) {
                $validator->errors()->add(
                    'default_payment_gateway',
                    __('general.platform_default_gateway_must_be_enabled')
                );
            }

            if ($default === 'paytabs' && ! filled($this->input('paytabs_profile_id'))) {
                // Allow keeping existing credentials when blank secret fields are submitted.
                $settings = siteSettings();
                $profile = $this->input('paytabs_profile_id') ?: $settings?->paytabs_profile_id;
                $server = $this->input('paytabs_server_key') ?: $settings?->paytabs_server_key;
                if (! filled($profile) || ! filled($server)) {
                    $validator->errors()->add(
                        'paytabs_profile_id',
                        __('general.platform_default_gateway_needs_credentials')
                    );
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'order_payment_mode' => __('general.platform_payment_mode'),
            'order_assignment_mode' => __('general.platform_assignment_mode'),
            'order_source_retention_days' => __('general.platform_source_retention_days'),
            'order_delivery_retention_days' => __('general.platform_delivery_retention_days'),
            'vendor_document_download_allowed' => __('general.platform_vendor_document_download'),
            'vendor_payout_schedule' => __('general.platform_vendor_payout_schedule'),
            'platform_fee_percent' => __('general.platform_fee_percent'),
            'platform_fee_fixed' => __('general.platform_fee_fixed'),
            'default_payment_gateway' => __('general.platform_default_payment_gateway'),
        ];
    }
}
