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
        ] = $this->copyDefaultBrandingAssets();

        DB::table('site_settings')->insert([
            'site_title' => 'Tarjuman',
            'contact_email' => 'support@admin.com',
            'contact_phone' => '',
            'address' => 'Dubai, UAE',
            'currency' => 'AED',
            'logo' => $logo,
            'logo_ar' => $logoAr,
            'favicon' => $favicon,
            'footer_logo' => $footerLogo,
            'footer_logo_ar' => $footerLogoAr,
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
            'secondary_button_color' => '#FFFFFF',
            'secondary_button_color_end' => '#FFFFFF',
            'secondary_button_color_angle' => 135,
            'primary_button_text_color' => '#FFFFFF',
            'secondary_button_text_color' => '#000000',
            'primary_button_border_color' => '#227241',
            'secondary_button_border_color' => '#000000',
            'footer_bg_color' => '#000000',
            'footer_heading_color' => '#fff',
            'footer_link_color' => '#fff',
            'footer_link_hover_color' => '#ffffff69',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        CatalogCache::flushSiteSettings();
    }

    /**
     * Copy default branding images into uploads/front (same path as SiteSettingsController@update).
     *
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string}
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
}
