<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('type', 20)->default('individual')->after('id');
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone', 32)->nullable()->unique()->after('email');
            $table->string('profile_image')->nullable()->after('phone');
            $table->string('company_name')->nullable()->after('profile_image');
            $table->string('expected_volume', 32)->nullable()->after('company_name');
            $table->boolean('is_active')->default(true)->after('expected_volume');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn([
                'type',
                'first_name',
                'last_name',
                'phone',
                'profile_image',
                'company_name',
                'expected_volume',
                'is_active',
            ]);
        });
    }
};
