<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use App\Support\CatalogCache;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SiteSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('site_settings')->truncate();

        [
            $logo,
            $logoAr,
            $favicon,
            $footerLogo,
            $footerLogoAr,
            $fontEn,
            $fontAr,
        ] = $this->copyDefaultBrandingAssets();

        DB::table('site_settings')->insert([
            'site_title' => 'Tarjuman',
            'contact_email' => 'support@admin.com',
            'contact_phone' => '',
            'address' => 'Dubai, UAE',
            'currency' => 'AED',
            'order_payment_mode' => 'later',
            'order_assignment_mode' => 'open',
            'order_source_retention_days' => 90,
            'order_delivery_retention_days' => 1095,
            'vendor_document_download_allowed' => false,
            'vendor_payout_schedule' => 'weekly',
            'platform_fee_percent' => 10,
            'platform_fee_fixed' => 0,
            'logo' => $logo,
            'logo_ar' => $logoAr,
            'favicon' => $favicon,
            'footer_logo' => $footerLogo,
            'footer_logo_ar' => $footerLogoAr,
            'font_en' => $fontEn,
            'font_ar' => $fontAr,
            'facebook' => '',
            'twitter' => '',
            'pinterest' => '',
            'footer_scripts' => '',
            'footer_sentence' => '',
            'copyright' => '',
            'primary_color' => '#000000',
            'primary_color_end' => '#000000',
            'primary_color_angle' => 135,
            'secondary_color' => '#FFFFFF',
            'secondary_color_end' => '#FFFFFF',
            'secondary_color_angle' => 135,
            'primary_button_color' => '#227241',
            'primary_button_color_end' => '#227241',
            'primary_button_color_angle' => 135,
            'secondary_button_color' => '#CCCCCC',
            'secondary_button_color_end' => '#CCCCCC',
            'secondary_button_color_angle' => 135,
            'primary_button_text_color' => '#FFFFFF',
            'secondary_button_text_color' => '#666666',
            'primary_button_border_color' => '#227241',
            'secondary_button_border_color' => '#CCCCCC',
            'footer_bg_color' => '#000000',
            'footer_heading_color' => '#FFFFFF',
            'footer_link_color' => '#FFFFFF',
            'footer_link_hover_color' => '#CCCCCC',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        CatalogCache::flushSiteSettings();

        // Always re-seed trust badge galleries with site settings.
        $this->call(SiteSettingsGalleriesSeeder::class);
    }

    /**
     * Copy default branding images/fonts into uploads/front (same path as SiteSettingsController@update).
     *
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string, 6: string}
     */
    protected function copyDefaultBrandingAssets(): array
    {
        $destinationRelative = uploadsDir('front');
        $destinationAbsolute = public_path($destinationRelative);
        File::ensureDirectoryExists($destinationAbsolute);

        $stamp = time();
        $assets = [
            ['source' => 'assets/img/branding/default-logo.svg', 'filename' => 'logo-'.$stamp.'.svg'],
            ['source' => 'assets/img/branding/default-ar-logo.svg', 'filename' => 'logo-ar-'.$stamp.'.svg'],
            ['source' => 'assets/img/branding/default-favicon.svg', 'filename' => 'favicon-'.$stamp.'.svg'],
            ['source' => 'assets/img/branding/default-footer-en-logo.svg', 'filename' => 'footer-logo-'.$stamp.'.svg'],
            ['source' => 'assets/img/branding/default-footer-ar-logo.svg', 'filename' => 'footer-logo-ar-'.$stamp.'.svg'],
            ['source' => 'assets/fonts/SpaceGrotesk-Regular.ttf', 'filename' => 'font-en-'.$stamp.'.ttf'],
            ['source' => 'assets/fonts/IBMPlexSansArabic-Regular.ttf', 'filename' => 'font-ar-'.$stamp.'.ttf'],
        ];

        $copied = [];

        foreach ($assets as $asset) {
            $sourceAbsolute = public_path($asset['source']);
            if (! File::exists($sourceAbsolute)) {
                throw new \RuntimeException(
                    'Default branding asset is missing: public/'.$asset['source']
                );
            }

            File::copy($sourceAbsolute, $destinationAbsolute.$asset['filename']);
            $copied[] = $asset['filename'];
        }

        return $copied;
    }

    /**
     * Fill empty footer logo columns on an existing settings row (safe for re-runs).
     */
    public static function seedFooterLogosIfMissing(?SiteSetting $settings = null): void
    {
        $settings ??= SiteSetting::query()->find(1);
        if (! $settings) {
            return;
        }

        $destinationRelative = uploadsDir('front');
        $destinationAbsolute = public_path($destinationRelative);
        File::ensureDirectoryExists($destinationAbsolute);
        $stamp = time();
        $payload = [];

        if (empty($settings->footer_logo)) {
            $source = public_path('assets/img/branding/default-footer-en-logo.svg');
            if (File::exists($source)) {
                $filename = 'footer-logo-'.$stamp.'.svg';
                File::copy($source, $destinationAbsolute.$filename);
                $payload['footer_logo'] = $filename;
            }
        }

        if (empty($settings->footer_logo_ar)) {
            $source = public_path('assets/img/branding/default-footer-ar-logo.svg');
            if (File::exists($source)) {
                $filename = 'footer-logo-ar-'.$stamp.'.svg';
                File::copy($source, $destinationAbsolute.$filename);
                $payload['footer_logo_ar'] = $filename;
            }
        }

        if ($payload !== []) {
            $settings->update($payload);
            CatalogCache::flushSiteSettings();
        }
    }

    /**
     * Fill empty font columns on an existing settings row (safe for re-runs).
     */
    public static function seedFontsIfMissing(?SiteSetting $settings = null): void
    {
        $settings ??= SiteSetting::query()->find(1);
        if (! $settings) {
            return;
        }

        $destinationRelative = uploadsDir('front');
        $destinationAbsolute = public_path($destinationRelative);
        File::ensureDirectoryExists($destinationAbsolute);
        $stamp = time();
        $payload = [];

        if (empty($settings->font_en)) {
            $source = public_path('assets/fonts/SpaceGrotesk-Regular.ttf');
            if (File::exists($source)) {
                $filename = 'font-en-'.$stamp.'.ttf';
                File::copy($source, $destinationAbsolute.$filename);
                $payload['font_en'] = $filename;
            }
        }

        if (empty($settings->font_ar)) {
            $source = public_path('assets/fonts/IBMPlexSansArabic-Regular.ttf');
            if (File::exists($source)) {
                $filename = 'font-ar-'.$stamp.'.ttf';
                File::copy($source, $destinationAbsolute.$filename);
                $payload['font_ar'] = $filename;
            }
        }

        if ($payload !== []) {
            $settings->update($payload);
            CatalogCache::flushSiteSettings();
        }
    }
}
