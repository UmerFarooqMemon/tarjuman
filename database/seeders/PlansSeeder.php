<?php

namespace Database\Seeders;

use App\Models\AddOn;
use App\Models\DeliverySpeed;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $standardSpeedId = DeliverySpeed::whereTranslation('name', 'Standard', 'en')->value('id');
        $expressSpeedId = DeliverySpeed::whereTranslation('name', 'Express', 'en')->value('id');
        $sameDaySpeedId = DeliverySpeed::whereTranslation('name', 'Same Day', 'en')->value('id');

        $notarizationId = AddOn::whereTranslation('name', 'Notarization', 'en')->value('id');
        $hardCopyId = AddOn::whereTranslation('name', 'Hard Copy Delivery', 'en')->value('id');
        $stampId = AddOn::whereTranslation('name', 'Certified Stamp', 'en')->value('id');
        $attestationId = AddOn::whereTranslation('name', 'Attestation', 'en')->value('id');

        $plans = [
            [
                'name_en' => 'Basic',
                'name_ar' => 'أساسي',
                'price_amount' => 200,
                'page_quota' => 50,
                'word_quota' => 500,
                'delivery_speed_id' => $standardSpeedId,
                'add_on_ids' => array_values(array_filter([$stampId])),
            ],
            [
                'name_en' => 'Pro',
                'name_ar' => 'احترافي',
                'price_amount' => 450,
                'page_quota' => 150,
                'word_quota' => 2000,
                'delivery_speed_id' => $expressSpeedId,
                'add_on_ids' => array_values(array_filter([$stampId, $hardCopyId])),
            ],
            [
                'name_en' => 'Pro Plus',
                'name_ar' => 'احترافي بلس',
                'price_amount' => 800,
                'page_quota' => 400,
                'word_quota' => 5000,
                'delivery_speed_id' => $sameDaySpeedId,
                'add_on_ids' => array_values(array_filter([$stampId, $hardCopyId, $notarizationId, $attestationId])),
            ],
        ];

        foreach ($plans as $sort => $data) {
            $plan = Plan::whereTranslation('name', $data['name_en'], 'en')->first()
                ?? Plan::query()->where('sort_order', $sort)->first()
                ?? new Plan;

            $plan->fill([
                'price_amount' => $data['price_amount'],
                'currency' => platformCurrency(),
                'billing_period' => 'monthly',
                'delivery_speed_id' => $data['delivery_speed_id'],
                'page_quota' => $data['page_quota'],
                'word_quota' => $data['word_quota'],
                'sort_order' => $sort,
                'is_active' => true,
            ]);
            $plan->save();

            syncModelTranslations($plan, [
                'en' => ['name' => $data['name_en']],
                'ar' => ['name' => $data['name_ar']],
            ]);

            $plan->addOns()->sync($data['add_on_ids']);
        }
    }
}
