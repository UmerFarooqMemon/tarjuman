@extends('admin.layouts.app')

@section('content')
@php
    $kpis = $dashboard['kpis'];
    $quotesOverTime = $dashboard['quotes_over_time'];
    $languagePairs = $dashboard['language_pairs'];
    $documentTypes = $dashboard['document_types'];
    $pricingRules = $dashboard['pricing_rules'];
    $deliverySpeeds = $dashboard['delivery_speeds'];
    $topAddOns = $dashboard['top_add_ons'];
    $recentQuotes = $dashboard['recent_quotes'];
@endphp

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 text-muted">{{ __('general.dash_quotes_today') }}</p>
                        <h4 class="mb-0">{{ number_format($kpis['quotes_today']) }}</h4>
                        <small class="text-muted">{{ __('general.dash_quotes_this_week') }}: {{ number_format($kpis['quotes_this_week']) }}</small>
                    </div>
                    <span class="badge bg-label-primary rounded p-2">
                        <i class="ti ti-file-analytics ti-sm"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 text-muted">{{ __('general.dash_conversion_rate') }}</p>
                        <h4 class="mb-0">{{ number_format($kpis['conversion_rate'], 1) }}%</h4>
                        <small class="text-muted">{{ __('general.dash_converted_of_current', ['converted' => $kpis['converted_total'], 'current' => $kpis['current_total']]) }}</small>
                    </div>
                    <span class="badge bg-label-success rounded p-2">
                        <i class="ti ti-percentage ti-sm"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 text-muted">{{ __('general.dash_pipeline_value') }}</p>
                        <h4 class="mb-0">{!! formatMoney($kpis['pipeline_value']) !!}</h4>
                        <small class="text-muted">{{ __('general.dash_open_quotes', ['count' => $kpis['quoted_total']]) }}</small>
                    </div>
                    <span class="badge bg-label-warning rounded p-2">
                        <i class="ti ti-coin ti-sm"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 text-muted">{{ __('general.dash_avg_quote_value') }}</p>
                        <h4 class="mb-0">{!! formatMoney($kpis['avg_quote_value']) !!}</h4>
                        <small class="text-muted">{{ __('general.dash_add_on_attach_rate') }}: {{ number_format($kpis['add_on_attach_rate'], 1) }}%</small>
                    </div>
                    <span class="badge bg-label-info rounded p-2">
                        <i class="ti ti-chart-bar ti-sm"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('general.dash_quotes_over_time') }}</h5>
                <small class="text-muted">{{ __('general.dash_last_30_days') }}</small>
            </div>
            <div class="card-body">
                <div id="dashQuotesOverTime" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('general.dash_language_pairs') }}</h5>
            </div>
            <div class="card-body">
                <div id="dashLanguagePairs" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('general.dash_document_types') }}</h5>
            </div>
            <div class="card-body">
                <div id="dashDocumentTypes" style="min-height: 260px;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('general.dash_pricing_rules') }}</h5>
            </div>
            <div class="card-body">
                <div id="dashPricingRules" style="min-height: 260px;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('general.dash_delivery_speeds') }}</h5>
            </div>
            <div class="card-body">
                <div id="dashDeliverySpeeds" style="min-height: 260px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">{{ __('general.dash_top_add_ons') }}</h5>
                <span class="badge bg-label-primary">{{ number_format($kpis['add_on_attach_rate'], 1) }}% {{ __('general.dash_attach_rate_short') }}</span>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('general.name') }}</th>
                            <th class="text-end">{{ __('general.count') }}</th>
                            <th class="text-end">{{ __('general.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topAddOns as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td class="text-end">{{ number_format($row['count']) }}</td>
                                <td class="text-end">{!! formatMoney($row['amount']) !!}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">{{ __('general.dash_no_data_yet') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('general.dash_recent_quotes') }}</h5>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('general.languages') }}</th>
                            <th>{{ __('general.document_type') }}</th>
                            <th>{{ __('general.pages') }} / {{ __('general.words') }}</th>
                            <th>{{ __('general.status') }}</th>
                            <th class="text-end">{{ __('general.total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentQuotes as $quote)
                            <tr>
                                <td>{{ $quote->id }}</td>
                                <td>
                                    <span class="text-nowrap">{{ $quote->source_language_code }} → {{ $quote->target_language_code }}</span>
                                </td>
                                <td>{{ $quote->documentType?->displayName() ?: ($quote->document_type_name ?: '—') }}</td>
                                <td>{{ number_format($quote->page_count) }} / {{ number_format($quote->word_count) }}</td>
                                <td>
                                    @if ($quote->status === \App\Models\Estimate::STATUS_CONVERTED)
                                        <span class="badge bg-label-success">{{ __('general.dash_status_converted') }}</span>
                                    @else
                                        <span class="badge bg-label-warning">{{ __('general.dash_status_quoted') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">{!! formatMoney($quote->total_amount, $quote->currency) !!}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">{{ __('general.dash_no_data_yet') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@php
    $chartData = [
        'quotesOverTime' => $quotesOverTime,
        'languagePairs' => $languagePairs,
        'documentTypes' => $documentTypes,
        'pricingRules' => $pricingRules,
        'deliverySpeeds' => $deliverySpeeds,
    ];
@endphp

@push('footer-js')
<script>
(function () {
    if (typeof ApexCharts === 'undefined') {
        return;
    }

    const primary = getComputedStyle(document.documentElement).getPropertyValue('--admin-primary').trim() || '#7367f0';
    const chartData = @json($chartData);

    const emptyState = (el, message) => {
        if (!el) return;
        el.innerHTML = '<div class="d-flex align-items-center justify-content-center text-muted h-100 py-5">' + message + '</div>';
    };

    const noData = @json(__('general.dash_no_data_yet'));
    const quotesLabel = @json(__('general.dash_quotes'));
    const countLabel = @json(__('general.count'));

    const lineEl = document.querySelector('#dashQuotesOverTime');
    if (lineEl) {
        const hasData = (chartData.quotesOverTime.series || []).some((n) => n > 0);
        if (!hasData) {
            emptyState(lineEl, noData);
        } else {
            new ApexCharts(lineEl, {
                chart: { type: 'area', height: 300, toolbar: { show: false } },
                series: [{ name: quotesLabel, data: chartData.quotesOverTime.series }],
                xaxis: { categories: chartData.quotesOverTime.labels, labels: { rotate: -45 } },
                colors: [primary],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.45, opacityTo: 0.05 } },
                grid: { borderColor: 'rgba(75,70,92,0.08)' },
            }).render();
        }
    }

    const renderBar = (selector, rows, horizontal, options = {}) => {
        const el = document.querySelector(selector);
        if (!el) return;
        if (!rows.length) {
            emptyState(el, noData);
            return;
        }
        const showCategoryLabels = options.showCategoryLabels !== false;
        new ApexCharts(el, {
            chart: { type: 'bar', height: horizontal ? 300 : 260, toolbar: { show: false } },
            series: [{ name: countLabel, data: rows.map((r) => r.count) }],
            xaxis: {
                categories: rows.map((r) => r.label),
                labels: { show: horizontal ? true : showCategoryLabels },
            },
            yaxis: {
                labels: { show: horizontal ? showCategoryLabels : true },
            },
            plotOptions: { bar: { horizontal: !!horizontal, borderRadius: 6, columnWidth: '45%' } },
            colors: [primary],
            dataLabels: { enabled: false },
            tooltip: {
                y: { formatter: (val) => val },
                x: { show: true },
            },
            grid: { borderColor: 'rgba(75,70,92,0.08)' },
        }).render();
    };

    const renderDonut = (selector, rows) => {
        const el = document.querySelector(selector);
        if (!el) return;
        if (!rows.length) {
            emptyState(el, noData);
            return;
        }
        new ApexCharts(el, {
            chart: { type: 'donut', height: 260 },
            labels: rows.map((r) => r.label),
            series: rows.map((r) => r.count),
            legend: { position: 'bottom' },
            dataLabels: { enabled: false },
            colors: [primary, '#28c76f', '#ff9f43', '#00cfe8', '#ea5455', '#a8aaae', '#7367f0', '#82868b'],
        }).render();
    };

    renderBar('#dashLanguagePairs', chartData.languagePairs, true);
    renderDonut('#dashDocumentTypes', chartData.documentTypes);
    renderBar('#dashPricingRules', chartData.pricingRules, false, { showCategoryLabels: false });
    renderDonut('#dashDeliverySpeeds', chartData.deliverySpeeds);
})();
</script>
@endpush
