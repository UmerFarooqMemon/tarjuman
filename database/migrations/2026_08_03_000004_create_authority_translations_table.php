<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authority_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('authority_id')->constrained('authorities')->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('name', 160);
            $table->timestamps();

            $table->unique(['authority_id', 'locale']);
            $table->unique(['locale', 'name'], 'authority_translations_locale_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authority_translations');
    }
};
