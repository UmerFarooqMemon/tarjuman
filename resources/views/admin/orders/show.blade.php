@extends('admin.layouts.app')

@section('content')
@php
    $auditEvents = $order->events
        ->sortByDesc('created_at')
        ->values();

    $feeBreakdown = orderFeeBreakdown($order->confirmed_amount ?? $order->estimate_amount);

    $actorCache = [
        'vendor_user' => [],
        'admin' => [],
    ];

    $resolveActor = function (?string $type, $id) use (&$actorCache): string {
        if (! $type || ! $id) {
            return '—';
        }

        if ($type === 'vendor_user') {
            if (! array_key_exists($id, $actorCache['vendor_user'])) {
                $user = \App\Models\VendorUser::query()->with('vendor')->find($id);
                $actorCache['vendor_user'][$id] = $user
                    ? trim(($user->fullName() ?: 'Vendor user').' · '.($user->vendor?->displayName() ?: '#'.$user->vendor_id))
                    : __('general.vendor').' #'.$id;
            }

            return $actorCache['vendor_user'][$id];
        }

        if ($type === 'admin') {
            if (! array_key_exists($id, $actorCache['admin'])) {
                $admin = \App\Models\Admin::query()->find($id);
                $actorCache['admin'][$id] = $admin
                    ? ($admin->fullName() ?: $admin->email ?: 'Admin #'.$id)
                    : 'Admin #'.$id;
            }

            return $actorCache['admin'][$id];
        }

        return ucfirst(str_replace('_', ' ', $type)).' #'.$id;
    };
@endphp

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">{{ __('general.menu_orders') }} #{{ $order->order_id }}</h5>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-label-secondary">{{ __('general.back') }}</a>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">{{ __('general.status') }}</dt>
            <dd class="col-sm-9 d-flex flex-wrap gap-1">{!! orderStatusBadge($order->status) !!}{!! orderPaymentStatusBadge($order->payment_status) !!}</dd>
            <dt class="col-sm-3">{{ __('general.vendor_amount') }}</dt>
            <dd class="col-sm-9">{!! formatMoney($feeBreakdown['vendor_amount'], $order->currency) !!}</dd>
            <dt class="col-sm-3">{{ __('general.platform_fee') }}</dt>
            <dd class="col-sm-9">
                <span class="d-inline-flex align-items-center flex-wrap gap-1">
                    {!! formatMoney($feeBreakdown['platform_fee'], $order->currency) !!}
                    @if ($feeBreakdown['fee_percent'] > 0)
                        <span class="text-muted small lh-1">({{ rtrim(rtrim(number_format($feeBreakdown['fee_percent'], 2, '.', ''), '0'), '.') }}%)</span>
                    @endif
                </span>
            </dd>
            <dt class="col-sm-3">{{ __('general.order_total') }}</dt>
            <dd class="col-sm-9">{!! formatMoney($feeBreakdown['total'], $order->currency) !!}</dd>
            <dt class="col-sm-3">{{ __('general.platform_payment_mode') }}</dt>
            <dd class="col-sm-9">{{ formatPaymentTimingMode($order->payment_timing_snapshot) }}</dd>
            <dt class="col-sm-3">{{ __('general.platform_assignment_mode') }}</dt>
            <dd class="col-sm-9">{{ formatAssignmentMode($order->assignment_mode_snapshot) }}</dd>
            <dt class="col-sm-3">{{ __('general.customer') }}</dt>
            <dd class="col-sm-9">{{ $order->customer?->fullName() }} &lt;{{ $order->customer?->email }}&gt;</dd>
            <dt class="col-sm-3">{{ __('general.vendor') }}</dt>
            <dd class="col-sm-9">{{ $order->vendor?->displayName() ?: '—' }}</dd>
        </dl>
        <p class="text-muted small mt-3 mb-0">{{ __('general.order_fee_breakdown_hint') }}</p>
    </div>
</div>

