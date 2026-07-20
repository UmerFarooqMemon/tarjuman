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
            $table->string('primary_color', 20)->nullable()->default('#7367F0')->after('favicon');
            $table->string('secondary_color', 20)->nullable()->default('#A8AAAE')->after('primary_color');
            $table->string('primary_button_color', 20)->nullable()->default('#7367F0')->after('secondary_color');
            $table->string('secondary_button_color', 20)->nullable()->default('#A8AAAE')->after('primary_button_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'primary_color',
                'secondary_color',
                'primary_button_color',
                'secondary_button_color',
            ]);
        });
    }
};
