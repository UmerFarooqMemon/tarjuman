<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SiteSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Reset the site_settings table
         */
        // if (\App::environment() == 'local') {
        \DB::table('site_settings')->truncate();
        // }

        \DB::table('site_settings')->insert([
            'site_title' => 'Admin',
            'contact_email' => 'support@admin.com',
            'contact_phone' => '',
            'address' => 'Dubai, UAE',
            'currency' => 'AED',
            'logo' => '',
            'facebook' => '',
            'twitter' => '',
            'pinterest' => '',
            'footer_scripts' => '',
            'footer_sentence' => '',
            'copyright' => '',
            'primary_color' => '#7367F0',
            'primary_color_end' => '#9E95F5',
            'primary_color_angle' => 135,
            'secondary_color' => '#A8AAAE',
            'secondary_color_end' => '#D2D2D6',
            'secondary_color_angle' => 135,
            'primary_button_color' => '#7367F0',
            'primary_button_color_end' => '#9E95F5',
            'primary_button_color_angle' => 135,
            'secondary_button_color' => '#A8AAAE',
            'secondary_button_color_end' => '#D2D2D6',
            'secondary_button_color_angle' => 135,
            'primary_button_text_color' => '#FFFFFF',
            'secondary_button_text_color' => '#FFFFFF',
            'primary_button_border_color' => '#7367F0',
            'secondary_button_border_color' => '#A8AAAE',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
