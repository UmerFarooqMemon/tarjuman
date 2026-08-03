<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('status', 32)->default('quoted')->index();

            $table->foreignId('document_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('document_type_name')->nullable();

            $table->foreignId('source_language_id')->nullable()->constrained('languages')->nullOnDelete();
            $table->string('source_language_code', 16)->nullable();
            $table->string('source_language_name')->nullable();

            $table->foreignId('target_language_id')->nullable()->constrained('languages')->nullOnDelete();
            $table->string('target_language_code', 16)->nullable();
            $table->string('target_language_name')->nullable();

            $table->foreignId('pricing_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('pricing_rule_name')->nullable();
            $table->string('billing_unit', 16)->nullable();
            $table->unsignedInteger('billing_quantity')->default(0);
            $table->decimal('unit_rate', 12, 4)->default(0);
            $table->unsignedInteger('page_count')->default(0);
            $table->unsignedInteger('word_count')->default(0);
            $table->decimal('translation_amount', 14, 4)->default(0);

            $table->decimal('add_ons_total', 14, 4)->default(0);

            $table->foreignId('delivery_speed_id')->nullable()->constrained()->nullOnDelete();
            $table->string('delivery_speed_name')->nullable();
            $table->decimal('delivery_speed_amount', 14, 4)->default(0);

            $table->decimal('total_amount', 14, 4)->default(0);
            $table->string('currency', 10);

            // Filled when an order is placed from this estimate (orders table comes later).
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->timestamp('converted_at')->nullable()->index();

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimates');
    }
};
