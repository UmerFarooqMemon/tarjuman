<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_speed_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_speed_id')->constrained('delivery_speeds')->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('name', 120);
            $table->string('duration_label', 120);
            $table->timestamps();

            $table->unique(['delivery_speed_id', 'locale']);
            $table->unique(['locale', 'name'], 'delivery_speed_translations_locale_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_speed_translations');
    }
};
