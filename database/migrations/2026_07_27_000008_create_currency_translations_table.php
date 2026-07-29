<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('name', 120);
            $table->timestamps();

            $table->unique(['currency_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_translations');
    }
};
