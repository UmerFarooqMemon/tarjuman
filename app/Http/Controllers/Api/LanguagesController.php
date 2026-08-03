<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiLocale;
use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguagesController extends Controller
{
    use ResolvesApiLocale;

    /**
     * Active languages for source / target selection.
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $this->apiLocale($request);

        $data = Language::cachedActive()
            ->map(function (Language $language) use ($locale) {
                $name = (string) ($language->{"name:{$locale}"}
                    ?: $language->native_name
                    ?: $language->{'name:en'}
                    ?: strtoupper((string) $language->code));

                return [
                    'id' => $language->id,
                    'code' => $language->code,
                    'native_name' => $language->native_name,
                    'direction' => $language->direction,
                    'name' => $name,
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
