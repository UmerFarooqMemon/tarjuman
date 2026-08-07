@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">{!! __('general.menu_orders') !!}</h5>
        </div>
    </div>
    <div class="card-datatable text-nowrap">
        <table class="datatables-records table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>{!! __('general.status') !!}</th>
                    <th>{!! __('general.amount') !!}</th>
                    <th>{!! __('general.menu_vendors') !!}</th>
                    <th>{!! __('general.actions') !!}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>{{ $order->order_id }}</td>
                        <td class="text-wrap"><div class="d-flex flex-wrap gap-1">{!! orderStatusBadge($order->status) !!}{!! orderPaymentStatusBadge($order->payment_status) !!}</div></td>
                        <td>{!! formatMoney($order->confirmed_amount ?? $order->estimate_amount, $order->currency) !!}</td>
                        <td>{{ $order->vendor?->displayName() ?: '—' }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-body" title="{{ __('general.view') }}">
                                    <i class="text-primary ti ti-eye"></i>
                                </a>
                                @if (adminCanAssignOrder($order))
                                    @can('orders.edit')
                                        <a
                                            href="{{ route('admin.orders.show', $order) }}#assign-vendor"
                                            class="btn btn-sm btn-primary"
                                        >{{ __('general.assign_vendor') }}</a>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('footer-js')
<script>
    var ordersTable = $('.datatables-records').DataTable({
        language: { url: langUrl },
        pageLength: 50,
        order: [],
        columnDefs: [{ orderable: false, targets: [4] }],
        scrollX: true
    });

    var filterOrderId = @json(request('order_id'));
    if (filterOrderId) {
        ordersTable.search(filterOrderId).draw();
    }
</script>
@endsection
