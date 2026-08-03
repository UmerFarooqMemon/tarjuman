<?php

namespace Database\Seeders;

use App\Models\PricingRule;
use App\Support\CatalogCache;
use Illuminate\Database\Seeder;

class PricingRulesSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'name' => 'Short documents (per word)',
                'min_pages' => null,
                'max_pages' => 1,
                'billing_unit' => PricingRule::BILLING_UNIT_WORD,
                'rate_amount' => 0.50,
                'priority' => 10,
            ],
            [
                'name' => 'Standard documents (per page)',
                'min_pages' => 2,
                'max_pages' => null,
                'billing_unit' => PricingRule::BILLING_UNIT_PAGE,
                'rate_amount' => 50,
                'priority' => 10,
            ],
        ];

        foreach ($rules as $data) {
            $rule = PricingRule::query()
                ->where('name', $data['name'])
                ->first();

            if (! $rule) {
                $rule = new PricingRule;
            }

            $rule->fill([
                ...$data,
                'currency' => platformCurrency(),
                'is_active' => true,
            ]);
            $rule->save();
        }

        CatalogCache::flushPricingRules();
    }
}
