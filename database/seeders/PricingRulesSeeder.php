<?php

namespace Database\Seeders;

use App\Models\PricingRule;
use App\Support\CatalogCache;
use Illuminate\Database\Seeder;

class PricingRulesSeeder extends Seeder
{
    public function run(): void
    {
        // Remove legacy default rule names so re-seeds don't leave overlapping ranges.
        PricingRule::query()
            ->whereIn('name', [
                'Short documents (per word)',
                'Standard documents (per page)',
                'Short documents (per page)',
                'Long documents (per word)',
            ])
            ->delete();

        $rules = [
            [
                'name' => 'Short documents (per page)',
                'min_pages' => null,
                'max_pages' => 5,
                'billing_unit' => PricingRule::BILLING_UNIT_PAGE,
                'rate_amount' => 50,
                'priority' => 10,
            ],
            [
                'name' => 'Long documents (per word)',
                'min_pages' => 6,
                'max_pages' => null,
                'billing_unit' => PricingRule::BILLING_UNIT_WORD,
                'rate_amount' => 0.50,
                'priority' => 10,
            ],
        ];

        foreach ($rules as $data) {
            PricingRule::query()->create([
                ...$data,
                'currency' => platformCurrency(),
                'is_active' => true,
            ]);
        }

        CatalogCache::flushPricingRules();
    }
}
