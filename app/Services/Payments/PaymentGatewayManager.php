<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Models\SiteSetting;
use InvalidArgumentException;
use RuntimeException;

class PaymentGatewayManager
{
    /**
     * @var array<string, class-string<PaymentGatewayInterface>>
     */
    protected array $drivers = [
        'paytabs' => PayTabsGateway::class,
        'tap' => TapGateway::class,
        'noon' => NoonGateway::class,
        'amazon_ps' => AmazonPaymentServicesGateway::class,
    ];

    public function default(?SiteSetting $settings = null): PaymentGatewayInterface
    {
        $settings ??= siteSettings() ?? new SiteSetting;
        $driver = $settings->default_payment_gateway;

        if (! is_string($driver) || $driver === '') {
            throw new RuntimeException('No default payment gateway is configured.');
        }

        return $this->driver($driver, $settings);
    }

    public function driver(string $name, ?SiteSetting $settings = null): PaymentGatewayInterface
    {
        if (! isset($this->drivers[$name])) {
            throw new InvalidArgumentException("Unknown payment gateway [{$name}].");
        }

        $settings ??= siteSettings() ?? new SiteSetting;
        $gateway = app($this->drivers[$name], ['settings' => $settings]);

        if (! $gateway->isConfigured()) {
            throw new RuntimeException("Payment gateway [{$name}] is not enabled or missing credentials.");
        }

        return $gateway;
    }

    /**
     * @return list<string>
     */
    public function availableDrivers(): array
    {
        return array_keys($this->drivers);
    }
}
