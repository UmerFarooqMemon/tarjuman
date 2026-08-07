<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TapGateway implements PaymentGatewayInterface
{
    public function __construct(protected SiteSetting $settings) {}

    public function driverName(): string
    {
        return 'tap';
    }

    public function isConfigured(): bool
    {
        return (bool) $this->settings->tap_enabled && filled($this->settings->tap_secret_key);
    }

    public function isTestMode(): bool
    {
        return (bool) $this->settings->tap_test_mode;
    }

    public function createPayment(Order $order, string $returnUrl, string $callbackUrl): array
    {
        $response = Http::withToken((string) $this->settings->tap_secret_key)
            ->acceptJson()
            ->post('https://api.tap.company/v2/charges', [
                'amount' => $order->payableAmount(),
                'currency' => $order->currency ?: 'AED',
                'threeDSecure' => true,
                'save_card' => false,
                'description' => 'Order '.$order->order_id,
                'reference' => ['transaction' => $order->order_id, 'order' => (string) $order->id],
                'receipt' => ['email' => true, 'sms' => false],
                'customer' => [
                    'first_name' => optional($order->customer)->first_name,
                    'last_name' => optional($order->customer)->last_name,
                    'email' => optional($order->customer)->email,
                    'phone' => ['number' => optional($order->customer)->phone],
                ],
                'source' => ['id' => 'src_all'],
                'redirect' => ['url' => $returnUrl],
                'post' => ['url' => $callbackUrl],
            ])
            ->throw()
            ->json();

        return [
            'redirect_url' => $response['transaction']['url'] ?? null,
            'payment_link' => $response['transaction']['url'] ?? null,
            'tran_ref' => $response['id'] ?? null,
            'checkout_id' => $response['id'] ?? null,
            'raw' => is_array($response) ? $response : [],
        ];
    }

    public function verifyCallback(Request $request): array
    {
        $chargeId = $request->input('id') ?? $request->input('tap_id') ?? $request->input('charge_id');
        if (! filled($chargeId)) {
            return ['success' => false, 'raw' => $request->all()];
        }

        $response = Http::withToken((string) $this->settings->tap_secret_key)
            ->acceptJson()
            ->get('https://api.tap.company/v2/charges/'.$chargeId)
            ->throw()
            ->json();

        return [
            'success' => ($response['status'] ?? null) === 'CAPTURED',
            'tran_ref' => $chargeId,
            'amount' => isset($response['amount']) ? (float) $response['amount'] : null,
            'currency' => $response['currency'] ?? null,
            'raw' => is_array($response) ? $response : [],
        ];
    }
}
