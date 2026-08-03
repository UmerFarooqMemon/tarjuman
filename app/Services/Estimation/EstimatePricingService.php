<?php

namespace App\Services\Estimation;

use App\Models\DeliverySpeed;
use App\Services\Pricing\PricingCalculator;
use InvalidArgumentException;
use Throwable;

class EstimatePricingService
{
    public function __construct(
        protected PricingCalculator $pricingCalculator,
    ) {}

    /**
     * Build a single platform estimate (translation + add-ons + optional delivery speed).
     *
     * @return array{
     *     translation: array<string, mixed>,
     *     add_ons_total: string,
     *     delivery_speed: array<string, mixed>|null,
     *     delivery_speed_amount: string,
     *     total_amount: string,
     *     currency: string
     * }
     *
     * @throws InvalidArgumentException when no pricing rule covers the volume
     */
    public function quote(
        int $pageCount,
        int $wordCount,
        string $addOnsTotal,
        ?DeliverySpeed $deliverySpeed = null,
    ): array {
        try {
            $translation = $this->pricingCalculator->quote($pageCount, $wordCount);
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                'No pricing rule matches the given page count.',
                previous: $e
            );
        }

        $deliveryAmount = '0.0000';
        $deliveryPayload = null;

        if ($deliverySpeed) {
            $deliveryAmount = number_format((float) $deliverySpeed->price_amount, 4, '.', '');
            $deliveryPayload = [
                'id' => $deliverySpeed->id,
                'name' => $deliverySpeed->displayName(),
                'duration_label' => $deliverySpeed->displayDuration(),
                'min_hours' => $deliverySpeed->min_hours,
                'max_hours' => $deliverySpeed->max_hours,
                'amount' => $deliveryAmount,
            ];
        }

        $subtotal = bcadd($translation->totalAmount, $addOnsTotal, 4);
        $total = bcadd($subtotal, $deliveryAmount, 4);

        return [
            'translation' => $translation->toArray(),
            'add_ons_total' => $addOnsTotal,
            'delivery_speed' => $deliveryPayload,
            'delivery_speed_amount' => $deliveryAmount,
            'total_amount' => $total,
            'currency' => $translation->currency,
        ];
    }
}
