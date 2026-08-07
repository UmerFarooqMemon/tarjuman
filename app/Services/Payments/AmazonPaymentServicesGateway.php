<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Amazon Payment Services (PayFort) hosted checkout.
 * @see https://paymentservices.amazon.com/
 */
class AmazonPaymentServicesGateway implements PaymentGatewayInterface
{
    public function __construct(protected SiteSetting $settings) {}

    public function driverName(): string
    {
        return 'amazon_ps';
    }

    public function isConfigured(): bool
    {
        return (bool) $this->settings->amazon_ps_enabled
            && filled($this->settings->amazon_ps_merchant_identifier)
            && filled($this->settings->amazon_ps_access_code)
            && filled($this->settings->amazon_ps_sha_request)
            && filled($this->settings->amazon_ps_sha_response);
    }

    public function isTestMode(): bool
    {
        return (bool) $this->settings->amazon_ps_test_mode;
    }

    public function createPayment(Order $order, string $returnUrl, string $callbackUrl): array
    {
        $amount = (int) round($order->payableAmount() * 100);
        $payload = [
            'command' => 'AUTHORIZATION',
            'access_code' => $this->settings->amazon_ps_access_code,
            'merchant_identifier' => $this->settings->amazon_ps_merchant_identifier,
            'merchant_reference' => $order->order_id,
            'amount' => (string) $amount,
            'currency' => $order->currency ?: 'AED',
            'language' => 'en',
            'customer_email' => optional($order->customer)->email,
            'return_url' => $returnUrl,
        ];
        $payload['signature'] = $this->signature($payload, (string) $this->settings->amazon_ps_sha_request);

        // Hosted page: return signed payload for client POST, plus APS endpoint.
        return [
            'redirect_url' => $this->baseUrl().'/FortAPI/paymentPage',
            'payment_link' => $this->baseUrl().'/FortAPI/paymentPage',
            'tran_ref' => $order->order_id,
            'checkout_id' => $order->order_id,
            'raw' => $payload,
        ];
    }

    public function verifyCallback(Request $request): array
    {
        $data = $request->all();
        $signature = (string) ($data['signature'] ?? '');
        unset($data['signature']);

        $expected = $this->signature($data, (string) $this->settings->amazon_ps_sha_response);
        $status = (string) ($request->input('status') ?? '');
        $success = hash_equals($expected, $signature) && in_array($status, ['02', '14', '14000'], true);

        return [
            'success' => $success,
            'tran_ref' => $request->input('fort_id') ?? $request->input('merchant_reference'),
            'amount' => isset($data['amount']) ? ((float) $data['amount']) / 100 : null,
            'currency' => $request->input('currency'),
            'raw' => $request->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function signature(array $params, string $phrase): string
    {
        ksort($params);
        $joined = $phrase;
        foreach ($params as $key => $value) {
            if ($value === null || $value === '' || $key === 'signature') {
                continue;
            }
            $joined .= $key.'='.$value;
        }
        $joined .= $phrase;

        return hash('sha256', $joined);
    }

    protected function baseUrl(): string
    {
        return $this->isTestMode()
            ? 'https://sbcheckout.payfort.com'
            : 'https://checkout.payfort.com';
    }
}
