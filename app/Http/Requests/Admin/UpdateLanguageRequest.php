<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.languages.index');
    }

    protected function prepareForValidation(): void
    {
        $language = $this->route('language');

        if ($language && $language->hasLockedCode()) {
            $this->merge(['code' => $language->code]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $language = $this->route('language');
        $languageId = $language?->id;

        $codeRules = [
            'required',
            'string',
            'max:16',
            'alpha_dash',
            Rule::unique('languages', 'code')->ignore($languageId),
        ];

        if ($language?->hasLockedCode()) {
            $codeRules[] = Rule::in([$language->code]);
        }

        return [
            'code' => $codeRules,
            'native_name' => ['required', 'string', 'max:120'],
            'direction' => ['required', Rule::in(['ltr', 'rtl'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.in' => __('general.language_system_locale_code_locked'),
        ];
    }
}
