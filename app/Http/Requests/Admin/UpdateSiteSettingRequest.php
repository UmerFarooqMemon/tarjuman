<?php

namespace App\Http\Requests\Admin;

use App\Rules\E164Phone;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingRequest extends FormRequest
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
        $hex = ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'];
        $angle = ['nullable', 'integer', 'min:0', 'max:360'];

        return [
            'site_title' => 'required|max:190',
            'legal_business_name' => 'nullable|max:190',
            'trade_license_number' => 'nullable|max:64',
            'contact_email' => 'required|email:strict,filter|max:190',
            'footer_sentence' => 'max:65535',
            'contact_phone' => ['nullable', 'string', 'max:20', new E164Phone],
            'address' => 'nullable|max:190',
            'address_ar' => 'nullable|max:190',
            'currency' => ['required', 'string', 'size:3', 'in:'.implode(',', gccCurrencyCodes())],
            'facebook' => 'max:190',
            'twitter' => 'max:190',
            'pinterest' => 'max:190',
            'copyright' => 'max:65535',
            'footer_scripts' => 'max:65535',
            'logo' => ['nullable', 'file', 'extensions:jpeg,jpg,png,svg,webp', 'max:4096'],
            'logo_ar' => ['nullable', 'file', 'extensions:jpeg,jpg,png,svg,webp', 'max:4096'],
            'favicon' => ['nullable', 'file', 'extensions:jpeg,jpg,png,svg,webp,ico', 'max:2048'],
            'primary_color' => $hex,
            'primary_color_end' => $hex,
            'primary_color_angle' => $angle,
            'secondary_color' => $hex,
            'secondary_color_end' => $hex,
            'secondary_color_angle' => $angle,
            'primary_button_color' => $hex,
            'primary_button_color_end' => $hex,
            'primary_button_color_angle' => $angle,
            'secondary_button_color' => $hex,
            'secondary_button_color_end' => $hex,
            'secondary_button_color_angle' => $angle,
            'primary_button_text_color' => $hex,
            'secondary_button_text_color' => $hex,
            'primary_button_border_color' => $hex,
            'secondary_button_border_color' => $hex,
            'accepted_by_keep' => ['nullable', 'array'],
            'accepted_by_keep.*' => ['string', 'max:120'],
            'accepted_by_uploads' => ['nullable', 'array', 'max:24'],
            'accepted_by_uploads.*' => ['file', 'extensions:jpeg,jpg,png,svg,webp', 'max:4096'],
            'accepted_by_managed' => ['nullable', 'boolean'],
            'certified_by_keep' => ['nullable', 'array'],
            'certified_by_keep.*' => ['string', 'max:120'],
            'certified_by_uploads' => ['nullable', 'array', 'max:24'],
            'certified_by_uploads.*' => ['file', 'extensions:jpeg,jpg,png,svg,webp', 'max:4096'],
            'certified_by_managed' => ['nullable', 'boolean'],
            'regulated_by_keep' => ['nullable', 'array'],
            'regulated_by_keep.*' => ['string', 'max:120'],
            'regulated_by_uploads' => ['nullable', 'array', 'max:24'],
            'regulated_by_uploads.*' => ['file', 'extensions:jpeg,jpg,png,svg,webp', 'max:4096'],
            'regulated_by_managed' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'contact_phone' => $this->contact_phone === '' ? null : $this->contact_phone,
        ]);

        if ($this->filled('currency')) {
            $this->merge([
                'currency' => strtoupper((string) $this->input('currency')),
            ]);
        }

        foreach ([
            'primary_color_end',
            'secondary_color_end',
            'primary_button_color_end',
            'secondary_button_color_end',
        ] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'contact_email' => __('general.contact_email'),
            'contact_phone' => __('general.contact_phone'),
            'address' => __('general.address_en'),
            'address_ar' => __('general.address_ar'),
            'currency' => __('general.platform_currency'),
            'logo' => __('general.logo_en'),
            'logo_ar' => __('general.logo_ar'),
            'favicon' => __('general.favicon_logo'),
            'accepted_by_uploads' => __('general.settings_tab_accepted_by'),
            'accepted_by_uploads.*' => __('general.settings_gallery_image', ['gallery' => __('general.settings_tab_accepted_by')]),
            'certified_by_uploads' => __('general.settings_tab_certified_by'),
            'certified_by_uploads.*' => __('general.settings_gallery_image', ['gallery' => __('general.settings_tab_certified_by')]),
            'regulated_by_uploads' => __('general.settings_tab_regulated_by'),
            'regulated_by_uploads.*' => __('general.settings_gallery_image', ['gallery' => __('general.settings_tab_regulated_by')]),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'logo.extensions' => __('general.settings_logo_invalid_type', ['logo' => __('general.logo_en')]),
            'logo_ar.extensions' => __('general.settings_logo_invalid_type', ['logo' => __('general.logo_ar')]),
            'favicon.extensions' => __('general.settings_logo_invalid_type', ['logo' => __('general.favicon_logo')]),

            'accepted_by_uploads.*.extensions' => __('general.settings_gallery_invalid_type', ['gallery' => __('general.settings_tab_accepted_by')]),
            'accepted_by_uploads.*.max' => __('general.settings_gallery_too_large', ['gallery' => __('general.settings_tab_accepted_by')]),
            'accepted_by_uploads.max' => __('general.settings_gallery_too_many', ['gallery' => __('general.settings_tab_accepted_by')]),

            'certified_by_uploads.*.extensions' => __('general.settings_gallery_invalid_type', ['gallery' => __('general.settings_tab_certified_by')]),
            'certified_by_uploads.*.max' => __('general.settings_gallery_too_large', ['gallery' => __('general.settings_tab_certified_by')]),
            'certified_by_uploads.max' => __('general.settings_gallery_too_many', ['gallery' => __('general.settings_tab_certified_by')]),

            'regulated_by_uploads.*.extensions' => __('general.settings_gallery_invalid_type', ['gallery' => __('general.settings_tab_regulated_by')]),
            'regulated_by_uploads.*.max' => __('general.settings_gallery_too_large', ['gallery' => __('general.settings_tab_regulated_by')]),
            'regulated_by_uploads.max' => __('general.settings_gallery_too_many', ['gallery' => __('general.settings_tab_regulated_by')]),
        ];
    }
}
