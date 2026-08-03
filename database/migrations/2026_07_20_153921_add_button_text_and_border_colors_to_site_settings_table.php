<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('primary_button_text_color', 20)->nullable()->default('#FFFFFF')->after('primary_button_color_angle');
            $table->string('secondary_button_text_color', 20)->nullable()->default('#000000')->after('secondary_button_color_angle');
            $table->string('primary_button_border_color', 20)->nullable()->default('#000000')->after('primary_button_text_color');
            $table->string('secondary_button_border_color', 20)->nullable()->default('#000000')->after('secondary_button_text_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'primary_button_text_color',
                'secondary_button_text_color',
                'primary_button_border_color',
                'secondary_button_border_color',
            ]);
        });
    }
};
