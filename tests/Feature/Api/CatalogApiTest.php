<?php

namespace Tests\Feature\Api;

use App\Models\AddOn;
use App\Models\Authority;
use App\Models\DeliverySpeed;
use App\Models\DocumentType;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['api.token' => 'test-api-token']);
    }

    #[Test]
    public function catalog_endpoints_require_api_token(): void
    {
        foreach (['/api/languages', '/api/document-types', '/api/authorities', '/api/add-ons', '/api/delivery-speeds'] as $uri) {
            $this->getJson($uri)
                ->assertUnauthorized()
                ->assertJsonPath('message', 'Unauthenticated.');
        }
    }

    #[Test]
    public function it_lists_active_languages(): void
    {
        $active = $this->createLanguage('en', 'English', 'الإنجليزية', 'ltr', true);
        $this->createLanguage('fr', 'French', 'الفرنسية', 'ltr', false);

        $response = $this->withHeader('X-API-Token', 'test-api-token')
            ->getJson('/api/languages?locale=ar');

        $response->assertOk()
            ->assertJsonPath('locale', 'ar')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.code', 'en')
            ->assertJsonPath('data.0.name', 'الإنجليزية')
            ->assertJsonMissingPath('data.0.translations');
    }

    #[Test]
    public function it_lists_active_document_types(): void
    {
        $active = $this->createDocumentType('Passport', 'جواز سفر', true);
        $this->createDocumentType('Hidden', 'مخفي', false);

        $response = $this->withHeader('Authorization', 'Bearer test-api-token')
            ->getJson('/api/document-types?locale=en');

        $response->assertOk()
            ->assertJsonPath('locale', 'en')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.name', 'Passport')
            ->assertJsonMissingPath('data.0.translations');
    }

    #[Test]
    public function it_lists_active_authorities(): void
    {
        $active = $this->createAuthority('Ministry of Justice (MOJ)', 'وزارة العدل', true);
        $this->createAuthority('Hidden', 'مخفي', false);

        $response = $this->withHeader('X-API-Token', 'test-api-token')
            ->getJson('/api/authorities?locale=ar');

        $response->assertOk()
            ->assertJsonPath('locale', 'ar')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.name', 'وزارة العدل')
            ->assertJsonMissingPath('data.0.translations');
    }

    #[Test]
    public function it_lists_active_add_ons_with_currency(): void
    {
        $active = $this->createAddOn('Notarization', 'توثيق', AddOn::PRICING_MODE_FIXED, 50, true);
        $this->createAddOn('Hidden', 'مخفي', AddOn::PRICING_MODE_PER_PAGE, 10, false);

        $response = $this->withHeader('X-API-Token', 'test-api-token')
            ->getJson('/api/add-ons?locale=ar');

        $response->assertOk()
            ->assertJsonPath('locale', 'ar')
            ->assertJsonPath('currency.code', platformCurrency())
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.name', 'توثيق')
            ->assertJsonPath('data.0.pricing_mode', 'fixed')
            ->assertJsonPath('data.0.amount', '50.0000')
            ->assertJsonMissingPath('data.0.translations');
    }

    #[Test]
    public function it_lists_active_delivery_speeds_with_currency(): void
    {
        $active = $this->createDeliverySpeed('Express', 'سريع', '24 Hours', '٢٤ ساعة', 25, true);
        $this->createDeliverySpeed('Hidden', 'مخفي', '1 Day', 'يوم', 5, false);

        $response = $this->withHeader('X-API-Token', 'test-api-token')
            ->getJson('/api/delivery-speeds');

        $response->assertOk()
            ->assertJsonPath('locale', 'en')
            ->assertJsonPath('currency.code', platformCurrency())
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.name', 'Express')
            ->assertJsonPath('data.0.duration_label', '24 Hours')
            ->assertJsonPath('data.0.price_amount', '25.0000')
            ->assertJsonMissingPath('data.0.translations');
    }

    protected function createLanguage(
        string $code,
        string $nameEn,
        string $nameAr,
        string $direction,
        bool $active,
    ): Language {
        $language = Language::create([
            'code' => $code,
            'native_name' => $nameEn,
            'direction' => $direction,
            'sort_order' => 1,
            'is_active' => $active,
        ]);
        $language->translateOrNew('en')->name = $nameEn;
        $language->translateOrNew('ar')->name = $nameAr;
        $language->save();

        return $language;
    }

    protected function createDocumentType(string $nameEn, string $nameAr, bool $active): DocumentType
    {
        $type = DocumentType::create([
            'sort_order' => 1,
            'is_active' => $active,
        ]);
        $type->translateOrNew('en')->name = $nameEn;
        $type->translateOrNew('ar')->name = $nameAr;
        $type->save();

        return $type;
    }

    protected function createAuthority(string $nameEn, string $nameAr, bool $active): Authority
    {
        $authority = Authority::create([
            'sort_order' => 1,
            'is_active' => $active,
        ]);
        $authority->translateOrNew('en')->name = $nameEn;
        $authority->translateOrNew('ar')->name = $nameAr;
        $authority->save();

        return $authority;
    }

    protected function createAddOn(
        string $nameEn,
        string $nameAr,
        string $mode,
        float $amount,
        bool $active,
    ): AddOn {
        $addOn = AddOn::create([
            'pricing_mode' => $mode,
            'default_amount' => $amount,
            'sort_order' => 1,
            'is_active' => $active,
        ]);
        $addOn->translateOrNew('en')->name = $nameEn;
        $addOn->translateOrNew('ar')->name = $nameAr;
        $addOn->save();

        return $addOn;
    }

    protected function createDeliverySpeed(
        string $nameEn,
        string $nameAr,
        string $durationEn,
        string $durationAr,
        float $price,
        bool $active,
    ): DeliverySpeed {
        $speed = DeliverySpeed::create([
            'price_amount' => $price,
            'min_hours' => null,
            'max_hours' => 24,
            'sort_order' => 1,
            'is_active' => $active,
        ]);
        $speed->translateOrNew('en')->fill([
            'name' => $nameEn,
            'duration_label' => $durationEn,
        ]);
        $speed->translateOrNew('ar')->fill([
            'name' => $nameAr,
            'duration_label' => $durationAr,
        ]);
        $speed->save();

        return $speed;
    }
}
