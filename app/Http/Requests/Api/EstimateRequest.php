<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $addOnIds = $this->input('add_on_ids', []);

        if (! is_array($addOnIds)) {
            $addOnIds = $addOnIds !== null && $addOnIds !== '' ? [$addOnIds] : [];
        }

        $this->merge([
            'add_on_ids' => array_values(array_filter($addOnIds, fn ($id) => $id !== null && $id !== '')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxFiles = max(1, (int) config('estimation.max_files', 10));
        $maxKb = max(1, (int) config('estimation.max_file_kb', 10240));
        $mimes = implode(',', config('estimation.allowed_mimes', ['pdf', 'docx', 'jpg', 'jpeg', 'png']));

        return [
            'documents' => ['required', 'array', 'min:1', 'max:'.$maxFiles],
            'documents.*' => [
                'required',
                'file',
                'mimes:'.$mimes,
                'max:'.$maxKb,
            ],
            'document_type_id' => [
                'required',
                'integer',
                Rule::exists('document_types', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'source_language_id' => [
                'required',
                'integer',
                Rule::exists('languages', 'id')->where(fn ($q) => $q->where('is_active', true)),
                'different:target_language_id',
            ],
            'target_language_id' => [
                'required',
                'integer',
                Rule::exists('languages', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'add_on_ids' => ['sometimes', 'array'],
            'add_on_ids.*' => [
                'integer',
                Rule::exists('add_ons', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'delivery_speed_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('delivery_speeds', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            // Pass the last estimate's request_id (or session_id) when recalculating
            // so prior quotes in the same checkout flow are superseded.
            'previous_request_id' => ['sometimes', 'nullable', 'uuid'],
            'session_id' => ['sometimes', 'nullable', 'uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'documents' => 'documents',
            'documents.*' => 'document',
            'document_type_id' => 'document type',
            'source_language_id' => 'source language',
            'target_language_id' => 'target language',
            'add_on_ids' => 'add-ons',
            'add_on_ids.*' => 'add-on',
            'delivery_speed_id' => 'delivery speed',
            'previous_request_id' => 'previous estimate',
            'session_id' => 'estimate session',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $maxFiles = max(1, (int) config('estimation.max_files', 10));
        $maxMb = max(1, (int) round(((int) config('estimation.max_file_kb', 10240)) / 1024));

        return [
            'documents.required' => 'Please upload at least one document.',
            'documents.array' => 'Please upload at least one document.',
            'documents.min' => 'Please upload at least one document.',
            'documents.max' => "You can upload a maximum of {$maxFiles} documents.",
            'documents.*.required' => 'Please upload a valid document file.',
            'documents.*.file' => 'Please upload a valid document file.',
            'documents.*.mimes' => 'Documents must be PDF, DOCX, JPG, or PNG.',
            'documents.*.max' => "Each document must be {$maxMb} MB or smaller.",
            'document_type_id.required' => 'Please select a document type.',
            'document_type_id.integer' => 'Please select a valid document type.',
            'document_type_id.exists' => 'The selected document type is invalid or unavailable.',
            'source_language_id.required' => 'Please select a source language.',
            'source_language_id.integer' => 'Please select a valid source language.',
            'source_language_id.exists' => 'The selected source language is invalid or unavailable.',
            'source_language_id.different' => 'Source and target languages must be different.',
            'target_language_id.required' => 'Please select a target language.',
            'target_language_id.integer' => 'Please select a valid target language.',
            'target_language_id.exists' => 'The selected target language is invalid or unavailable.',
            'add_on_ids.array' => 'Please select valid add-ons.',
            'add_on_ids.*.integer' => 'Please select a valid add-on.',
            'add_on_ids.*.exists' => 'One or more selected add-ons are invalid or unavailable.',
            'delivery_speed_id.integer' => 'Please select a valid delivery speed.',
            'delivery_speed_id.exists' => 'The selected delivery speed is invalid or unavailable.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $this->normalizeErrorKeys($validator->errors()->toArray());
        $firstMessage = collect($errors)->flatten()->first() ?: 'Please check the form and try again.';

        throw new HttpResponseException(response()->json([
            'message' => $firstMessage,
            'errors' => $errors,
        ], 422));
    }

    /**
     * Collapse indexed keys (documents.0) into form-friendly keys (documents).
     *
     * @param  array<string, list<string>>  $errors
     * @return array<string, list<string>>
     */
    protected function normalizeErrorKeys(array $errors): array
    {
        $normalized = [];

        foreach ($errors as $key => $messages) {
            $field = preg_replace('/\.\d+/', '', $key) ?: $key;
            $field = Str::of($field)->replaceMatches('/\.+/', '.')->trim('.')->toString();

            foreach ($messages as $message) {
                $clean = $this->humanizeMessage((string) $message, (string) $field);
                $normalized[$field] ??= [];

                if (! in_array($clean, $normalized[$field], true)) {
                    $normalized[$field][] = $clean;
                }
            }
        }

        return $normalized;
    }

    protected function humanizeMessage(string $message, string $field): string
    {
        // Replace leftover dotted attribute labels such as "documents.0".
        $message = preg_replace('/\bdocuments\.\d+\b/i', 'document', $message) ?? $message;
        $message = preg_replace('/\badd_on_ids\.\d+\b/i', 'add-on', $message) ?? $message;
        $message = str_replace(
            ['document_type_id', 'source_language_id', 'target_language_id', 'add_on_ids', 'delivery_speed_id'],
            ['document type', 'source language', 'target language', 'add-ons', 'delivery speed'],
            $message
        );

        if ($field === 'documents' && str_contains(strtolower($message), 'required')) {
            return 'Please upload at least one valid document.';
        }

        return $message;
    }
}
