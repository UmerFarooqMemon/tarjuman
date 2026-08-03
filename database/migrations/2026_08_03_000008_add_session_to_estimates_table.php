<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->uuid('session_uuid')->nullable()->after('uuid')->index();
            $table->foreignId('previous_estimate_id')
                ->nullable()
                ->after('session_uuid')
                ->constrained('estimates')
                ->nullOnDelete();
        });

        // Existing rows are each their own session.
        DB::table('estimates')
            ->whereNull('session_uuid')
            ->update(['session_uuid' => DB::raw('uuid')]);
    }

    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('previous_estimate_id');
            $table->dropColumn('session_uuid');
        });
    }
};
