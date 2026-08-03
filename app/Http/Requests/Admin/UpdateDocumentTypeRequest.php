<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.document-types.index');
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
        $documentTypeId = $this->route('document_type')?->id;
        $rules = [];

        foreach (crudLocaleCodes() as $locale) {
            $rules["translations.{$locale}.name"] = [
                'required',
                'string',
                'max:120',
                Rule::unique('document_type_translations', 'name')
                    ->where('locale', $locale)
                    ->ignore($documentTypeId, 'document_type_id'),
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
            $messages["translations.{$locale}.name.unique"] = __('general.document_type_name_already_exists');
        }

        return $messages;
    }
}
