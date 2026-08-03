<?php

namespace Database\Seeders;

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

        [$logo, $favicon] = $this->copyDefaultBrandingAssets();

        DB::table('site_settings')->insert([
            'site_title' => 'Tarjuman',
            'contact_email' => 'support@admin.com',
            'contact_phone' => '',
            'address' => 'Dubai, UAE',
            'currency' => 'AED',
            'logo' => $logo,
            'favicon' => $favicon,
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
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Copy default branding images into uploads/front (same path as SiteSettingsController@update).
     *
     * @return array{0: string, 1: string} [logo filename, favicon filename]
     */
    protected function copyDefaultBrandingAssets(): array
    {
        $destinationRelative = uploadsDir('front');
        $destinationAbsolute = public_path($destinationRelative);

        $stamp = time();
        $logoFilename = 'logo-'.$stamp.'.svg';
        $faviconFilename = 'favicon-'.$stamp.'.svg';

        $logoSource = public_path('assets/img/branding/default-logo.svg');
        $faviconSource = public_path('assets/img/branding/default-favicon.svg');

        if (! File::exists($logoSource) || ! File::exists($faviconSource)) {
            throw new \RuntimeException(
                'Default branding assets are missing from public/assets/img/branding (default-logo.svg, default-favicon.svg).'
            );
        }

        File::copy($logoSource, $destinationAbsolute.$logoFilename);
        File::copy($faviconSource, $destinationAbsolute.$faviconFilename);

        return [$logoFilename, $faviconFilename];
    }
}
