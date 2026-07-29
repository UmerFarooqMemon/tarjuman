<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('language_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('name', 120);
            $table->timestamps();

            $table->unique(['language_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('language_translations');
    }
};
