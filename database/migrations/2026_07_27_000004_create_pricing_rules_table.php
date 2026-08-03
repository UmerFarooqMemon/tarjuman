<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->nullable();
            // Inclusive page range. null min = no lower bound; null max = no upper bound.
            $table->unsignedInteger('min_pages')->nullable();
            $table->unsignedInteger('max_pages')->nullable();
            $table->string('billing_unit', 16); // word | page
            $table->decimal('rate_amount', 12, 4);
            $table->string('currency', 3)->default('AED');
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'priority'], 'pricing_rules_active_priority_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
