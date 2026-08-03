<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimate_add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('add_on_id')->nullable()->constrained('add_ons')->nullOnDelete();
            $table->string('name');
            $table->string('pricing_mode', 32);
            $table->decimal('unit_amount', 12, 4)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('amount', 14, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimate_add_ons');
    }
};
