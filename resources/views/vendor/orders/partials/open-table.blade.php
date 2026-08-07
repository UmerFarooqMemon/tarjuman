@php
    /** @var \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\Paginator $orders */
    /** @var string $mode */
    $tableClass = $tableClass ?? 'datatables-open-orders';
@endphp

@if (($mode ?? 'open') !== 'open')
    <div class="card-body">
        <p class="text-muted mb-0">{{ __('general.vendor_open_accept_disabled') }}</p>
    </div>
@else
    <table class="{{ $tableClass }} table">
        <thead>
            <tr>
                <th>{{ __('general.order_id') }}</th>
                <th>{{ __('general.amount') }}</th>
                <th>{{ __('general.status') }}</th>
                <th>{{ __('general.created_at') }}</th>
                <th>{!! __('general.actions') !!}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->order_id }}</td>
                    <td>{!! formatMoney($order->estimate_amount, $order->currency) !!}</td>
                    <td>{!! orderStatusBadge($order->status) !!}</td>
                    <td>{{ optional($order->created_at)?->format('Y-m-d H:i') }}</td>
                    <td>
                        <div class="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="btn btn-sm btn-secondary"
                                data-vendor-order-view
                                data-view-url="{{ route('vendor.orders.details', $order) }}"
                            >{{ __('general.view') }}</button>
                            <form method="POST" action="{{ route('vendor.orders.accept', $order) }}">
                                @csrf
                                <button class="btn btn-sm btn-primary" type="submit">{{ __('general.accept') }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
@endif
