<?php

namespace Database\Seeders;

use App\Models\DeliverySpeed;
use App\Support\CatalogCache;
use Illuminate\Database\Seeder;

class DeliverySpeedsSeeder extends Seeder
{
    public function run(): void
    {
        $speeds = [
            [
                'price_amount' => 0,
                'min_hours' => 48,
                'max_hours' => 72,
                'name_en' => 'Standard',
                'name_ar' => 'عادي',
                'duration_en' => '2-3 Days',
                'duration_ar' => '٢-٣ أيام',
            ],
            [
                'price_amount' => 50,
                'min_hours' => null,
                'max_hours' => 24,
                'name_en' => 'Express',
                'name_ar' => 'سريع',
                'duration_en' => '24 Hours',
                'duration_ar' => '٢٤ ساعة',
            ],
            [
                'price_amount' => 100,
                'min_hours' => null,
                'max_hours' => 12,
                'name_en' => 'Same Day',
                'name_ar' => 'نفس اليوم',
                'duration_en' => '< 12 Hours',
                'duration_ar' => 'أقل من ١٢ ساعة',
            ],
        ];

        foreach ($speeds as $sort => $data) {
            $speed = DeliverySpeed::whereTranslation('name', $data['name_en'], 'en')->first()
                ?? DeliverySpeed::query()->where('sort_order', $sort)->first()
                ?? new DeliverySpeed;

            $speed->fill([
                'price_amount' => $data['price_amount'],
                'min_hours' => $data['min_hours'],
                'max_hours' => $data['max_hours'],
                'sort_order' => $sort,
                'is_active' => true,
            ]);
            $speed->save();

            syncModelTranslations($speed, [
                'en' => [
                    'name' => $data['name_en'],
                    'duration_label' => $data['duration_en'],
                ],
                'ar' => [
                    'name' => $data['name_ar'],
                    'duration_label' => $data['duration_ar'],
                ],
            ]);
        }

        CatalogCache::flushDeliverySpeeds();
    }
}
