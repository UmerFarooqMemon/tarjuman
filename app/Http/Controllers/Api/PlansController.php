<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiLocale;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlansController extends Controller
{
    use ResolvesApiLocale;

    public function index(Request $request): JsonResponse
    {
        $locale = $this->apiLocale($request);

        $plans = Plan::query()
            ->with(['translations', 'deliverySpeed.translations', 'addOns.translations'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Plan $plan) use ($locale) {
                return [
                    'id' => $plan->id,
                    'name' => [
                        'en' => (string) ($plan->{'name:en'} ?: ''),
                        'ar' => (string) ($plan->{'name:ar'} ?: ''),
                        'localized' => $plan->displayName($locale),
                    ],
                    'price_amount' => $plan->price_amount,
                    'currency' => $plan->currency ?: platformCurrency(),
                    'billing_period' => $plan->billing_period,
                    'page_quota' => $plan->page_quota,
                    'word_quota' => $plan->word_quota,
                    'delivery_speed' => $plan->deliverySpeed ? [
                        'id' => $plan->deliverySpeed->id,
                        'name' => $plan->deliverySpeed->displayName($locale),
                    ] : null,
                    'add_ons' => $plan->addOns->map(fn ($addOn) => [
                        'id' => $addOn->id,
                        'name' => $addOn->displayName($locale),
                    ])->values(),
                ];
            });

        return response()->json([
            'data' => $plans,
            'locale' => $locale,
        ]);
    }
}
