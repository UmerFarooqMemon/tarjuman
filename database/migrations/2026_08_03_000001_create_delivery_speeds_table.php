<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_speeds', function (Blueprint $table) {
            $table->id();
            $table->decimal('price_amount', 12, 4)->default(0);
            // Optional hour bounds for estimation / filtering (e.g. same day max 12).
            $table->unsignedInteger('min_hours')->nullable();
            $table->unsignedInteger('max_hours')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_speeds');
    }
};
