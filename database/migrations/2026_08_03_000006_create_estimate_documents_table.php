<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimate_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimate_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->string('extension', 16);
            $table->unsignedInteger('pages')->default(0);
            $table->unsignedInteger('words')->default(0);
            $table->string('method', 32)->nullable();
            $table->boolean('used_fallback')->default(false);
            $table->json('warnings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimate_documents');
    }
};
