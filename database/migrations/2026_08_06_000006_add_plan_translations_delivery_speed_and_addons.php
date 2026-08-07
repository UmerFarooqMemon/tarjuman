<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->foreignId('delivery_speed_id')
                ->nullable()
                ->after('billing_period')
                ->constrained('delivery_speeds')
                ->nullOnDelete();
        });

        Schema::create('plan_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('name', 190);
            $table->timestamps();

            $table->unique(['plan_id', 'locale']);
            $table->unique(['locale', 'name'], 'plan_translations_locale_name_unique');
        });

        if (Schema::hasColumn('plans', 'name')) {
            $now = now();
            DB::table('plans')->orderBy('id')->chunkById(100, function ($plans) use ($now): void {
                foreach ($plans as $plan) {
                    $name = filled($plan->name) ? (string) $plan->name : 'Plan '.$plan->id;
                    foreach (['en', 'ar'] as $locale) {
                        DB::table('plan_translations')->updateOrInsert(
                            ['plan_id' => $plan->id, 'locale' => $locale],
                            ['name' => $name, 'created_at' => $now, 'updated_at' => $now]
                        );
                    }
                }
            });

            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }

        Schema::create('plan_add_on', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignId('add_on_id')->constrained('add_ons')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['plan_id', 'add_on_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_add_on');

        Schema::table('plans', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        DB::table('plan_translations')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                if ($row->locale !== 'en') {
                    continue;
                }
                DB::table('plans')->where('id', $row->plan_id)->update(['name' => $row->name]);
            }
        });

        Schema::dropIfExists('plan_translations');

        Schema::table('plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_speed_id');
        });
    }
};
