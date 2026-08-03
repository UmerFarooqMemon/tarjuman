<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('primary_color_end', 20)->nullable()->after('primary_color');
            $table->unsignedSmallInteger('primary_color_angle')->nullable()->default(135)->after('primary_color_end');

            $table->string('secondary_color_end', 20)->nullable()->after('secondary_color');
            $table->unsignedSmallInteger('secondary_color_angle')->nullable()->default(135)->after('secondary_color_end');

            $table->string('primary_button_color_end', 20)->nullable()->after('primary_button_color');
            $table->unsignedSmallInteger('primary_button_color_angle')->nullable()->default(135)->after('primary_button_color_end');

            $table->string('secondary_button_color_end', 20)->nullable()->after('secondary_button_color');
            $table->unsignedSmallInteger('secondary_button_color_angle')->nullable()->default(135)->after('secondary_button_color_end');
        });

        // Seed sensible gradient ends from existing solid colors when empty.
        DB::table('site_settings')->where('id', 1)->update([
            'primary_color_end' => DB::raw("COALESCE(primary_color_end, '#000000')"),
            'secondary_color_end' => DB::raw("COALESCE(secondary_color_end, '#FFFFFF')"),
            'primary_button_color_end' => DB::raw("COALESCE(primary_button_color_end, '#000000')"),
            'secondary_button_color_end' => DB::raw("COALESCE(secondary_button_color_end, '#FFFFFF')"),
            'primary_color_angle' => DB::raw('COALESCE(primary_color_angle, 135)'),
            'secondary_color_angle' => DB::raw('COALESCE(secondary_color_angle, 135)'),
            'primary_button_color_angle' => DB::raw('COALESCE(primary_button_color_angle, 135)'),
            'secondary_button_color_angle' => DB::raw('COALESCE(secondary_button_color_angle, 135)'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'primary_color_end',
                'primary_color_angle',
                'secondary_color_end',
                'secondary_color_angle',
                'primary_button_color_end',
                'primary_button_color_angle',
                'secondary_button_color_end',
                'secondary_button_color_angle',
            ]);
        });
    }
};
