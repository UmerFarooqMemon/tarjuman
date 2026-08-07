<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Note: do not use WithoutModelEvents here — Astrotomic Translatable
     * persists translation rows on the parent model's "saved" event.
     */
    public function run(): void
    {
        $this->call(AdminsTableSeeder::class);
        $this->call(SiteSettingsTableSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(LanguagesTableSeeder::class);
        $this->call(CurrenciesTableSeeder::class);
        $this->call(DocumentTypesSeeder::class);
        $this->call(AuthoritiesSeeder::class);
        $this->call(AddOnsSeeder::class);
        $this->call(DeliverySpeedsSeeder::class);
        $this->call(PricingRulesSeeder::class);
        $this->call(PlansSeeder::class);
        $this->call(CmsHomePageSeeder::class);
    }
}
