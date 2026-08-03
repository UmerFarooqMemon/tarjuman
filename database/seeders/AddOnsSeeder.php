<?php

namespace Database\Seeders;

use App\Models\AddOn;
use App\Support\CatalogCache;
use Illuminate\Database\Seeder;

class AddOnsSeeder extends Seeder
{
    public function run(): void
    {
        $addOns = [
            [
                'pricing_mode' => AddOn::PRICING_MODE_FIXED,
                'default_amount' => 50,
                'name_en' => 'Notarization',
                'name_ar' => 'توثيق كاتب العدل',
            ],
            [
                'pricing_mode' => AddOn::PRICING_MODE_FIXED,
                'default_amount' => 30,
                'name_en' => 'Hard Copy Delivery',
                'name_ar' => 'توصيل النسخة الورقية',
            ],
            [
                'pricing_mode' => AddOn::PRICING_MODE_FIXED,
                'default_amount' => 40,
                'name_en' => 'Certified Stamp',
                'name_ar' => 'ختم معتمد',
            ],
            [
                'pricing_mode' => AddOn::PRICING_MODE_FIXED,
                'default_amount' => 100,
                'name_en' => 'Attestation',
                'name_ar' => 'تصديق',
            ],
        ];

        foreach ($addOns as $sort => $data) {
            $addOn = AddOn::whereTranslation('name', $data['name_en'], 'en')->first()
                ?? AddOn::query()->where('sort_order', $sort)->first()
                ?? new AddOn;

            $addOn->fill([
                'pricing_mode' => $data['pricing_mode'],
                'default_amount' => $data['default_amount'],
                'sort_order' => $sort,
                'is_active' => true,
            ]);
            $addOn->save();

            syncModelTranslations($addOn, [
                'en' => ['name' => $data['name_en']],
                'ar' => ['name' => $data['name_ar']],
            ]);
        }

        CatalogCache::flushAddOns();

        // Remove previous default add-ons that are no longer part of the catalog seed.
        $retiredNames = [
            'MOE Attestation',
            'MOFA Attestation',
            'Apostille',
            'Urgent Processing',
        ];

        AddOn::whereHas('translations', function ($query) use ($retiredNames) {
            $query->where('locale', 'en')->whereIn('name', $retiredNames);
        })->each(fn (AddOn $addOn) => $addOn->delete());

        CatalogCache::flushAddOns();
    }
}
