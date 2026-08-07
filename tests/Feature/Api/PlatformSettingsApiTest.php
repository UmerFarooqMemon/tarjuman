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
        $footerEn = 'footer-logo-en-test.svg';
        $footerAr = 'footer-logo-ar-test.svg';
        $fontEn = 'font-en-test.woff2';
        $fontAr = 'font-ar-test.woff2';
        $accepted = 'accepted-by-test.png';
        $certified = 'certified-by-test.png';
        $regulated = 'regulated-by-test.webp';
        File::put($dir.$logoEn, '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        File::put($dir.$logoAr, '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        File::put($dir.$favicon, '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        File::put($dir.$footerEn, '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        File::put($dir.$footerAr, '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        File::put($dir.$fontEn, 'woff2');
        File::put($dir.$fontAr, 'woff2');
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
            'footer_logo' => $footerEn,
            'footer_logo_ar' => $footerAr,
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
            'footer_bg_color' => '#0F172A',
            'footer_heading_color' => '#FFFFFF',
            'footer_link_color' => '#CBD5E1',
            'footer_link_hover_color' => '#F8FAFC',
            'font_en' => $fontEn,
            'font_ar' => $fontAr,
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
            ->assertJsonPath('data.branding.footer_bg', '#0F172A')
            ->assertJsonPath('data.branding.footer_heading', '#FFFFFF')
            ->assertJsonPath('data.branding.footer_link', '#CBD5E1')
            ->assertJsonPath('data.branding.footer_link_hover', '#F8FAFC')
            ->assertJsonPath('data.branding.fonts.en.family', 'Tarjuman EN')
            ->assertJsonPath('data.branding.fonts.ar.family', 'Tarjuman AR')
            ->assertJsonPath('data.branding.fonts.en.format', 'woff2')
            ->assertJsonPath('data.branding.fonts.ar.format', 'woff2')
            ->assertJsonCount(1, 'data.accepted_by')
            ->assertJsonCount(1, 'data.certified_by')
            ->assertJsonCount(1, 'data.regulated_by')
            ->assertJsonPath('data.orders.payment_mode', 'later')
            ->assertJsonPath('data.orders.assignment_mode', 'open')
            ->assertJsonPath('data.orders.source_retention_days', 90)
            ->assertJsonPath('data.orders.delivery_retention_days', 1095)
            ->assertJsonPath('data.orders.vendor_payout_schedule', 'weekly')
            ->assertJsonPath('data.orders.payment_gateway.default', null);

        $this->assertStringContainsString($logoEn, (string) $response->json('data.logos.en'));
        $this->assertStringContainsString($logoAr, (string) $response->json('data.logos.ar'));
        $this->assertStringContainsString($favicon, (string) $response->json('data.logos.favicon'));
        $this->assertStringContainsString($footerEn, (string) $response->json('data.logos.footer_en'));
        $this->assertStringContainsString($footerAr, (string) $response->json('data.logos.footer_ar'));
        $this->assertStringContainsString($fontEn, (string) $response->json('data.branding.fonts.en.url'));
        $this->assertStringContainsString($fontAr, (string) $response->json('data.branding.fonts.ar.url'));
        $this->assertStringContainsString($accepted, (string) $response->json('data.accepted_by.0'));
        $this->assertStringContainsString($certified, (string) $response->json('data.certified_by.0'));
        $this->assertStringContainsString($regulated, (string) $response->json('data.regulated_by.0'));

        File::delete([
            $dir.$logoEn,
            $dir.$logoAr,
            $dir.$favicon,
            $dir.$footerEn,
            $dir.$footerAr,
            $dir.$fontEn,
            $dir.$fontAr,
            $dir.$accepted,
            $dir.$certified,
            $dir.$regulated,
        ]);
    }
}
