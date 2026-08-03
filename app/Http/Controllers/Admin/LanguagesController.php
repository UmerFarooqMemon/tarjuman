<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreLanguageRequest;
use App\Http\Requests\Admin\UpdateLanguageRequest;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguagesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->registerCrudPermissions('languages');
        $this->middleware('permission:languages.edit')->only(['changeStatus']);
    }

    public function index()
    {
        $languages = Language::with('translations')
            ->ordered()
            ->get();

        return view('admin.languages.index', compact('languages'));
    }

    public function store(StoreLanguageRequest $request)
    {
        Language::create([
            'code' => strtolower($request->code),
            'native_name' => $request->native_name,
            'direction' => $request->direction,
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => true,
            'is_crud_locale' => in_array(strtolower($request->code), ['en', 'ar'], true),
        ]);

        return redirect()
            ->route('admin.languages.index')
            ->with('success', __('general.language_has_been_created_successfully'));
    }

    public function update(UpdateLanguageRequest $request, Language $language)
    {
        $payload = [
            'native_name' => $request->native_name,
            'direction' => $request->direction,
            'sort_order' => (int) $request->input('sort_order', 0),
        ];

        if (! $language->hasLockedCode()) {
            $payload['code'] = strtolower($request->code);
        }

        $language->update($payload);

        return redirect()
            ->route('admin.languages.index')
            ->with('success', __('general.language_has_been_updated_successfully'));
    }

    public function destroy(Language $language)
    {
        if ($language->hasLockedCode()) {
            return redirect()
                ->route('admin.languages.index')
                ->with('error', __('general.language_system_locale_cannot_delete'));
        }

        $language->delete();

        return redirect()
            ->route('admin.languages.index')
            ->with('success', __('general.language_has_been_deleted_successfully'));
    }

    public function changeStatus(Request $request)
    {
        if (! isset($request->id, $request->status)) {
            return response()->json([
                'error' => 1,
                'message' => __('general.something_went_wrong_please_try_again_later'),
                'data' => [],
            ]);
        }

        $language = Language::find($request->id);

        if (! $language) {
            return response()->json([
                'error' => 1,
                'message' => __('general.something_went_wrong_please_try_again_later'),
                'data' => [],
            ]);
        }

        $language->update(['is_active' => (bool) $request->status]);

        return response()->json([
            'error' => 0,
            'message' => __('general.status_has_been_changed_successfully'),
            'data' => [],
        ]);
    }
}
