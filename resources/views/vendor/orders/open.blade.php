@extends('vendor.layouts.app')

@section('css')
<link rel="stylesheet" href="{!! asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') !!}" />
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">{{ __('general.vendor_open_orders') }}</h5>
            <small class="text-muted">{{ __('general.vendor_dash_open_table_hint') }}</small>
        </div>
        <a href="{{ route('vendor.orders.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('general.vendor_my_orders') }}</a>
    </div>
    <div class="card-datatable text-nowrap">
        @include('vendor.orders.partials.open-table', [
            'orders' => $orders,
            'mode' => $mode,
            'tableClass' => 'datatables-open-orders',
        ])
    </div>
</div>
@endsection

@section('footer-js')
@if ($mode === 'open')
<script src="{!! asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') !!}"></script>
<script>
    $('.datatables-open-orders').DataTable({
        language: { url: langUrl },
        pageLength: 25,
        order: [],
        columnDefs: [{ orderable: false, targets: [-1] }],
        scrollX: true
    });
</script>
@endif
@endsection
