<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('trn', 32)->unique();
            $table->string('trade_license_no', 64);
            $table->date('trade_license_expiry')->nullable();
            $table->string('moj_registration_no', 64);
            $table->string('email', 128);
            $table->string('phone', 24)->nullable();
            $table->string('logo', 65)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_approved')->default(true);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
