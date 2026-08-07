<?php

namespace App\Services\Vendor;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class VendorDashboardStats
{
    /**
     * @return array{
     *     this_month: int,
     *     active_orders: int,
     *     due_today: int,
     *     due_this_week: int,
     *     completed_orders: int,
     *     accepted_today: int,
     *     awaiting_payment: int,
     *     in_progress: int,
     *     assignment_mode: string
     * }
     */
    public function forVendor(int $vendorId): array
    {
        $mode = normalizeAssignmentMode(siteSettings()?->order_assignment_mode);
        $mine = Order::query()->where('vendor_id', $vendorId);

        $thisMonth = (clone $mine)
            ->whereNotNull('assigned_at')
            ->whereBetween('assigned_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->count();

        $activeOrders = (clone $mine)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->count();

        $completedOrders = (clone $mine)
            ->where('status', Order::STATUS_COMPLETED)
            ->count();

        $acceptedToday = (clone $mine)
            ->whereDate('assigned_at', Carbon::today())
            ->count();

        $awaitingPayment = (clone $mine)
            ->where('status', Order::STATUS_AWAITING_CUSTOMER_PAYMENT)
            ->count();

        $inProgress = (clone $mine)
            ->whereIn('status', [Order::STATUS_ASSIGNED, Order::STATUS_IN_PROGRESS])
            ->count();

        $dueBuckets = $this->dueBuckets(
            (clone $mine)
                ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
                ->with('deliverySpeed')
                ->get()
        );

        return [
            'this_month' => $thisMonth,
            'active_orders' => $activeOrders,
            'due_today' => $dueBuckets['today'],
            'due_this_week' => $dueBuckets['week'],
            'completed_orders' => $completedOrders,
            'accepted_today' => $acceptedToday,
            'awaiting_payment' => $awaitingPayment,
            'in_progress' => $inProgress,
            'assignment_mode' => $mode,
        ];
    }

    /**
     * Estimated due datetime from work start + delivery speed hours.
     */
    public function estimatedDueAt(Order $order): ?Carbon
    {
        $start = $order->paid_at ?? $order->confirmed_at ?? $order->assigned_at;
        if (! $start) {
            return null;
        }

        $hours = (int) ($order->deliverySpeed?->max_hours ?: $order->deliverySpeed?->min_hours ?: 0);
        if ($hours <= 0) {
            return null;
        }

        return $start->copy()->addHours($hours);
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return array{today: int, week: int}
     */
    public function dueBuckets(Collection $orders): array
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $today = 0;
        $week = 0;

        foreach ($orders as $order) {
            $due = $this->estimatedDueAt($order);
            if (! $due) {
                continue;
            }

            if ($due->isToday()) {
                $today++;
            }

            if ($due->betweenIncluded($weekStart, $weekEnd)) {
                $week++;
            }
        }

        return [
            'today' => $today,
            'week' => $week,
        ];
    }

    /**
     * Restrict a vendor orders query to due-today / due-this-week.
     *
     * @param  'due_today'|'due_week'  $bucket
     */
    public function constrainToDueBucket(Builder $query, string $bucket): Builder
    {
        $candidates = (clone $query)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->with('deliverySpeed')
            ->get();

        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $ids = $candidates
            ->filter(function (Order $order) use ($bucket, $weekStart, $weekEnd) {
                $due = $this->estimatedDueAt($order);
                if (! $due) {
                    return false;
                }

                if ($bucket === 'due_today') {
                    return $due->isToday();
                }

                return $due->betweenIncluded($weekStart, $weekEnd);
            })
            ->pluck('id')
            ->all();

        return $query->whereIn('id', $ids === [] ? [-1] : $ids);
    }
}
