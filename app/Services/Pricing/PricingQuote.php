<?php

namespace App\Services\Pricing;

use App\Models\PricingRule;

readonly class PricingQuote
{
    public function __construct(
        public PricingRule $rule,
        public string $billingUnit,
        public int $quantity,
        public string $unitRate,
        public string $totalAmount,
        public string $currency,
        public int $pageCount,
        public int $wordCount,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'rule_id' => $this->rule->id,
            'rule_name' => $this->rule->name,
            'billing_unit' => $this->billingUnit,
            'quantity' => $this->quantity,
            'unit_rate' => $this->unitRate,
            'amount' => $this->totalAmount,
            'currency' => $this->currency,
            'page_count' => $this->pageCount,
            'word_count' => $this->wordCount,
        ];
    }
}
