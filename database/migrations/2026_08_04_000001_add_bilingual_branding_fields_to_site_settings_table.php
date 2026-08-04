<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('logo_ar', 65)->nullable()->after('logo');
            $table->string('legal_business_name', 190)->nullable()->after('site_title');
            $table->string('trade_license_number', 64)->nullable()->after('legal_business_name');
            $table->string('address_ar', 190)->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'logo_ar',
                'legal_business_name',
                'trade_license_number',
                'address_ar',
            ]);
        });
    }
};
