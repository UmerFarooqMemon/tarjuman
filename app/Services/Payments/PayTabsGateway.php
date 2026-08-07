<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayTabsGateway implements PaymentGatewayInterface
{
    public function __construct(protected SiteSetting $settings) {}

    public function driverName(): string
    {
        return 'paytabs';
    }

    public function isConfigured(): bool
    {
        return (bool) $this->settings->paytabs_enabled
            && filled($this->settings->paytabs_profile_id)
            && filled($this->settings->paytabs_server_key);
    }

    public function isTestMode(): bool
    {
        return (bool) $this->settings->paytabs_test_mode;
    }

    public function createPayment(Order $order, string $returnUrl, string $callbackUrl): array
    {
        $payload = [
            'profile_id' => (int) $this->settings->paytabs_profile_id,
            'tran_type' => 'sale',
            'tran_class' => 'ecom',
            'cart_id' => $order->order_id,
            'cart_currency' => $order->currency ?: 'AED',
            'cart_amount' => $order->payableAmount(),
            'cart_description' => 'Order '.$order->order_id,
            'paypage_lang' => 'en',
            'customer_details' => [
                'name' => optional($order->customer)->fullName() ?: 'Customer',
                'email' => optional($order->customer)->email,
                'phone' => optional($order->customer)->phone,
            ],
            'return' => $returnUrl,
            'callback' => $callbackUrl,
        ];

        $response = $this->http()
            ->post($this->baseUrl().'/payment/request', $payload)
            ->throw()
            ->json();

        return [
            'redirect_url' => $response['redirect_url'] ?? null,
            'payment_link' => $response['redirect_url'] ?? null,
            'tran_ref' => $response['tran_ref'] ?? null,
            'checkout_id' => $response['tran_ref'] ?? null,
            'raw' => is_array($response) ? $response : [],
        ];
    }

    public function verifyCallback(Request $request): array
    {
        $tranRef = $request->input('tranRef') ?? $request->input('tran_ref');
        if (! filled($tranRef)) {
            return ['success' => false, 'raw' => $request->all()];
        }

        $response = $this->http()
            ->post($this->baseUrl().'/payment/query', [
                'profile_id' => (int) $this->settings->paytabs_profile_id,
                'tran_ref' => $tranRef,
            ])
            ->throw()
            ->json();

        $success = ($response['payment_result']['response_status'] ?? null) === 'A';

        return [
            'success' => $success,
            'tran_ref' => $tranRef,
            'amount' => isset($response['cart_amount']) ? (float) $response['cart_amount'] : null,
            'currency' => $response['cart_currency'] ?? null,
            'raw' => is_array($response) ? $response : [],
        ];
    }

    protected function baseUrl(): string
    {
        // UAE endpoints; adjust region later if needed.
        return $this->isTestMode()
            ? 'https://secure-egypt.paytabs.com'
            : 'https://secure.paytabs.com';
    }

    protected function http(): PendingRequest
    {
        $key = $this->settings->paytabs_server_key;
        if (! filled($key)) {
            throw new RuntimeException('PayTabs server key is missing.');
        }

        return Http::withHeaders([
            'authorization' => $key,
            'content-type' => 'application/json',
        ])->acceptJson();
    }
}
