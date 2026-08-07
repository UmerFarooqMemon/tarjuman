@extends('vendor.layouts.app')

@section('css')
<link rel="stylesheet" href="{!! asset('assets/css/vendor-discover.css') !!}?v=1" />
@endsection

@section('content')
@php
    /** @var array $filters */
    /** @var array $cards */
    $mode = $mode ?? 'open';
    $discoverLabels = [
        'view' => __('general.view'),
        'accept' => __('general.accept'),
        'browse_more' => __('general.vendor_discover_browse_more'),
        'no_more' => __('general.vendor_discover_no_more'),
        'empty' => __('general.vendor_discover_empty'),
        'loading' => __('general.loading'),
        'load_failed' => __('general.vendor_discover_load_failed'),
        'pages' => __('general.pages'),
        'words' => __('general.words'),
    ];
@endphp

<div
    class="vendor-discover"
    data-vendor-discover
    data-feed-url="{{ route('vendor.orders.discover') }}"
    data-csrf="{{ csrf_token() }}"
    data-labels="{{ json_encode($discoverLabels, JSON_UNESCAPED_UNICODE) }}"
>
    <div class="vendor-discover__hero">
        <div class="vendor-discover__hero-copy">
            <p class="vendor-discover__eyebrow">{{ __('general.vendor_discover_eyebrow') }}</p>
            <h1 class="vendor-discover__title">{{ __('general.vendor_discover') }}</h1>
            <p class="vendor-discover__subtitle">{{ __('general.vendor_discover_subtitle') }}</p>
        </div>
        <div class="vendor-discover__hero-meta">
            <div class="vendor-discover__count">
                <span data-discover-total>{{ number_format((int) ($total ?? 0)) }}</span>
                <small>{{ __('general.vendor_discover_available') }}</small>
            </div>
        </div>
    </div>

    @if ($mode !== 'open')
        <div class="alert alert-warning mb-0">{{ __('general.vendor_open_accept_disabled') }}</div>
    @else
        <form class="vendor-discover__filters" method="GET" action="{{ route('vendor.orders.discover') }}" data-discover-filters>
            <div class="vendor-discover__filter-row">
                <div class="vendor-discover__search">
                    <i class="ti ti-search"></i>
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        class="form-control"
                        placeholder="{{ __('general.vendor_discover_search_placeholder') }}"
                        autocomplete="off"
                    >
                </div>

                <select name="sort" class="form-select vendor-discover__select">
                    <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>{{ __('general.vendor_discover_sort_newest') }}</option>
                    <option value="amount_desc" @selected(($filters['sort'] ?? '') === 'amount_desc')>{{ __('general.vendor_discover_sort_high_low') }}</option>
                    <option value="amount_asc" @selected(($filters['sort'] ?? '') === 'amount_asc')>{{ __('general.vendor_discover_sort_low_high') }}</option>
                </select>

                <select name="delivery_speed_id" class="form-select vendor-discover__select">
                    <option value="">{{ __('general.vendor_discover_all_delivery') }}</option>
                    @foreach ($deliverySpeeds as $speed)
                        <option value="{{ $speed->id }}" @selected((int) ($filters['delivery_speed_id'] ?? 0) === (int) $speed->id)>
                            {{ $speed->displayName() }}{{ $speed->displayDuration() ? ' · '.$speed->displayDuration() : '' }}
                        </option>
                    @endforeach
                </select>

                <select name="add_on_id" class="form-select vendor-discover__select">
                    <option value="">{{ __('general.vendor_discover_all_addons') }}</option>
                    @foreach ($addOns as $addOn)
                        <option value="{{ $addOn->id }}" @selected((int) ($filters['add_on_id'] ?? 0) === (int) $addOn->id)>
                            {{ $addOn->displayName() }}
                        </option>
                    @endforeach
                </select>

                <select name="document_type_id" class="form-select vendor-discover__select">
                    <option value="">{{ __('general.vendor_discover_all_documents') }}</option>
                    @foreach ($documentTypes as $type)
                        <option value="{{ $type->id }}" @selected((int) ($filters['document_type_id'] ?? 0) === (int) $type->id)>
                            {{ $type->displayName() }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">{{ __('general.apply') }}</button>
                @if (
                    filled($filters['q'] ?? null)
                    || ($filters['sort'] ?? 'newest') !== 'newest'
                    || filled($filters['delivery_speed_id'] ?? null)
                    || filled($filters['add_on_id'] ?? null)
                    || filled($filters['document_type_id'] ?? null)
                )
                    <a href="{{ route('vendor.orders.discover') }}" class="btn btn-outline-secondary">{{ __('general.reset') }}</a>
                @endif
            </div>
        </form>

        <div class="vendor-discover__grid" data-discover-grid>
            @forelse ($cards as $card)
                @include('vendor.orders.partials.discover-card', ['card' => $card])
            @empty
                <div class="vendor-discover__empty" data-discover-empty>
                    <div class="vendor-discover__empty-icon"><i class="ti ti-world-search"></i></div>
                    <h3>{{ __('general.vendor_discover_empty') }}</h3>
                    <p>{{ __('general.vendor_discover_empty_hint') }}</p>
                </div>
            @endforelse
        </div>

        <div class="vendor-discover__pager" data-discover-pager>
            @if ($hasMore ?? false)
                <button
                    type="button"
                    class="btn btn-outline-primary vendor-discover__browse"
                    data-discover-more
                    data-next-page="{{ $nextPage }}"
                >{{ __('general.vendor_discover_browse_more') }}</button>
            @elseif (count($cards) > 0)
                <p class="vendor-discover__no-more mb-0" data-discover-no-more>{{ __('general.vendor_discover_no_more') }}</p>
            @endif
        </div>
    @endif
</div>
@endsection

@section('footer-js')
@if (($mode ?? 'open') === 'open')
<script src="{!! asset('assets/js/vendor-discover.js') !!}?v=1"></script>
@endif
@endsection
