<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\CmsCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsPageController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $page = CmsCache::publishedPage($slug);

        if ($page === null) {
            return response()->json([
                'message' => 'Page not found.',
            ], 404);
        }

        // Public consumers only need enabled sections (already filtered).
        $page['sections'] = array_values(array_map(function (array $section) {
            unset($section['is_enabled']);

            return $section;
        }, $page['sections']));

        return response()->json($page);
    }

    public function preview(Request $request, string $slug): JsonResponse
    {
        if (! $request->hasValidSignature()) {
            return response()->json([
                'message' => 'Invalid or expired preview token.',
            ], 403);
        }

        $page = CmsCache::previewPage($slug);

        if ($page === null) {
            return response()->json([
                'message' => 'Page not found.',
            ], 404);
        }

        return response()->json($page);
    }
}
