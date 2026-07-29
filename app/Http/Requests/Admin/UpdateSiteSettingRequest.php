<?php

namespace App\Http\Requests\Admin;

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
            'contact_email' => 'required|email:strict,filter|max:190',
            'footer_sentence' => 'max:65535',
            'contact_phone' => 'max:190',
            'address' => 'max:190',
            'currency' => ['required', 'string', 'size:3', 'in:'.implode(',', gccCurrencyCodes())],
            'facebook' => 'max:190',
            'twitter' => 'max:190',
            'pinterest' => 'max:190',
            'copyright' => 'max:65535',
            'footer_scripts' => 'max:65535',
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
        ];
    }

    protected function prepareForValidation(): void
    {
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
}
