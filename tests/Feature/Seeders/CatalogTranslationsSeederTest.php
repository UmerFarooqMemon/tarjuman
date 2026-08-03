<?php

namespace Tests\Feature\Seeders;

use App\Models\AddOn;
use App\Models\AddOnTranslation;
use App\Models\Authority;
use App\Models\AuthorityTranslation;
use App\Models\Currency;
use App\Models\CurrencyTranslation;
use App\Models\DeliverySpeed;
use App\Models\DeliverySpeedTranslation;
use App\Models\DocumentType;
use App\Models\DocumentTypeTranslation;
use App\Models\Language;
use App\Models\LanguageTranslation;
use Database\Seeders\AddOnsSeeder;
use Database\Seeders\AuthoritiesSeeder;
use Database\Seeders\CurrenciesTableSeeder;
use Database\Seeders\DeliverySpeedsSeeder;
use Database\Seeders\DocumentTypesSeeder;
use Database\Seeders\LanguagesTableSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CatalogTranslationsSeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function catalog_seeders_persist_translations_even_when_model_events_are_muted(): void
    {
        Model::withoutEvents(function () {
            $this->seed(LanguagesTableSeeder::class);
            $this->seed(CurrenciesTableSeeder::class);
            $this->seed(DocumentTypesSeeder::class);
            $this->seed(AuthoritiesSeeder::class);
            $this->seed(AddOnsSeeder::class);
            $this->seed(DeliverySpeedsSeeder::class);
        });

        $this->assertSame(Language::count() * 2, LanguageTranslation::count());
        $this->assertSame(Currency::count() * 2, CurrencyTranslation::count());
        $this->assertSame(DocumentType::count() * 2, DocumentTypeTranslation::count());
        $this->assertSame(Authority::count() * 2, AuthorityTranslation::count());
        $this->assertSame(AddOn::count() * 2, AddOnTranslation::count());
        $this->assertSame(DeliverySpeed::count() * 2, DeliverySpeedTranslation::count());

        $this->assertGreaterThan(0, DocumentType::count());
        $this->assertGreaterThan(0, Authority::count());
        $this->assertGreaterThan(0, AddOn::count());
        $this->assertGreaterThan(0, DeliverySpeed::count());

        $this->assertNotNull(
            DocumentType::whereTranslation('name', 'Passport', 'en')->first()
        );
        $this->assertNotNull(
            Authority::whereTranslation('name', 'Ministry of Justice (MOJ)', 'en')->first()
        );
        $this->assertNotNull(
            DeliverySpeed::whereTranslation('name', 'Express', 'en')->first()
        );
    }
}
