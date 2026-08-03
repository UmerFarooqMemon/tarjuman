<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiLocale;
use App\Http\Controllers\Controller;
use App\Models\AddOn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddOnsController extends Controller
{
    use ResolvesApiLocale;

    /**
     * Active add-ons for estimate forms.
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $this->apiLocale($request);

        $data = AddOn::cachedActive()
            ->map(function (AddOn $addOn) use ($locale) {
                return [
                    'id' => $addOn->id,
                    'name' => $addOn->displayName($locale) ?: $addOn->displayName('en'),
                    'pricing_mode' => $addOn->pricing_mode,
                    'amount' => number_format((float) $addOn->default_amount, 4, '.', ''),
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
