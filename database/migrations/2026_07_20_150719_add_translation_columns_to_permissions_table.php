<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('module')->nullable()->after('name');
            $table->string('module_en')->nullable()->after('module');
            $table->string('module_ar')->nullable()->after('module_en');
            $table->string('name_en')->nullable()->after('module_ar');
            $table->string('name_ar')->nullable()->after('name_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn([
                'module',
                'module_en',
                'module_ar',
                'name_en',
                'name_ar',
            ]);
        });
    }
};
