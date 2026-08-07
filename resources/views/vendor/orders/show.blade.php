@extends('vendor.layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-1">{{ $order->order_id }}</h4>
        <p class="mb-0 d-flex flex-wrap align-items-center gap-1">
            {!! orderStatusBadge($order->status) !!}
            {!! orderPaymentStatusBadge($order->payment_status) !!}
        </p>
    </div>
    <a href="{{ $canConfirm ? route('vendor.orders.index', ['status' => 'action_required']) : route('vendor.orders.index') }}" class="btn btn-outline-secondary">
        {{ __('general.back') }}
    </a>
</div>

@include('vendor.orders.partials.details-layout', [
    'order' => $order,
    'details' => $details,
    'canConfirm' => $canConfirm,
    'canSendPaymentLink' => $canSendPaymentLink ?? false,
    'canManageDelivery' => $canManageDelivery ?? false,
    'canComplete' => $canComplete ?? false,
    'canAccessDocuments' => $canAccessDocuments,
])

@if ($canConfirm)
<script>
(function () {
    var input = document.getElementById('confirmed_amount');
    if (!input) return;

    var percent = parseFloat(input.getAttribute('data-fee-percent') || '0') || 0;
    var currencyTpl = document.querySelector('[data-currency-icon]');
    var currencyHtml = currencyTpl ? currencyTpl.innerHTML : '';
    var vendorEl = document.querySelector('[data-fee-vendor]');
    var feeEl = document.querySelector('[data-fee-platform]');
    var totalEl = document.querySelector('[data-fee-total]');

    function money(n, minus) {
        var value = Math.abs(n);
        return '<span class="d-inline-flex align-items-center gap-1 vendor-order-money' +
            (minus ? ' text-danger' : '') + '">' +
            (minus ? '<span>-</span>' : '') +
            currencyHtml +
            '<span>' + (Math.round(value * 100) / 100).toFixed(2) + '</span></span>';
    }

    function refresh() {
        var total = Math.max(0, parseFloat(input.value) || 0);
        var fee = Math.min(total, Math.round((total * percent / 100) * 100) / 100);
        var vendor = Math.round((total - fee) * 100) / 100;
        if (vendorEl) vendorEl.innerHTML = money(vendor);
        if (feeEl) feeEl.innerHTML = money(fee, true);
        if (totalEl) totalEl.innerHTML = money(total);
    }

    input.addEventListener('input', refresh);
    refresh();
})();
</script>
@endif
@endsection
