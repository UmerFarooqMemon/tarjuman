<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('add_on_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('add_on_id')->constrained('add_ons')->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('name', 120);
            $table->timestamps();

            $table->unique(['add_on_id', 'locale']);
            $table->unique(['locale', 'name'], 'add_on_translations_locale_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('add_on_translations');
    }
};
