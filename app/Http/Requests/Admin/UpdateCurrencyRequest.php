<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.currencies.index');
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge(['code' => strtoupper(trim((string) $this->code))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $currencyId = $this->route('currency')?->id;

        $rules = [
            'code' => [
                'required',
                'string',
                'size:3',
                'alpha',
                Rule::unique('currencies', 'code')->ignore($currencyId),
            ],
            'icon_file' => ['nullable', 'file', 'max:100', 'extensions:svg'],
            'decimals' => ['required', 'integer', 'min:0', 'max:6'],
        ];

        foreach (crudLocaleCodes() as $locale) {
            $rules["translations.{$locale}.name"] = ['required', 'string', 'max:120'];
        }

        return $rules;
    }
}
