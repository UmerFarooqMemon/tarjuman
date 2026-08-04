<?php

namespace App\Services\Admin;

use App\Models\AddOn;
use App\Models\DeliverySpeed;
use App\Models\DocumentType;
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
            'document_types' => $this->documentTypes(8),
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
     * Group by FK and resolve labels in the current admin locale.
     *
     * @return list<array{label: string, count: int}>
     */
    protected function documentTypes(int $limit = 8): array
    {
        $rows = Estimate::query()
            ->current()
            ->select(['document_type_id', DB::raw('COUNT(*) as total')])
            ->groupBy('document_type_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        $types = DocumentType::query()
            ->with('translations')
            ->whereIn('id', $rows->pluck('document_type_id')->filter()->all())
            ->get()
            ->keyBy('id');

        return $rows
            ->map(fn ($row) => [
                'label' => $types->get($row->document_type_id)?->displayName()
                    ?: __('general.unknown'),
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    protected function groupedCounts(string $column, int $limit = 8): array
    {
        $allowed = ['pricing_rule_name'];
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
        $rows = Estimate::query()
            ->current()
            ->select(['delivery_speed_id', DB::raw('COUNT(*) as total')])
            ->groupBy('delivery_speed_id')
            ->orderByDesc('total')
            ->get();

        $speeds = DeliverySpeed::query()
            ->with('translations')
            ->whereIn('id', $rows->pluck('delivery_speed_id')->filter()->all())
            ->get()
            ->keyBy('id');

        return $rows
            ->map(fn ($row) => [
                'label' => $row->delivery_speed_id
                    ? ($speeds->get($row->delivery_speed_id)?->displayName() ?: __('general.unknown'))
                    : __('general.no_delivery_speed'),
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, count: int, amount: float}>
     */
    protected function topAddOns(int $limit = 8): array
    {
        $rows = EstimateAddOn::query()
            ->select([
                'estimate_add_ons.add_on_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(estimate_add_ons.amount) as amount_total'),
                DB::raw('MAX(estimate_add_ons.name) as snapshot_name'),
            ])
            ->join('estimates', 'estimates.id', '=', 'estimate_add_ons.estimate_id')
            ->whereIn('estimates.status', [Estimate::STATUS_QUOTED, Estimate::STATUS_CONVERTED])
            ->groupBy('estimate_add_ons.add_on_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        $addOns = AddOn::query()
            ->with('translations')
            ->whereIn('id', $rows->pluck('add_on_id')->filter()->all())
            ->get()
            ->keyBy('id');

        return $rows
            ->map(fn ($row) => [
                'label' => $addOns->get($row->add_on_id)?->displayName()
                    ?: ((string) $row->snapshot_name ?: __('general.unknown')),
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
            ->with(['documentType.translations'])
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
