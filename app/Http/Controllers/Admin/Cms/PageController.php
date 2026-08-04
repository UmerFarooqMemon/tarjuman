<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Cms\SchemaRegistry;
use App\Cms\Support\ContentNormalizer;
use App\Cms\Support\FieldRules;
use App\Http\Controllers\Admin\Controller;
use App\Models\CmsPage;
use App\Models\CmsSection;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:cms_pages.view')->only(['index', 'show']);
        $this->middleware('permission:cms_pages.edit')->only(['updateSection', 'toggleSection']);
    }

    public function index()
    {
        $pages = CmsPage::query()
            ->withCount('sections')
            ->orderBy('title')
            ->get();

        return view('admin.cms.pages.index', compact('pages'));
    }

    public function show(CmsPage $page)
    {
        $page->load(['sections' => fn ($q) => $q->orderBy('sort_order')]);

        return view('admin.cms.pages.show', compact('page'));
    }

    public function editSection(CmsPage $page, CmsSection $section)
    {
        abort_unless($section->cms_page_id === $page->id, 404);
        abort_unless(SchemaRegistry::has($section->type), 404);

        $schema = SchemaRegistry::get($section->type);
        $content = is_array($section->content) && $section->content !== []
            ? $section->content
            : $schema->defaults();

        $locale = in_array(request('locale'), ['en', 'ar'], true) ? request('locale') : 'en';
        $previewUrl = $this->buildPreviewUrl($page, $section->type, $locale);

        return view('admin.cms.sections.edit', compact(
            'page',
            'section',
            'schema',
            'content',
            'previewUrl',
            'locale'
        ));
    }

    public function updateSection(Request $request, CmsPage $page, CmsSection $section)
    {
        abort_unless($section->cms_page_id === $page->id, 404);
        abort_unless(SchemaRegistry::has($section->type), 404);

        $schema = SchemaRegistry::get($section->type);
        $rules = FieldRules::fromFields($schema->fields());
        $rules['is_enabled'] = ['sometimes', 'boolean'];

        $validated = $request->validate($rules);
        $content = ContentNormalizer::mergeUploads(
            $validated['content'] ?? [],
            $request,
            (string) config('cms.uploads_module', 'cms')
        );

        $section->update([
            'content' => $content,
            'is_enabled' => $request->boolean('is_enabled', $section->is_enabled),
        ]);

        $locale = in_array($request->input('preview_locale'), ['en', 'ar'], true)
            ? $request->input('preview_locale')
            : 'en';

        return redirect()
            ->route('admin.cms.pages.sections.edit', [
                'page' => $page,
                'section' => $section,
                'locale' => $locale,
                'saved' => 1,
            ])
            ->with('success', __('general.cms_section_updated_successfully'));
    }

    public function toggleSection(Request $request, CmsPage $page, CmsSection $section)
    {
        abort_unless($section->cms_page_id === $page->id, 404);

        $section->update([
            'is_enabled' => $request->boolean('status', ! $section->is_enabled),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 0,
                'message' => __('general.status_has_been_changed_successfully'),
                'data' => [],
            ]);
        }

        return back()->with('success', __('general.status_has_been_changed_successfully'));
    }

    /**
     * Full-page frontend URL for the admin iframe.
     * Live edits are pushed via postMessage; Save still writes to the DB for the public site.
     */
    protected function buildPreviewUrl(CmsPage $page, ?string $focusType, string $locale): string
    {
        $query = http_build_query(array_filter([
            'cms_preview' => 1,
            'locale' => $locale,
            'focus' => $focusType,
            'page' => $page->slug,
        ]));

        $url = cms_frontend_url($page->preview_path).'?'.$query;

        if ($focusType) {
            $url .= '#cms-'.$focusType;
        }

        return $url;
    }
}
