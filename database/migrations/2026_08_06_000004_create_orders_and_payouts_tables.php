<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('estimate_id')->nullable()->constrained('estimates')->nullOnDelete();
            $table->uuid('session_uuid')->nullable()->index();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('accepted_by_vendor_user_id')->nullable()->constrained('vendor_users')->nullOnDelete();

            $table->string('status', 40)->default('open')->index();
            $table->string('payment_status', 40)->default('unpaid')->index();
            $table->string('payment_method', 20)->nullable(); // gateway|plan
            $table->string('payment_timing_snapshot', 16)->nullable(); // quick|later
            $table->string('assignment_mode_snapshot', 16)->nullable(); // manual|open
            $table->string('payment_gateway_snapshot', 32)->nullable();
            $table->string('payment_tran_ref')->nullable()->index();
            $table->string('payment_checkout_id')->nullable();
            $table->string('payment_link_url')->nullable();

            $table->string('customer_first_name')->nullable();
            $table->string('customer_last_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 32)->nullable();

            $table->unsignedBigInteger('source_language_id')->nullable();
            $table->unsignedBigInteger('target_language_id')->nullable();
            $table->unsignedBigInteger('document_type_id')->nullable();
            $table->unsignedBigInteger('delivery_speed_id')->nullable();

            $table->unsignedInteger('word_count')->nullable();
            $table->unsignedInteger('page_count')->nullable();
            $table->decimal('estimate_amount', 12, 2)->nullable();
            $table->decimal('confirmed_amount', 12, 2)->nullable();
            $table->string('currency', 3)->default('AED');
            $table->text('vendor_note')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20); // source|delivery
            $table->string('disk_path');
            $table->string('original_name');
            $table->string('mime')->nullable();
            $table->string('checksum_sha256', 64)->nullable();
            $table->string('encryption', 32)->default('app_v1');
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamp('retained_until')->nullable()->index();
            $table->timestamp('purged_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('add_on_id')->nullable();
            $table->string('name');
            $table->decimal('amount', 12, 2)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type', 64);
            $table->string('actor_type', 32)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'type']);
        });

        Schema::create('vendor_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('vendor_amount', 12, 2);
            $table->string('currency', 3)->default('AED');
            $table->string('status', 32)->default('pending'); // pending|included_in_payout
            $table->foreignId('vendor_payout_id')->nullable();
            $table->timestamps();

            $table->unique('order_id');
        });

        Schema::create('vendor_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 3)->default('AED');
            $table->string('status', 32)->default('pending'); // pending|paid
            $table->timestamp('paid_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::table('vendor_earnings', function (Blueprint $table) {
            $table->foreign('vendor_payout_id')->references('id')->on('vendor_payouts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vendor_earnings', function (Blueprint $table) {
            $table->dropForeign(['vendor_payout_id']);
        });
        Schema::dropIfExists('vendor_earnings');
        Schema::dropIfExists('vendor_payouts');
        Schema::dropIfExists('order_events');
        Schema::dropIfExists('order_add_ons');
        Schema::dropIfExists('order_documents');
        Schema::dropIfExists('orders');
    }
};
