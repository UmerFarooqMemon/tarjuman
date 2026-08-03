<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiLocale;
use App\Http\Controllers\Controller;
use App\Models\Authority;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthoritiesController extends Controller
{
    use ResolvesApiLocale;

    /**
     * Active authorities for website forms.
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $this->apiLocale($request);

        $data = Authority::cachedActive()
            ->map(function (Authority $authority) use ($locale) {
                return [
                    'id' => $authority->id,
                    'name' => $authority->displayName($locale) ?: $authority->displayName('en'),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'data' => $data,
            'locale' => $locale,
        ]);
    }
}
