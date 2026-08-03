<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiLocale;
use App\Http\Controllers\Controller;
use App\Models\DeliverySpeed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliverySpeedsController extends Controller
{
    use ResolvesApiLocale;

    /**
     * Active delivery speeds / modes for estimate forms.
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $this->apiLocale($request);

        $data = DeliverySpeed::cachedActive()
            ->map(function (DeliverySpeed $speed) use ($locale) {
                return [
                    'id' => $speed->id,
                    'name' => $speed->displayName($locale) ?: $speed->displayName('en'),
                    'duration_label' => $speed->displayDuration($locale) ?: $speed->displayDuration('en'),
                    'price_amount' => number_format((float) $speed->price_amount, 4, '.', ''),
                    'min_hours' => $speed->min_hours,
                    'max_hours' => $speed->max_hours,
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'data' => $data,
            'locale' => $locale,
            'currency' => $this->currencyPayload(),
        ]);
    }
}
