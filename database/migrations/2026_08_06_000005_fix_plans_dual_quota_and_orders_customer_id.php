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
            $table->unsignedInteger('page_quota')->default(0)->after('billing_period');
            $table->unsignedInteger('word_quota')->default(0)->after('page_quota');
        });

        if (Schema::hasColumn('plans', 'quota_unit')) {
            DB::table('plans')->orderBy('id')->chunkById(100, function ($plans): void {
                foreach ($plans as $plan) {
                    $pages = $plan->quota_unit === 'page' ? (int) $plan->quota_amount : 0;
                    $words = $plan->quota_unit === 'word' ? (int) $plan->quota_amount : 0;
                    DB::table('plans')->where('id', $plan->id)->update([
                        'page_quota' => $pages,
                        'word_quota' => $words,
                    ]);
                }
            });

            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn(['quota_unit', 'quota_amount']);
            });
        }

        Schema::table('enterprise_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('pages_total')->default(0)->after('current_period_end');
            $table->unsignedInteger('pages_used')->default(0)->after('pages_total');
            $table->unsignedInteger('words_total')->default(0)->after('pages_used');
            $table->unsignedInteger('words_used')->default(0)->after('words_total');
        });

        if (Schema::hasColumn('enterprise_subscriptions', 'quota_unit')) {
            DB::table('enterprise_subscriptions')->orderBy('id')->chunkById(100, function ($subs): void {
                foreach ($subs as $sub) {
                    $pagesTotal = $sub->quota_unit === 'page' ? (int) $sub->quota_total : 0;
                    $pagesUsed = $sub->quota_unit === 'page' ? (int) $sub->quota_used : 0;
                    $wordsTotal = $sub->quota_unit === 'word' ? (int) $sub->quota_total : 0;
                    $wordsUsed = $sub->quota_unit === 'word' ? (int) $sub->quota_used : 0;
                    DB::table('enterprise_subscriptions')->where('id', $sub->id)->update([
                        'pages_total' => $pagesTotal,
                        'pages_used' => $pagesUsed,
                        'words_total' => $wordsTotal,
                        'words_used' => $wordsUsed,
                    ]);
                }
            });

            Schema::table('enterprise_subscriptions', function (Blueprint $table) {
                $table->dropColumn(['quota_unit', 'quota_total', 'quota_used']);
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_first_name',
                'customer_last_name',
                'customer_email',
                'customer_phone',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('user_id', 'customer_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('customer_id', 'user_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('customer_first_name')->nullable();
            $table->string('customer_last_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 32)->nullable();
        });

        Schema::table('enterprise_subscriptions', function (Blueprint $table) {
            $table->string('quota_unit', 16)->nullable()->after('current_period_end');
            $table->unsignedInteger('quota_total')->default(0)->after('quota_unit');
            $table->unsignedInteger('quota_used')->default(0)->after('quota_total');
        });

        Schema::table('enterprise_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['pages_total', 'pages_used', 'words_total', 'words_used']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->string('quota_unit', 16)->nullable()->after('billing_period');
            $table->unsignedInteger('quota_amount')->default(0)->after('quota_unit');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['page_quota', 'word_quota']);
        });
    }
};
