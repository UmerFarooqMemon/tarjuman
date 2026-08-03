<?php

namespace App\Http\Requests\Admin;

use App\Models\PricingRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePricingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:120'],
            'min_pages' => ['nullable', 'integer', 'min:1'],
            'max_pages' => ['nullable', 'integer', 'min:1'],
            'billing_unit' => ['required', Rule::in(PricingRule::billingUnits())],
            'rate_amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3', Rule::in(gccCurrencyCodes())],
            'priority' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'currency' => platformCurrency(),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $min = $this->input('min_pages');
            $max = $this->input('max_pages');

            if ($min !== null && $max !== null && (int) $min > (int) $max) {
                $validator->errors()->add('max_pages', __('general.max_pages_must_be_gte_min_pages'));
            }
        });
    }
}
