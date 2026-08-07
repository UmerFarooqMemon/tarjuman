@extends('vendor.layouts.app')

@section('css')
<link rel="stylesheet" href="{!! asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') !!}" />
<link rel="stylesheet" href="{!! asset('assets/css/vendor-discover.css') !!}?v=6" />
@endsection

@section('content')
@php
    /** @var array $filters */
@endphp

<div class="card vendor-orders-page">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">{{ __('general.vendor_my_orders') }}</h5>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button
                type="button"
                class="btn btn-outline-primary btn-sm vendor-orders-toolbar-btn"
                data-orders-filters-toggle
                aria-expanded="false"
                aria-controls="vendorOrdersFilters"
            >
                <i class="ti ti-filter me-1"></i>{{ __('general.filters') }}
                <span class="badge bg-primary rounded-pill ms-1" data-orders-filters-badge hidden></span>
            </button>
            <button
                type="button"
                class="btn btn-sm btn-outline-secondary vendor-orders-toolbar-btn d-none"
                data-orders-filters-reset
            >
                {{ __('general.reset') }}
            </button>
        </div>
    </div>

    <div class="collapse border-bottom" id="vendorOrdersFilters" data-orders-filters-panel>
        <form class="p-3" data-orders-filters>
            <div class="vendor-discover__filter-row vendor-orders-filters">
                <div class="vendor-discover__search">
                    <i class="ti ti-search"></i>
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        class="form-control"
                        placeholder="{{ __('general.vendor_orders_search_placeholder') }}"
                        autocomplete="off"
                        data-orders-filter-input
                    >
                </div>

                <select name="status" class="form-select vendor-discover__select" data-orders-filter-input>
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option['value'] }}" @selected(($filters['status'] ?? '') === $option['value'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>

                <select name="payment_status" class="form-select vendor-discover__select" data-orders-filter-input>
                    @foreach ($paymentStatusOptions as $option)
                        <option value="{{ $option['value'] }}" @selected(($filters['payment_status'] ?? '') === $option['value'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>

                <select name="add_on_id" class="form-select vendor-discover__select" data-orders-filter-input>
                    <option value="">{{ __('general.vendor_discover_all_addons') }}</option>
                    @foreach ($addOns as $addOn)
                        <option value="{{ $addOn->id }}" @selected((int) ($filters['add_on_id'] ?? 0) === (int) $addOn->id)>
                            {{ $addOn->displayName() }}
                        </option>
                    @endforeach
                </select>

                <select name="delivery_speed_id" class="form-select vendor-discover__select" data-orders-filter-input>
                    <option value="">{{ __('general.vendor_discover_all_delivery') }}</option>
                    @foreach ($deliverySpeeds as $speed)
                        <option value="{{ $speed->id }}" @selected((int) ($filters['delivery_speed_id'] ?? 0) === (int) $speed->id)>
                            {{ $speed->displayName() }}
                        </option>
                    @endforeach
                </select>

                <select name="document_type_id" class="form-select vendor-discover__select" data-orders-filter-input>
                    <option value="">{{ __('general.vendor_discover_all_documents') }}</option>
                    @foreach ($documentTypes as $type)
                        <option value="{{ $type->id }}" @selected((int) ($filters['document_type_id'] ?? 0) === (int) $type->id)>
                            {{ $type->displayName() }}
                        </option>
                    @endforeach
                </select>

                <select name="sort" class="form-select vendor-discover__select" data-orders-filter-input>
                    <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>{{ __('general.vendor_discover_sort_newest') }}</option>
                    <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>{{ __('general.vendor_orders_sort_oldest') }}</option>
                    <option value="amount_desc" @selected(($filters['sort'] ?? '') === 'amount_desc')>{{ __('general.vendor_discover_sort_high_low') }}</option>
                    <option value="amount_asc" @selected(($filters['sort'] ?? '') === 'amount_asc')>{{ __('general.vendor_discover_sort_low_high') }}</option>
                </select>
            </div>
        </form>
    </div>

    <div class="card-datatable text-nowrap">
        <table class="datatables-my-orders table w-100" data-orders-table data-orders-url="{{ $dataUrl }}">
            <thead>
                <tr>
                    <th>{{ __('general.order_id') }}</th>
                    <th>{{ __('general.document_type') }}</th>
                    <th>{{ __('general.status') }}</th>
                    <th>{{ __('general.payment_status') }}</th>
                    <th>{{ __('general.amount') }}</th>
                    <th>{{ __('general.created_at') }}</th>
                    <th>{!! __('general.actions') !!}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('footer-js')
<script src="{!! asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') !!}"></script>
<script src="{!! asset('assets/js/vendor-orders.js') !!}?v=4"></script>
@endsection
