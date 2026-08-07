<?php

namespace App\Contracts\Payments;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function driverName(): string;

    /**
     * @return array{redirect_url?: string, payment_link?: string, tran_ref?: string, checkout_id?: string, raw?: array<string, mixed>}
     */
    public function createPayment(Order $order, string $returnUrl, string $callbackUrl): array;

    /**
     * @return array{success: bool, tran_ref?: string, amount?: float, currency?: string, raw?: array<string, mixed>}
     */
    public function verifyCallback(Request $request): array;

    public function isConfigured(): bool;

    public function isTestMode(): bool;
}
