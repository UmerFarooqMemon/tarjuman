<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Support\CatalogCache;
use Illuminate\Database\Seeder;

class CurrenciesTableSeeder extends Seeder
{
    public function run(): void
    {
        $sort = 0;

        foreach (config('currencies.gcc', []) as $code => $meta) {
            $currency = Currency::updateOrCreate(
                ['code' => strtoupper($code)],
                [
                    'symbol' => $meta['symbol'] ?? null,
                    'symbol_native' => $meta['symbol_native'] ?? null,
                    'icon' => $meta['icon'] ?? null,
                    'decimals' => (int) ($meta['decimals'] ?? 2),
                    'country' => $meta['country'] ?? null,
                    'sort_order' => $sort++,
                    'is_active' => true,
                ]
            );

            $currency->translateOrNew('en')->name = $meta['name_en'] ?? $code;
            $currency->translateOrNew('ar')->name = $meta['name_ar'] ?? $code;
            $currency->save();
        }

        CatalogCache::flushCurrencies();
    }
}
