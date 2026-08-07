<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\VendorEarning;

class VendorEarningService
{
    public function accrueForOrder(Order $order): ?VendorEarning
    {
        if (! $order->vendor_id) {
            return null;
        }

        if (! in_array($order->payment_status, [Order::PAYMENT_PAID, Order::PAYMENT_COVERED_BY_PLAN], true)) {
            return null;
        }

        $breakdown = orderFeeBreakdown($order->payableAmount());

        return VendorEarning::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'vendor_id' => $order->vendor_id,
                'amount' => $breakdown['total'],
                'platform_fee' => $breakdown['platform_fee'],
                'vendor_amount' => $breakdown['vendor_amount'],
                'currency' => $order->currency ?: 'AED',
                'status' => 'pending',
            ]
        );
    }
}
