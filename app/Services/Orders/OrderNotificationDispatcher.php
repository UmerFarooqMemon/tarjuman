<?php

namespace App\Services\Orders;

use App\Models\Admin;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorUser;
use App\Notifications\OrderAlertNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class OrderNotificationDispatcher
{
    public function orderCreated(Order $order): void
    {
        $this->notifyAdmins($order, OrderAlertNotification::EVENT_CREATED);

        if (normalizeAssignmentMode($order->assignment_mode_snapshot) === 'open'
            && $order->status === Order::STATUS_OPEN
            && ! $order->vendor_id) {
            $this->notifyActiveVendorUsers($order, OrderAlertNotification::EVENT_CREATED);
        }
    }

    public function orderAccepted(Order $order): void
    {
        $this->notifyAdmins($order, OrderAlertNotification::EVENT_ACCEPTED);
    }

    public function orderAssigned(Order $order): void
    {
        $this->notifyAdmins($order, OrderAlertNotification::EVENT_ASSIGNED);
        $this->notifyVendorUsersForOrder($order, OrderAlertNotification::EVENT_ASSIGNED);
    }

    public function paymentLinkReady(Order $order): void
    {
        $this->notifyAdmins($order, OrderAlertNotification::EVENT_PAYMENT_LINK);
        $this->notifyCustomer($order, OrderAlertNotification::EVENT_PAYMENT_LINK);
    }

    public function orderPaid(Order $order): void
    {
        $this->notifyAdmins($order, OrderAlertNotification::EVENT_PAID);
        $this->notifyVendorUsersForOrder($order, OrderAlertNotification::EVENT_PAID);
    }

    public function orderCompleted(Order $order): void
    {
        $this->notifyAdmins($order, OrderAlertNotification::EVENT_COMPLETED);
        $this->notifyCustomer($order, OrderAlertNotification::EVENT_COMPLETED);
    }

    protected function notifyAdmins(Order $order, string $event): void
    {
        $admins = Admin::query()
            ->where('is_active', true)
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new OrderAlertNotification($order, $event));
    }

    protected function notifyActiveVendorUsers(Order $order, string $event): void
    {
        $users = VendorUser::query()
            ->where('is_active', true)
            ->whereHas('vendor', fn ($q) => $q->where('is_active', true))
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new OrderAlertNotification($order, $event));
    }

    protected function notifyVendorUsersForOrder(Order $order, string $event): void
    {
        if (! $order->vendor_id) {
            return;
        }

        /** @var Collection<int, VendorUser> $users */
        $users = VendorUser::query()
            ->where('vendor_id', $order->vendor_id)
            ->where('is_active', true)
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new OrderAlertNotification($order, $event));
    }

    protected function notifyCustomer(Order $order, string $event): void
    {
        $customer = $order->customer;
        if (! $customer instanceof User || ! filled($customer->email)) {
            return;
        }

        $customer->notify(new OrderAlertNotification($order, $event));
    }
}
