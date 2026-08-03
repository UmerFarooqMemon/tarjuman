<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiLocale;
use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentTypesController extends Controller
{
    use ResolvesApiLocale;

    /**
     * Active document types for estimate forms.
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $this->apiLocale($request);

        $data = DocumentType::cachedActive()
            ->map(function (DocumentType $type) use ($locale) {
                return [
                    'id' => $type->id,
                    'name' => $type->displayName($locale) ?: $type->displayName('en'),
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
