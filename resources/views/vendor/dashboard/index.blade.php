@extends('vendor.layouts.app')

@section('css')
<link rel="stylesheet" href="{!! asset('assets/css/vendor-discover.css') !!}?v=20260807f" />
@endsection

@section('content')
@php
    $cards = [
        [
            'label' => __('general.vendor_dash_this_month'),
            'value' => number_format($kpis['this_month']),
            'hint' => __('general.vendor_dash_this_month_hint'),
            'icon' => 'ti ti-calendar-stats',
            'color' => 'info',
            'url' => route('vendor.orders.index', ['status' => 'this_month']),
        ],
        [
            'label' => __('general.vendor_dash_active_orders'),
            'value' => number_format($kpis['active_orders']),
            'hint' => __('general.vendor_dash_active_orders_hint'),
            'icon' => 'ti ti-briefcase',
            'color' => 'primary',
            'url' => route('vendor.orders.index', ['status' => 'active']),
        ],
        [
            'label' => __('general.vendor_dash_due_today'),
            'value' => number_format($kpis['due_today']),
            'hint' => __('general.vendor_dash_due_today_hint'),
            'icon' => 'ti ti-calendar-event',
            'color' => 'warning',
            'url' => route('vendor.orders.index', ['status' => 'due_today']),
        ],
        [
            'label' => __('general.vendor_dash_due_this_week'),
            'value' => number_format($kpis['due_this_week']),
            'hint' => __('general.vendor_dash_due_this_week_hint'),
            'icon' => 'ti ti-calendar-due',
            'color' => 'warning',
            'url' => route('vendor.orders.index', ['status' => 'due_week']),
        ],
        [
            'label' => __('general.vendor_dash_accepted_today'),
            'value' => number_format($kpis['accepted_today']),
            'hint' => __('general.vendor_dash_accepted_today_hint'),
            'icon' => 'ti ti-calendar-plus',
            'color' => 'primary',
            'url' => route('vendor.orders.index', ['status' => 'today']),
        ],
        [
            'label' => __('general.vendor_dash_awaiting_payment'),
            'value' => number_format($kpis['awaiting_payment']),
            'hint' => __('general.vendor_dash_awaiting_payment_hint'),
            'icon' => 'ti ti-credit-card',
            'color' => 'warning',
            'url' => route('vendor.orders.index', ['status' => 'awaiting_payment']),
        ],
        [
            'label' => __('general.vendor_dash_in_progress'),
            'value' => number_format($kpis['in_progress']),
            'hint' => __('general.vendor_dash_in_progress_hint'),
            'icon' => 'ti ti-loader',
            'color' => 'info',
            'url' => route('vendor.orders.index', ['status' => 'in_progress']),
        ],
        [
            'label' => __('general.vendor_dash_completed_orders'),
            'value' => number_format($kpis['completed_orders']),
            'hint' => __('general.vendor_dash_completed_orders_hint'),
            'icon' => 'ti ti-circle-check',
            'color' => 'success',
            'url' => route('vendor.orders.index', ['status' => 'completed']),
        ],
    ];
@endphp

<div class="mb-4">
    <h4 class="mb-1">{{ __('general.vendor_dashboard_welcome', ['name' => $vendorName]) }}</h4>
    <p class="text-muted mb-0">{{ __('general.vendor_dashboard_intro') }}</p>
</div>

<div class="row g-4 mb-4">
    @foreach ($cards as $card)
        <div class="col-sm-6 col-xl-3">
            <a href="{{ $card['url'] }}" class="card h-100 text-body" style="text-decoration: none;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 text-muted">{{ $card['label'] }}</p>
                            <h4 class="mb-0">{{ $card['value'] }}</h4>
                            <small class="text-muted">{{ $card['hint'] }}</small>
                        </div>
                        <span class="badge bg-label-{{ $card['color'] }} rounded p-2">
                            <i class="{{ $card['icon'] }} ti-sm"></i>
                        </span>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

@if (($kpis['assignment_mode'] ?? '') === 'open')
<div class="vendor-discover vendor-dashboard-discover">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h5 class="mb-0">{{ __('general.vendor_dash_latest_open') }}</h5>
            <small class="text-muted">{{ __('general.vendor_dash_latest_open_hint') }}</small>
        </div>
        <a href="{{ route('vendor.orders.discover') }}" class="btn btn-sm btn-primary">
            {{ __('general.vendor_dash_discover_more') }}
        </a>
    </div>

    <div class="vendor-discover__grid vendor-dashboard-discover__grid">
        @forelse ($latestOpenCards as $card)
            @include('vendor.orders.partials.discover-card', ['card' => $card])
        @empty
            <div class="vendor-discover__empty">
                <div class="vendor-discover__empty-icon"><i class="ti ti-world-search"></i></div>
                <h3>{{ __('general.vendor_discover_empty') }}</h3>
                <p>{{ __('general.vendor_discover_empty_hint') }}</p>
                <a href="{{ route('vendor.orders.discover') }}" class="btn btn-sm btn-primary mt-2">
                    {{ __('general.vendor_discover') }}
                </a>
            </div>
        @endforelse
    </div>
</div>
@endif
@endsection
