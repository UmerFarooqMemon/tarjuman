<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_type_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_type_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('name', 120);
            $table->timestamps();

            $table->unique(['document_type_id', 'locale']);
            $table->unique(['locale', 'name'], 'document_type_translations_locale_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_type_translations');
    }
};
