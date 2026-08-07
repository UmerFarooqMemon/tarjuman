<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_id', 32)->nullable()->unique()->after('id');
        });

        $orders = DB::table('orders')->orderBy('id')->get(['id']);
        $seq = 1;
        foreach ($orders as $order) {
            DB::table('orders')->where('id', $order->id)->update([
                'order_id' => 'TRJ-'.str_pad((string) $seq, 5, '0', STR_PAD_LEFT),
            ]);
            $seq++;
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE orders MODIFY order_id VARCHAR(32) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE orders ALTER COLUMN order_id SET NOT NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite cannot easily alter nullability; leave nullable after backfill.
        } else {
            DB::statement('ALTER TABLE orders ALTER COLUMN order_id SET NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};
