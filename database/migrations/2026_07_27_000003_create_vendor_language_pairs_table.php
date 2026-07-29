<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_language_pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_language_id')->constrained('languages')->restrictOnDelete();
            $table->foreignId('target_language_id')->constrained('languages')->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['vendor_id', 'source_language_id', 'target_language_id'],
                'vendor_language_pairs_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_language_pairs');
    }
};
