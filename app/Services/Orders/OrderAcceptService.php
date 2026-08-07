<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\Vendor;
use App\Services\Orders\VendorEarningService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class OrderAcceptService
{
    public function __construct(
        protected VendorEarningService $earnings,
        protected OrderNotificationDispatcher $notifier,
    ) {}

    /**
     * Atomic first-to-accept claim for open orders.
     */
    public function accept(Order $order, Vendor $vendor, int $vendorUserId): Order
    {
        $this->assertOpenAcceptMode($order);

        $fresh = DB::transaction(function () use ($order, $vendor, $vendorUserId) {
            $updated = Order::query()
                ->whereKey($order->id)
                ->where('status', Order::STATUS_OPEN)
                ->whereNull('vendor_id')
                ->update([
                    'vendor_id' => $vendor->id,
                    'accepted_by_vendor_user_id' => $vendorUserId,
                    'status' => $this->statusAfterAssign($order),
                    'assigned_at' => now(),
                ]);

            $fresh = $order->fresh();

            if ($updated === 0) {
                if ($fresh && (int) $fresh->vendor_id === (int) $vendor->id) {
                    return $fresh;
                }

                throw new ConflictHttpException(__('general.order_already_taken'));
            }

            $this->recordEvent($fresh, 'accepted', 'vendor_user', $vendorUserId, [
                'vendor_id' => $vendor->id,
            ]);

            $this->earnings->accrueForOrder($fresh);

            return $fresh;
        });

        $this->notifier->orderAccepted($fresh);

        return $fresh;
    }

    /**
     * Admin manual assignment.
     */
    public function assignManually(Order $order, Vendor $vendor, ?int $adminId = null): Order
    {
        $platformManual = platformAssignmentIsManual();
        $snapshotManual = normalizeAssignmentMode($order->assignment_mode_snapshot) === 'manual';

        // Snapshot wins for marketplace-placed jobs unless platform is currently manual
        // (ops may switch mode and still need to place unassigned open orders).
        if (! $platformManual && ! $snapshotManual) {
            throw ValidationException::withMessages([
                'assignment' => [__('general.order_manual_assign_disabled')],
            ]);
        }

        if ($order->vendor_id) {
            throw ValidationException::withMessages([
                'vendor_id' => [__('general.order_already_assigned')],
            ]);
        }

        if (! in_array($order->status, [Order::STATUS_OPEN], true)) {
            throw ValidationException::withMessages([
                'status' => [__('general.order_not_assignable')],
            ]);
        }

        if (! $vendor->is_active || ! $vendor->is_approved) {
            throw ValidationException::withMessages([
                'vendor_id' => [__('general.order_vendor_not_eligible')],
            ]);
        }

        $order->forceFill([
            'vendor_id' => $vendor->id,
            'assignment_mode_snapshot' => 'manual',
            'status' => $this->statusAfterAssign($order),
            'assigned_at' => now(),
        ])->save();

        $this->recordEvent($order, 'assigned', 'admin', $adminId, [
            'vendor_id' => $vendor->id,
        ]);

        $this->earnings->accrueForOrder($order->fresh());

        $order = $order->fresh();
        $this->notifier->orderAssigned($order);

        return $order;
    }

    protected function statusAfterAssign(Order $order): string
    {
        $timing = $order->payment_timing_snapshot
            ?: (siteSettings()?->order_payment_mode ?: 'later');

        if ($timing === 'later' && $order->payment_status !== Order::PAYMENT_PAID
            && $order->payment_status !== Order::PAYMENT_COVERED_BY_PLAN) {
            return Order::STATUS_PENDING_VENDOR_CONFIRM;
        }

        return Order::STATUS_ASSIGNED;
    }

    protected function assertOpenAcceptMode(Order $order): void
    {
        $mode = orderAssignmentMode($order);

        if ($mode !== 'open') {
            throw ValidationException::withMessages([
                'assignment' => [__('general.order_open_accept_disabled')],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function recordEvent(?Order $order, string $type, ?string $actorType, ?int $actorId, array $payload = []): void
    {
        if (! $order) {
            return;
        }

        OrderEvent::query()->create([
            'order_id' => $order->id,
            'type' => $type,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'payload' => $payload,
        ]);
    }
}
