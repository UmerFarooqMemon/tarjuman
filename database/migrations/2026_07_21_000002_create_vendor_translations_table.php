<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('locale', 8)->index();
            $table->string('legal_name', 191);
            $table->string('business_name', 191)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            $table->unique(['vendor_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_translations');
    }
};
