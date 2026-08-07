<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Noon Payments driver (company merchant).
 * Endpoint paths follow Noon Payments API conventions; refine with live credentials during integration testing.
 */
class NoonGateway implements PaymentGatewayInterface
{
    public function __construct(protected SiteSetting $settings) {}

    public function driverName(): string
    {
        return 'noon';
    }

    public function isConfigured(): bool
    {
        return (bool) $this->settings->noon_enabled
            && filled($this->settings->noon_business_id)
            && filled($this->settings->noon_app_key)
            && filled($this->settings->noon_app_secret);
    }

    public function isTestMode(): bool
    {
        return (bool) $this->settings->noon_test_mode;
    }

    public function createPayment(Order $order, string $returnUrl, string $callbackUrl): array
    {
        $auth = base64_encode($this->settings->noon_business_id.'.'.$this->settings->noon_app_key.':'.$this->settings->noon_app_secret);

        $response = Http::withHeaders([
            'Authorization' => 'Key_'.$auth,
        ])
            ->acceptJson()
            ->post($this->baseUrl().'/order', [
                'apiOperation' => 'INITIATE',
                'order' => [
                    'reference' => $order->order_id,
                    'amount' => $order->payableAmount(),
                    'currency' => $order->currency ?: 'AED',
                    'name' => 'Order '.$order->order_id,
                    'channel' => 'web',
                    'category' => 'pay',
                ],
                'configuration' => [
                    'returnUrl' => $returnUrl,
                ],
            ])
            ->throw()
            ->json();

        $result = $response['result'] ?? $response;

        return [
            'redirect_url' => $result['checkoutUrl'] ?? $result['checkout_url'] ?? null,
            'payment_link' => $result['checkoutUrl'] ?? $result['checkout_url'] ?? null,
            'tran_ref' => $result['order']['id'] ?? $result['orderId'] ?? null,
            'checkout_id' => $result['order']['id'] ?? $result['orderId'] ?? null,
            'raw' => is_array($response) ? $response : [],
        ];
    }

    public function verifyCallback(Request $request): array
    {
        $orderId = $request->input('orderId') ?? $request->input('order_id');
        if (! filled($orderId)) {
            return ['success' => false, 'raw' => $request->all()];
        }

        $auth = base64_encode($this->settings->noon_business_id.'.'.$this->settings->noon_app_key.':'.$this->settings->noon_app_secret);

        $response = Http::withHeaders([
            'Authorization' => 'Key_'.$auth,
        ])
            ->acceptJson()
            ->get($this->baseUrl().'/order/'.$orderId)
            ->throw()
            ->json();

        $status = data_get($response, 'result.order.status')
            ?? data_get($response, 'result.status')
            ?? data_get($response, 'status');

        return [
            'success' => in_array(strtoupper((string) $status), ['CAPTURED', 'PAID', 'SUCCESS'], true),
            'tran_ref' => (string) $orderId,
            'amount' => (float) (data_get($response, 'result.order.amount') ?? 0),
            'currency' => data_get($response, 'result.order.currency'),
            'raw' => is_array($response) ? $response : [],
        ];
    }

    protected function baseUrl(): string
    {
        return $this->isTestMode()
            ? 'https://api-test.noonpayments.com/payment/v1'
            : 'https://api.noonpayments.com/payment/v1';
    }
}
