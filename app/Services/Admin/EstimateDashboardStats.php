<?php

namespace App\Services\Admin;

use App\Models\Estimate;
use App\Models\EstimateAddOn;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EstimateDashboardStats
{
    /**
     * @return array<string, mixed>
     */
    public function build(?Carbon $now = null): array
    {
        $now = ($now ?? now())->copy();
        $todayStart = $now->copy()->startOfDay();
        $weekStart = $now->copy()->startOfWeek();
        $chartStart = $now->copy()->subDays(29)->startOfDay();

        $current = Estimate::query()->current();

        $quotesToday = (clone $current)->where('created_at', '>=', $todayStart)->count();
        $quotesThisWeek = (clone $current)->where('created_at', '>=', $weekStart)->count();
        $currentTotal = (clone $current)->count();
        $convertedTotal = Estimate::query()->converted()->count();
        $quotedTotal = Estimate::query()->quoted()->count();

        $pipelineValue = (float) Estimate::query()->quoted()->sum('total_amount');
        $avgQuoteValue = (float) ((clone $current)->avg('total_amount') ?? 0);
        $conversionRate = $currentTotal > 0
            ? round(($convertedTotal / $currentTotal) * 100, 1)
            : 0.0;

        $estimatesWithAddOns = Estimate::query()
            ->current()
            ->whereHas('addOns')
            ->count();

        $addOnAttachRate = $currentTotal > 0
            ? round(($estimatesWithAddOns / $currentTotal) * 100, 1)
            : 0.0;

        return [
            'kpis' => [
                'quotes_today' => $quotesToday,
                'quotes_this_week' => $quotesThisWeek,
                'conversion_rate' => $conversionRate,
                'pipeline_value' => $pipelineValue,
                'avg_quote_value' => round($avgQuoteValue, 4),
                'current_total' => $currentTotal,
                'quoted_total' => $quotedTotal,
                'converted_total' => $convertedTotal,
                'add_on_attach_rate' => $addOnAttachRate,
            ],
            'quotes_over_time' => $this->quotesOverTime($chartStart, $now),
            'language_pairs' => $this->languagePairs(),
            'document_types' => $this->groupedCounts('document_type_name', 8),
            'pricing_rules' => $this->groupedCounts('pricing_rule_name', 8),
            'delivery_speeds' => $this->deliverySpeedMix(),
            'top_add_ons' => $this->topAddOns(8),
            'recent_quotes' => $this->recentQuotes(10),
            'currency' => platformCurrency(),
        ];
    }

    /**
     * @return array{labels: list<string>, series: list<int>}
     */
    protected function quotesOverTime(Carbon $from, Carbon $to): array
    {
        $dayExpr = $this->dateExpression('created_at');

        $rows = Estimate::query()
            ->current()
            ->where('created_at', '>=', $from)
            ->selectRaw("{$dayExpr} as day, COUNT(*) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $series = [];

        foreach (CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()) as $date) {
            $key = $date->toDateString();
            $labels[] = $date->format('d M');
            $series[] = (int) ($rows[$key] ?? 0);
        }

        return compact('labels', 'series');
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    protected function languagePairs(int $limit = 8): array
    {
        return Estimate::query()
            ->current()
            ->select([
                'source_language_code',
                'target_language_code',
                DB::raw('COUNT(*) as total'),
            ])
            ->groupBy('source_language_code', 'target_language_code')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'label' => ($row->source_language_code ?: '?').' → '.($row->target_language_code ?: '?'),
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    protected function groupedCounts(string $column, int $limit = 8): array
    {
        $allowed = ['document_type_name', 'pricing_rule_name'];
        if (! in_array($column, $allowed, true)) {
            return [];
        }

        $unknown = __('general.unknown');

        return Estimate::query()
            ->current()
            ->select([
                DB::raw("COALESCE({$column}, ".$this->quote($unknown).') as label'),
                DB::raw('COUNT(*) as total'),
            ])
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'label' => (string) $row->label,
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    protected function deliverySpeedMix(): array
    {
        $none = __('general.no_delivery_speed');

        return Estimate::query()
            ->current()
            ->select([
                DB::raw('COALESCE(delivery_speed_name, '.$this->quote($none).') as label'),
                DB::raw('COUNT(*) as total'),
            ])
            ->groupBy('label')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => (string) $row->label,
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, count: int, amount: float}>
     */
    protected function topAddOns(int $limit = 8): array
    {
        return EstimateAddOn::query()
            ->select([
                'estimate_add_ons.name as label',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(estimate_add_ons.amount) as amount_total'),
            ])
            ->join('estimates', 'estimates.id', '=', 'estimate_add_ons.estimate_id')
            ->whereIn('estimates.status', [Estimate::STATUS_QUOTED, Estimate::STATUS_CONVERTED])
            ->groupBy('estimate_add_ons.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'label' => (string) $row->label,
                'count' => (int) $row->total,
                'amount' => (float) $row->amount_total,
            ])
            ->all();
    }

    /**
     * @return Collection<int, Estimate>
     */
    protected function recentQuotes(int $limit = 10): Collection
    {
        return Estimate::query()
            ->current()
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    protected function dateExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m-%d', {$column})"
            : "DATE({$column})";
    }

    protected function quote(string $value): string
    {
        return DB::getPdo()->quote($value);
    }
}
