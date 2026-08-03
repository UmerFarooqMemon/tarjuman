<?php

namespace App\Http\Requests\Admin;

use App\Models\AddOn;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAddOnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.add-ons.index');
    }

    protected function prepareForValidation(): void
    {
        $translations = $this->input('translations', []);

        foreach (crudLocaleCodes() as $locale) {
            if (isset($translations[$locale]['name'])) {
                $translations[$locale]['name'] = trim((string) $translations[$locale]['name']);
            }
        }

        $this->merge(['translations' => $translations]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'pricing_mode' => ['required', Rule::in(AddOn::pricingModes())],
            'default_amount' => ['required', 'numeric', 'min:0'],
        ];

        foreach (crudLocaleCodes() as $locale) {
            $rules["translations.{$locale}.name"] = [
                'required',
                'string',
                'max:120',
                Rule::unique('add_on_translations', 'name')->where('locale', $locale),
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [];

        foreach (crudLocaleCodes() as $locale) {
            $messages["translations.{$locale}.name.unique"] = __('general.add_on_name_already_exists');
        }

        return $messages;
    }
}
