<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->decimal('platform_fee_percent', 5, 2)
                ->default(10)
                ->after('vendor_payout_schedule');
            $table->decimal('platform_fee_fixed', 12, 2)
                ->default(0)
                ->after('platform_fee_percent');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['platform_fee_percent', 'platform_fee_fixed']);
        });
    }
};
