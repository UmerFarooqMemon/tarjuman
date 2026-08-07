<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Services\Vendor\VendorDashboardStats;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class VendorMyOrdersQuery
{
    /**
     * @return array{
     *     q: string,
     *     status: string,
     *     payment_status: string,
     *     add_on_id: ?int,
     *     delivery_speed_id: ?int,
     *     document_type_id: ?int,
     *     sort: string
     * }
     */
    public function filtersFromRequest(Request $request): array
    {
        $sort = (string) $request->input('sort', 'newest');
        if (! in_array($sort, ['newest', 'amount_desc', 'amount_asc', 'oldest'], true)) {
            $sort = 'newest';
        }

        $status = trim((string) $request->input('status', ''));
        $paymentStatus = trim((string) $request->input('payment_status', ''));

        return [
            'q' => trim((string) $request->input('q', $request->input('order_id', ''))),
            'status' => $status,
            'payment_status' => $paymentStatus,
            'add_on_id' => $request->filled('add_on_id') ? (int) $request->input('add_on_id') : null,
            'delivery_speed_id' => $request->filled('delivery_speed_id') ? (int) $request->input('delivery_speed_id') : null,
            'document_type_id' => $request->filled('document_type_id') ? (int) $request->input('document_type_id') : null,
            'sort' => $sort,
        ];
    }

    /**
     * @param  array{
     *     q: string,
     *     status: string,
     *     payment_status: string,
     *     add_on_id: ?int,
     *     delivery_speed_id: ?int,
     *     document_type_id: ?int,
     *     sort: string
     * }  $filters
     * @return Collection<int, Order>
     */
    public function get(int $vendorId, array $filters): Collection
    {
        return $this->query($vendorId, $filters)->get();
    }

    /**
     * @param  array{
     *     q: string,
     *     status: string,
     *     payment_status: string,
     *     add_on_id: ?int,
     *     delivery_speed_id: ?int,
     *     document_type_id: ?int,
     *     sort: string
     * }  $filters
     */
    public function query(int $vendorId, array $filters): Builder
    {
        $query = Order::query()
            ->with([
                'documentType.translations',
                'deliverySpeed.translations',
                'addOns.addOn.translations',
            ])
            ->where('vendor_id', $vendorId);

        if ($filters['q'] !== '') {
            $term = '%'.$filters['q'].'%';
            $query->where(function (Builder $inner) use ($term) {
                $inner->where('order_id', 'like', $term)
                    ->orWhereHas('documentType.translations', fn (Builder $t) => $t->where('name', 'like', $term));
            });
        }

        $status = $filters['status'];
        if ($status === 'action_required') {
            $query->where('status', Order::STATUS_PENDING_VENDOR_CONFIRM);
        } elseif ($status === 'accepted' || $status === 'active') {
            $query->whereNotIn('status', [Order::STATUS_CANCELLED, Order::STATUS_COMPLETED]);
        } elseif ($status === 'this_month') {
            $query->whereNotNull('assigned_at')
                ->whereBetween('assigned_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ]);
        } elseif ($status === 'awaiting_payment') {
            $query->where('status', Order::STATUS_AWAITING_CUSTOMER_PAYMENT);
        } elseif ($status === 'in_progress') {
            $query->whereIn('status', [Order::STATUS_ASSIGNED, Order::STATUS_IN_PROGRESS]);
        } elseif ($status === 'today') {
            $query->whereDate('assigned_at', now()->toDateString());
        } elseif ($status === 'due_today' || $status === 'due_week') {
            app(VendorDashboardStats::class)
                ->constrainToDueBucket($query, $status);
        } elseif ($status !== '') {
            $query->where('status', $status);
        }

        if ($filters['payment_status'] !== '') {
            $query->where('payment_status', $filters['payment_status']);
        }

        if ($filters['add_on_id']) {
            $query->whereHas('addOns', fn (Builder $a) => $a->where('add_on_id', $filters['add_on_id']));
        }

        if ($filters['delivery_speed_id']) {
            $query->where('delivery_speed_id', $filters['delivery_speed_id']);
        }

        if ($filters['document_type_id']) {
            $query->where('document_type_id', $filters['document_type_id']);
        }

        return match ($filters['sort']) {
            'amount_desc' => $query->orderByRaw('COALESCE(confirmed_amount, estimate_amount) DESC')->orderByDesc('id'),
            'amount_asc' => $query->orderByRaw('COALESCE(confirmed_amount, estimate_amount) ASC')->orderByDesc('id'),
            'oldest' => $query->oldest('id'),
            default => $query->latest('id'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function statusOptions(): array
    {
        return [
            ['value' => '', 'label' => __('general.vendor_orders_all_statuses')],
            ['value' => 'action_required', 'label' => __('general.vendor_dash_action_required')],
            ['value' => 'this_month', 'label' => __('general.vendor_dash_this_month')],
            ['value' => 'active', 'label' => __('general.vendor_dash_active_orders')],
            ['value' => 'due_today', 'label' => __('general.vendor_dash_due_today')],
            ['value' => 'due_week', 'label' => __('general.vendor_dash_due_this_week')],
            ['value' => Order::STATUS_PENDING_VENDOR_CONFIRM, 'label' => formatOrderStatus(Order::STATUS_PENDING_VENDOR_CONFIRM)],
            ['value' => Order::STATUS_ASSIGNED, 'label' => formatOrderStatus(Order::STATUS_ASSIGNED)],
            ['value' => Order::STATUS_AWAITING_CUSTOMER_PAYMENT, 'label' => formatOrderStatus(Order::STATUS_AWAITING_CUSTOMER_PAYMENT)],
            ['value' => Order::STATUS_IN_PROGRESS, 'label' => formatOrderStatus(Order::STATUS_IN_PROGRESS)],
            ['value' => Order::STATUS_COMPLETED, 'label' => formatOrderStatus(Order::STATUS_COMPLETED)],
            ['value' => Order::STATUS_CANCELLED, 'label' => formatOrderStatus(Order::STATUS_CANCELLED)],
            ['value' => 'today', 'label' => __('general.vendor_dash_accepted_today')],
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function paymentStatusOptions(): array
    {
        return [
            ['value' => '', 'label' => __('general.vendor_orders_all_payments')],
            ['value' => Order::PAYMENT_UNPAID, 'label' => formatOrderPaymentStatus(Order::PAYMENT_UNPAID)],
            ['value' => Order::PAYMENT_PENDING, 'label' => formatOrderPaymentStatus(Order::PAYMENT_PENDING)],
            ['value' => Order::PAYMENT_PAID, 'label' => formatOrderPaymentStatus(Order::PAYMENT_PAID)],
            ['value' => Order::PAYMENT_REFUNDED, 'label' => formatOrderPaymentStatus(Order::PAYMENT_REFUNDED)],
            ['value' => Order::PAYMENT_COVERED_BY_PLAN, 'label' => formatOrderPaymentStatus(Order::PAYMENT_COVERED_BY_PLAN)],
        ];
    }
}
