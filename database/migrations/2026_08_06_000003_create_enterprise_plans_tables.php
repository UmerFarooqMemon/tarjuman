<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price_amount', 12, 2);
            $table->string('currency', 3)->default('AED');
            $table->string('billing_period', 16)->default('monthly');
            $table->string('quota_unit', 16); // page|word
            $table->unsignedInteger('quota_amount');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('enterprise_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->string('status', 32)->default('active'); // active|exhausted|cancelled|past_due
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->string('quota_unit', 16);
            $table->unsignedInteger('quota_total')->default(0);
            $table->unsignedInteger('quota_used')->default(0);
            $table->string('payment_gateway', 32)->nullable();
            $table->string('payment_tran_ref')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('subscription_usage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_subscription_id')->constrained('enterprise_subscriptions')->cascadeOnDelete();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedInteger('amount');
            $table->string('quota_unit', 16);
            $table->string('type', 32)->default('deduct'); // deduct|adjust|reset
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_usage_events');
        Schema::dropIfExists('enterprise_subscriptions');
        Schema::dropIfExists('plans');
    }
};
