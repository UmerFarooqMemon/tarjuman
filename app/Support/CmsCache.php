<?php

namespace App\Support;

use App\Cms\Support\ContentNormalizer;
use App\Models\CmsPage;
use Illuminate\Support\Facades\Cache;

class CmsCache
{
    public static function pageKey(string $slug): string
    {
        return 'cms.page.'.$slug;
    }

    /**
     * Published page payload for the public API.
     *
     * @return array{slug: string, title: string, preview_path: string, sections: list<array<string, mixed>>}|null
     */
    public static function publishedPage(string $slug): ?array
    {
        /** @var array{slug: string, title: string, preview_path: string, sections: list<array<string, mixed>>}|null $payload */
        $payload = Cache::rememberForever(self::pageKey($slug), function () use ($slug) {
            $page = CmsPage::query()
                ->where('slug', $slug)
                ->where('is_published', true)
                ->with(['sections' => fn ($q) => $q->where('is_enabled', true)->orderBy('sort_order')])
                ->first();

            if (! $page) {
                return null;
            }

            return self::serializePage($page, onlyEnabled: true);
        });

        return $payload;
    }

    /**
     * Uncached page payload (includes disabled sections) for admin preview bootstrap.
     *
     * @return array{slug: string, title: string, preview_path: string, sections: list<array<string, mixed>>}|null
     */
    public static function previewPage(string $slug): ?array
    {
        $page = CmsPage::query()
            ->where('slug', $slug)
            ->with(['sections' => fn ($q) => $q->orderBy('sort_order')])
            ->first();

        if (! $page) {
            return null;
        }

        return self::serializePage($page, onlyEnabled: false);
    }

    public static function flushPage(string $slug): void
    {
        Cache::forget(self::pageKey($slug));
    }

    /**
     * @return array{slug: string, title: string, preview_path: string, sections: list<array<string, mixed>>}
     */
    protected static function serializePage(CmsPage $page, bool $onlyEnabled): array
    {
        $sections = $page->sections
            ->when($onlyEnabled, fn ($c) => $c->where('is_enabled', true))
            ->values()
            ->map(function ($section) {
                $content = is_array($section->content) ? $section->content : [];

                return [
                    'id' => $section->id,
                    'type' => $section->type,
                    'sort_order' => $section->sort_order,
                    'is_enabled' => (bool) $section->is_enabled,
                    'content' => ContentNormalizer::absoluteUrls($content),
                ];
            })
            ->all();

        return [
            'slug' => $page->slug,
            'title' => $page->title,
            'preview_path' => $page->preview_path,
            'sections' => $sections,
        ];
    }
}