@php
    $canAssign = adminCanAssignOrder($order);
    $needsAssignment = ! $order->vendor_id
        && (platformAssignmentIsManual() || orderAssignmentMode($order) === 'manual');
@endphp

@if ($needsAssignment)
    @can('orders.edit')
    <div class="card mb-4" id="assign-vendor">
        <div class="card-header">
            <h5 class="mb-0">{{ __('general.assign_vendor') }}</h5>
            <small class="text-muted">{{ __('general.assign_vendor_hint') }}</small>
        </div>
        <div class="card-body">
            @if ($canAssign)
                @if ($vendors->isEmpty())
                    <div class="alert alert-warning mb-0">{{ __('general.assign_vendor_no_vendors') }}</div>
                @else
                    <form method="POST" action="{{ route('admin.orders.assign', $order) }}" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label" for="assignVendorId">{{ __('general.menu_vendors') }}</label>
                            <select name="vendor_id" id="assignVendorId" class="form-select" required>
                                <option value="">{{ __('general.select_vendor') }}</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" @selected((string) old('vendor_id') === (string) $vendor->id)>
                                        {{ $vendor->displayName() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">{{ __('general.assign_vendor') }}</button>
                        </div>
                    </form>
                @endif
            @elseif ($order->status === \App\Models\Order::STATUS_PENDING_PAYMENT)
                <div class="alert alert-info mb-0">{{ __('general.assign_vendor_waiting_payment') }}</div>
            @else
                <div class="alert alert-secondary mb-0">{{ __('general.order_not_assignable') }}</div>
            @endif
        </div>
    </div>
    @endcan
@endif

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">{{ __('general.order_documents') }}</h5>
    </div>
    <div class="card-body">
        @if ($order->documents->isEmpty())
            <p class="text-muted mb-0">{{ __('general.no_documents_found') }}</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('general.file_name') }}</th>
                            <th>{{ __('general.type') }}</th>
                            <th>{{ __('general.size') }}</th>
                            <th>{{ __('general.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->documents as $document)
                            <tr>
                                <td>{{ $document->original_name }}</td>
                                <td>{{ $document->mime ?: '—' }}</td>
                                <td>{{ number_format(($document->size ?? 0) / 1024, 1) }} KB</td>
                                <td>{{ $document->isPurged() ? __('general.order_document_purged') : __('general.active') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('general.order_audit_trail') }}</h5>
        <small class="text-muted">{{ __('general.order_audit_trail_hint') }}</small>
    </div>
    <div class="card-body">
        @if ($auditEvents->isEmpty())
            <p class="text-muted mb-0">{{ __('general.no_records_found') }}</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('general.created_at') }}</th>
                            <th>{{ __('general.event') }}</th>
                            <th>{{ __('general.actor') }}</th>
                            <th>{{ __('general.details') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($auditEvents as $event)
                            @php
                                $payload = is_array($event->payload) ? $event->payload : [];
                                $detailParts = [];
                                if (! empty($payload['document_name'])) {
                                    $detailParts[] = $payload['document_name'];
                                }
                                if (! empty($payload['document_uuid'])) {
                                    $detailParts[] = $payload['document_uuid'];
                                }
                                if (! empty($payload['ip'])) {
                                    $detailParts[] = 'IP '.$payload['ip'];
                                }
                                if (isset($payload['confirmed_amount'])) {
                                    $detailParts[] = __('general.confirmed_amount').': '.$payload['confirmed_amount'];
                                }
                                if (! empty($payload['user_agent'])) {
                                    $detailParts[] = \Illuminate\Support\Str::limit($payload['user_agent'], 80);
                                }
                            @endphp
                            <tr>
                                <td>{{ optional($event->created_at)?->format('Y-m-d H:i:s') }}</td>
                                <td>{{ formatOrderEventType($event->type) }}</td>
                                <td>{{ $resolveActor($event->actor_type, $event->actor_id) }}</td>
                                <td>{{ $detailParts ? implode(' · ', $detailParts) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
