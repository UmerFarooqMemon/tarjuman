<?php

namespace App\Http\Requests\Admin;

use App\Models\VendorLanguagePair;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreVendorLanguagePairRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function getRedirectUrl(): string
    {
        $vendor = $this->route('vendor');

        return route('admin.vendors.language-pairs.index', $vendor);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pairs' => ['required', 'array', 'min:1'],
            'pairs.*.source_language_id' => [
                'required',
                'integer',
                'exists:languages,id',
            ],
            'pairs.*.target_language_id' => [
                'required',
                'integer',
                'exists:languages,id',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $vendorId = $this->route('vendor')?->id;
            $pairs = collect($this->input('pairs', []));
            $seen = [];

            foreach ($pairs as $index => $pair) {
                $sourceId = data_get($pair, 'source_language_id');
                $targetId = data_get($pair, 'target_language_id');

                if (! $sourceId || ! $targetId) {
                    continue;
                }

                if ((int) $sourceId === (int) $targetId) {
                    $validator->errors()->add(
                        "pairs.{$index}.target_language_id",
                        __('general.source_and_target_must_differ')
                    );
                }

                $key = ((int) $sourceId).':'.((int) $targetId);

                if (isset($seen[$key])) {
                    $validator->errors()->add(
                        "pairs.{$index}.source_language_id",
                        __('general.language_pair_duplicate_in_form')
                    );
                }

                $seen[$key] = true;

                $exists = VendorLanguagePair::query()
                    ->where('vendor_id', $vendorId)
                    ->where('source_language_id', $sourceId)
                    ->where('target_language_id', $targetId)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        "pairs.{$index}.source_language_id",
                        __('general.language_pair_already_exists')
                    );
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pairs.required' => __('general.at_least_one_language_pair_required'),
            'pairs.min' => __('general.at_least_one_language_pair_required'),
            'pairs.*.source_language_id.required' => __('general.source_language_required'),
            'pairs.*.target_language_id.required' => __('general.target_language_required'),
        ];
    }
}
