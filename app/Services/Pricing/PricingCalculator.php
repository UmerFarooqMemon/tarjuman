<?php

namespace App\Services\Pricing;

use App\Models\PricingRule;
use App\Support\CatalogCache;
use InvalidArgumentException;

class PricingCalculator
{
    /**
     * Resolve the best matching platform pricing rule and compute the quote.
     *
     * Matching order:
     * 1. Active platform rules
     * 2. Rules whose page range covers $pageCount
     * 3. Highest priority first, then newest id
     *
     * @throws InvalidArgumentException when no rule matches
     */
    public function quote(int $pageCount, int $wordCount): PricingQuote
    {
        if ($pageCount < 1) {
            throw new InvalidArgumentException('Page count must be at least 1.');
        }

        if ($wordCount < 0) {
            throw new InvalidArgumentException('Word count cannot be negative.');
        }

        $rules = CatalogCache::activePricingRules()
            ->sort(function (PricingRule $a, PricingRule $b) {
                if ((int) $a->priority !== (int) $b->priority) {
                    return (int) $b->priority <=> (int) $a->priority;
                }

                return (int) $b->id <=> (int) $a->id;
            })
            ->values();

        $matched = $rules->first(
            fn (PricingRule $rule) => $rule->matchesPageCount($pageCount)
        );

        if (! $matched) {
            throw new InvalidArgumentException('No pricing rule matches the given page count.');
        }

        $quantity = $matched->billing_unit === PricingRule::BILLING_UNIT_PAGE
            ? $pageCount
            : $wordCount;

        $total = bcmul((string) $matched->rate_amount, (string) $quantity, 4);

        return new PricingQuote(
            rule: $matched,
            billingUnit: $matched->billing_unit,
            quantity: $quantity,
            unitRate: (string) $matched->rate_amount,
            totalAmount: $total,
            currency: $matched->currency,
            pageCount: $pageCount,
            wordCount: $wordCount,
        );
    }
}
