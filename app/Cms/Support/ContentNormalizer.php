<?php

namespace App\Cms\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class ContentNormalizer
{
    /**
     * Merge uploaded files into the content tree using dotted upload keys.
     *
     * Upload input name: uploads[stats.0.icon] (file)
     *
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    public static function mergeUploads(array $content, Request $request, string $diskModule = 'cms'): array
    {
        $uploads = $request->allFiles()['uploads'] ?? [];

        if (! is_array($uploads) || $uploads === []) {
            return $content;
        }

        foreach ($uploads as $dottedPath => $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $safe = preg_replace('/[^a-zA-Z0-9._-]+/', '-', (string) $dottedPath) ?: 'file';
            $filename = 'cms-'.$safe.'-'.time().'.'.$file->getClientOriginalExtension();
            $file->move(uploadsDir($diskModule), $filename);
            data_set($content, (string) $dottedPath, uploadsDir($diskModule).$filename);
        }

        return $content;
    }

    /**
     * Recursively convert stored asset paths to absolute URLs for API/preview.
     *
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    public static function absoluteUrls(array $content): array
    {
        $out = [];

        foreach ($content as $key => $value) {
            if (is_array($value)) {
                $out[$key] = self::absoluteUrls($value);

                continue;
            }

            if (is_string($value) && self::looksLikeAssetKey((string) $key)) {
                $out[$key] = cms_asset_url($value) ?? $value;
            } else {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    protected static function looksLikeAssetKey(string $key): bool
    {
        return in_array($key, ['icon', 'side_image', 'image', 'src'], true)
            || str_ends_with($key, '_image')
            || str_ends_with($key, '_icon');
    }
}
