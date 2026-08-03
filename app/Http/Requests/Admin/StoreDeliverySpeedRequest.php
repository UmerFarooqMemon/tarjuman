<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDeliverySpeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.delivery-speeds.index');
    }

    protected function prepareForValidation(): void
    {
        $translations = $this->input('translations', []);

        foreach (crudLocaleCodes() as $locale) {
            if (isset($translations[$locale]['name'])) {
                $translations[$locale]['name'] = trim((string) $translations[$locale]['name']);
            }
            if (isset($translations[$locale]['duration_label'])) {
                $translations[$locale]['duration_label'] = trim((string) $translations[$locale]['duration_label']);
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
            'price_amount' => ['required', 'numeric', 'min:0'],
            'min_hours' => ['nullable', 'integer', 'min:0'],
            'max_hours' => ['nullable', 'integer', 'min:0'],
        ];

        foreach (crudLocaleCodes() as $locale) {
            $rules["translations.{$locale}.name"] = [
                'required',
                'string',
                'max:120',
                Rule::unique('delivery_speed_translations', 'name')->where('locale', $locale),
            ];
            $rules["translations.{$locale}.duration_label"] = ['required', 'string', 'max:120'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $min = $this->input('min_hours');
            $max = $this->input('max_hours');

            if ($min !== null && $min !== '' && $max !== null && $max !== '' && (int) $min > (int) $max) {
                $validator->errors()->add('max_hours', __('general.max_hours_must_be_gte_min_hours'));
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [];

        foreach (crudLocaleCodes() as $locale) {
            $messages["translations.{$locale}.name.unique"] = __('general.delivery_speed_name_already_exists');
        }

        return $messages;
    }
}
