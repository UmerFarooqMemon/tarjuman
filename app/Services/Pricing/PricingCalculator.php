<?php

namespace App\Services\Pricing;

use App\Models\Vendor;
use App\Models\VendorLanguagePair;
use App\Models\VendorPricingRule;
use App\Support\CatalogCache;
use InvalidArgumentException;

class PricingCalculator
{
    /**
     * Resolve the best matching pricing rule and compute the quote.
     *
     * Matching order:
     * 1. Active rules for the vendor + language pair
     * 2. Optional document_type_id filter (null rules match any document type)
     * 3. Rules whose page range covers $pageCount
     * 4. Highest priority first, then newest id
     *
     * @throws InvalidArgumentException when no rule matches
     */
    public function quote(
        Vendor|int $vendor,
        VendorLanguagePair|int $languagePair,
        int $pageCount,
        int $wordCount,
        ?int $documentTypeId = null,
    ): PricingQuote {
        $vendorId = $vendor instanceof Vendor ? $vendor->id : $vendor;
        $pairId = $languagePair instanceof VendorLanguagePair ? $languagePair->id : $languagePair;

        if ($pageCount < 1) {
            throw new InvalidArgumentException('Page count must be at least 1.');
        }

        if ($wordCount < 0) {
            throw new InvalidArgumentException('Word count cannot be negative.');
        }

        $rules = CatalogCache::vendorPricingRules($vendorId, activeOnly: true)
            ->filter(fn (VendorPricingRule $rule) => (int) $rule->vendor_language_pair_id === (int) $pairId)
            ->filter(function (VendorPricingRule $rule) use ($documentTypeId) {
                if ($rule->document_type_id === null) {
                    return true;
                }

                return $documentTypeId !== null
                    && (int) $rule->document_type_id === (int) $documentTypeId;
            })
            ->sort(function (VendorPricingRule $a, VendorPricingRule $b) {
                $aSpecific = $a->document_type_id === null ? 1 : 0;
                $bSpecific = $b->document_type_id === null ? 1 : 0;

                if ($aSpecific !== $bSpecific) {
                    return $aSpecific <=> $bSpecific;
                }

                if ((int) $a->priority !== (int) $b->priority) {
                    return (int) $b->priority <=> (int) $a->priority;
                }

                return (int) $b->id <=> (int) $a->id;
            })
            ->values();

        $matched = $rules->first(
            fn (VendorPricingRule $rule) => $rule->matchesPageCount($pageCount)
        );

        if (! $matched) {
            throw new InvalidArgumentException('No pricing rule matches the given page count.');
        }

        $quantity = $matched->billing_unit === VendorPricingRule::BILLING_UNIT_PAGE
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
