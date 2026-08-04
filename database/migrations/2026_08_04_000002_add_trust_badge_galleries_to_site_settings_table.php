<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->json('accepted_by_images')->nullable()->after('copyright');
            $table->json('certified_by_images')->nullable()->after('accepted_by_images');
            $table->json('regulated_by_images')->nullable()->after('certified_by_images');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'accepted_by_images',
                'certified_by_images',
                'regulated_by_images',
            ]);
        });
    }
};
