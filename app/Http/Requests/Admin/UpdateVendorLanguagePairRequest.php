<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorLanguagePairRequest extends FormRequest
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
        $vendorId = $this->route('vendor')?->id;
        $pairId = $this->route('language_pair')?->id;

        return [
            'source_language_id' => [
                'required',
                'integer',
                'exists:languages,id',
                'different:target_language_id',
                Rule::unique('vendor_language_pairs', 'source_language_id')
                    ->ignore($pairId)
                    ->where(fn ($query) => $query
                        ->where('vendor_id', $vendorId)
                        ->where('target_language_id', $this->input('target_language_id'))),
            ],
            'target_language_id' => ['required', 'integer', 'exists:languages,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'source_language_id.unique' => __('general.language_pair_already_exists'),
            'source_language_id.different' => __('general.source_and_target_must_differ'),
        ];
    }
}
