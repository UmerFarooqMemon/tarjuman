<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('order_payment_mode', 16)->default('later')->after('currency');
            $table->string('order_assignment_mode', 16)->default('open')->after('order_payment_mode');
            $table->unsignedInteger('order_source_retention_days')->default(90)->after('order_assignment_mode');
            $table->unsignedInteger('order_delivery_retention_days')->default(1095)->after('order_source_retention_days');
            $table->string('vendor_payout_schedule', 16)->default('weekly')->after('order_delivery_retention_days');

            $table->string('default_payment_gateway', 32)->nullable()->after('vendor_payout_schedule');

            $table->boolean('paytabs_enabled')->default(false)->after('default_payment_gateway');
            $table->string('paytabs_profile_id')->nullable()->after('paytabs_enabled');
            $table->text('paytabs_server_key')->nullable()->after('paytabs_profile_id');
            $table->text('paytabs_client_key')->nullable()->after('paytabs_server_key');
            $table->boolean('paytabs_test_mode')->default(true)->after('paytabs_client_key');

            $table->boolean('tap_enabled')->default(false)->after('paytabs_test_mode');
            $table->text('tap_secret_key')->nullable()->after('tap_enabled');
            $table->text('tap_public_key')->nullable()->after('tap_secret_key');
            $table->boolean('tap_test_mode')->default(true)->after('tap_public_key');

            $table->boolean('noon_enabled')->default(false)->after('tap_test_mode');
            $table->string('noon_business_id')->nullable()->after('noon_enabled');
            $table->text('noon_app_key')->nullable()->after('noon_business_id');
            $table->text('noon_app_secret')->nullable()->after('noon_app_key');
            $table->boolean('noon_test_mode')->default(true)->after('noon_app_secret');

            $table->boolean('amazon_ps_enabled')->default(false)->after('noon_test_mode');
            $table->string('amazon_ps_merchant_identifier')->nullable()->after('amazon_ps_enabled');
            $table->text('amazon_ps_access_code')->nullable()->after('amazon_ps_merchant_identifier');
            $table->text('amazon_ps_sha_request')->nullable()->after('amazon_ps_access_code');
            $table->text('amazon_ps_sha_response')->nullable()->after('amazon_ps_sha_request');
            $table->boolean('amazon_ps_test_mode')->default(true)->after('amazon_ps_sha_response');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'order_payment_mode',
                'order_assignment_mode',
                'order_source_retention_days',
                'order_delivery_retention_days',
                'vendor_payout_schedule',
                'default_payment_gateway',
                'paytabs_enabled',
                'paytabs_profile_id',
                'paytabs_server_key',
                'paytabs_client_key',
                'paytabs_test_mode',
                'tap_enabled',
                'tap_secret_key',
                'tap_public_key',
                'tap_test_mode',
                'noon_enabled',
                'noon_business_id',
                'noon_app_key',
                'noon_app_secret',
                'noon_test_mode',
                'amazon_ps_enabled',
                'amazon_ps_merchant_identifier',
                'amazon_ps_access_code',
                'amazon_ps_sha_request',
                'amazon_ps_sha_response',
                'amazon_ps_test_mode',
            ]);
        });
    }
};
