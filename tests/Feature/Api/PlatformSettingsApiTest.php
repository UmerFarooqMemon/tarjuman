<?php

namespace Tests\Feature\Api;

use App\Models\SiteSetting;
use App\Support\CatalogCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlatformSettingsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['api.token' => 'test-api-token']);
    }

    #[Test]
    public function platform_settings_require_api_token(): void
    {
        $this->getJson('/api/platform-settings')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    #[Test]
    public function it_returns_platform_settings_payload(): void
    {
        $dir = public_path(uploadsDir('front'));
        File::ensureDirectoryExists($dir);

        $logoEn = 'logo-en-test.svg';
        $logoAr = 'logo-ar-test.svg';
        $favicon = 'favicon-test.svg';
        $accepted = 'accepted-by-test.png';
        $certified = 'certified-by-test.png';
        $regulated = 'regulated-by-test.webp';
        File::put($dir.$logoEn, '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        File::put($dir.$logoAr, '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        File::put($dir.$favicon, '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        File::put($dir.$accepted, 'png');
        File::put($dir.$certified, 'png');
        File::put($dir.$regulated, 'webp');

        SiteSetting::query()->create([
            'id' => 1,
            'site_title' => 'Tarjuman',
            'contact_email' => 'hello@tarjuman.test',
            'contact_phone' => '+971501234567',
            'address' => 'Dubai, UAE',
            'address_ar' => 'دبي، الإمارات',
            'currency' => 'AED',
            'logo' => $logoEn,
            'logo_ar' => $logoAr,
            'favicon' => $favicon,
            'instagram' => 'https://instagram.com/tarjuman',
            'facebook' => 'https://facebook.com/tarjuman',
            'tiktok' => 'https://tiktok.com/@tarjuman',
            'whatsapp' => 'https://wa.me/971501234567',
            'copyright' => '© Tarjuman',
            'accepted_by_images' => [$accepted],
            'certified_by_images' => [$certified],
            'regulated_by_images' => [$regulated],
            'primary_color' => '#111111',
            'primary_color_end' => '#222222',
            'primary_color_angle' => 90,
            'secondary_color' => '#FFFFFF',
            'primary_button_color' => '#227241',
            'secondary_button_color' => '#FFFFFF',
            'primary_button_text_color' => '#FFFFFF',
            'secondary_button_text_color' => '#000000',
            'primary_button_border_color' => '#227241',
            'secondary_button_border_color' => '#000000',
        ]);

        CatalogCache::flushSiteSettings();

        $response = $this->withHeader('X-API-Token', 'test-api-token')
            ->getJson('/api/platform-settings?locale=ar');

        $response->assertOk()
            ->assertJsonPath('locale', 'ar')
            ->assertJsonPath('currency.code', platformCurrency())
            ->assertJsonPath('data.contact_email', 'hello@tarjuman.test')
            ->assertJsonPath('data.contact_phone', '+971501234567')
            ->assertJsonPath('data.address.en', 'Dubai, UAE')
            ->assertJsonPath('data.address.ar', 'دبي، الإمارات')
            ->assertJsonPath('data.copyright', '© Tarjuman')
            ->assertJsonPath('data.social.instagram', 'https://instagram.com/tarjuman')
            ->assertJsonPath('data.social.facebook', 'https://facebook.com/tarjuman')
            ->assertJsonPath('data.social.tiktok', 'https://tiktok.com/@tarjuman')
            ->assertJsonPath('data.social.whatsapp', 'https://wa.me/971501234567')
            ->assertJsonPath('data.branding.primary.start', '#111111')
            ->assertJsonPath('data.branding.primary.end', '#222222')
            ->assertJsonPath('data.branding.primary.angle', 90)
            ->assertJsonPath('data.branding.primary.css', 'linear-gradient(90deg, #111111 0%, #222222 100%)')
            ->assertJsonPath('data.branding.primary_button_text', '#FFFFFF')
            ->assertJsonCount(1, 'data.accepted_by')
            ->assertJsonCount(1, 'data.certified_by')
            ->assertJsonCount(1, 'data.regulated_by');

        $this->assertStringContainsString($logoEn, (string) $response->json('data.logos.en'));
        $this->assertStringContainsString($logoAr, (string) $response->json('data.logos.ar'));
        $this->assertStringContainsString($favicon, (string) $response->json('data.logos.favicon'));
        $this->assertStringContainsString($accepted, (string) $response->json('data.accepted_by.0'));
        $this->assertStringContainsString($certified, (string) $response->json('data.certified_by.0'));
        $this->assertStringContainsString($regulated, (string) $response->json('data.regulated_by.0'));

        File::delete([$dir.$logoEn, $dir.$logoAr, $dir.$favicon, $dir.$accepted, $dir.$certified, $dir.$regulated]);
    }
}
