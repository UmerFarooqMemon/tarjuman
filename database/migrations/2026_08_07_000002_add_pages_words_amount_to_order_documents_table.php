<?php

use App\Models\EstimateDocument;
use App\Models\Order;
use App\Models\OrderDocument;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_documents', function (Blueprint $table) {
            $table->unsignedInteger('pages')->default(0)->after('size');
            $table->unsignedInteger('words')->default(0)->after('pages');
            $table->decimal('amount', 12, 2)->nullable()->after('words');
        });

        Order::query()
            ->whereNotNull('estimate_id')
            ->with(['documents', 'estimate.documents'])
            ->orderBy('id')
            ->chunkById(50, function ($orders): void {
                foreach ($orders as $order) {
                    $estimateDocs = $order->estimate?->documents ?? collect();
                    if ($estimateDocs->isEmpty() || $order->documents->isEmpty()) {
                        continue;
                    }

                    $matched = [];
                    foreach ($order->documents as $document) {
                        $estimateDoc = $estimateDocs->first(
                            fn (EstimateDocument $ed) => strcasecmp($ed->filename, $document->original_name) === 0
                        );

                        if (! $estimateDoc) {
                            continue;
                        }

                        $matched[] = [
                            'document' => $document,
                            'pages' => (int) $estimateDoc->pages,
                            'words' => (int) $estimateDoc->words,
                        ];
                    }

                    if ($matched === []) {
                        continue;
                    }

                    $totalPages = array_sum(array_column($matched, 'pages'));
                    $totalWords = array_sum(array_column($matched, 'words'));
                    $orderAmount = (float) ($order->estimate_amount ?? 0);
                    $useWords = $totalWords > 0;
                    $weightTotal = $useWords ? $totalWords : $totalPages;
                    $allocated = 0.0;
                    $lastIndex = count($matched) - 1;

                    foreach ($matched as $index => $row) {
                        $weight = $useWords ? $row['words'] : $row['pages'];
                        if ($index === $lastIndex) {
                            $amount = round(max(0, $orderAmount - $allocated), 2);
                        } elseif ($weightTotal > 0 && $orderAmount > 0) {
                            $amount = round($orderAmount * ($weight / $weightTotal), 2);
                            $allocated += $amount;
                        } else {
                            $amount = null;
                        }

                        $row['document']->forceFill([
                            'pages' => $row['pages'],
                            'words' => $row['words'],
                            'amount' => $amount,
                        ])->save();
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('order_documents', function (Blueprint $table) {
            $table->dropColumn(['pages', 'words', 'amount']);
        });
    }
};
