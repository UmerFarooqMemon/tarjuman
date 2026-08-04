<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('footer_bg_color', 20)->nullable()->default('#0F172A')->after('footer_logo_ar');
            $table->string('footer_heading_color', 20)->nullable()->default('#FFFFFF')->after('footer_bg_color');
            $table->string('footer_link_color', 20)->nullable()->default('#CBD5E1')->after('footer_heading_color');
            $table->string('footer_link_hover_color', 20)->nullable()->default('#FFFFFF')->after('footer_link_color');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'footer_bg_color',
                'footer_heading_color',
                'footer_link_color',
                'footer_link_hover_color',
            ]);
        });
    }
};
