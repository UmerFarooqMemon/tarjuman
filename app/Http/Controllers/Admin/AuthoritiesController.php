<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreAuthorityRequest;
use App\Http\Requests\Admin\UpdateAuthorityRequest;
use App\Models\Authority;
use Illuminate\Http\Request;

class AuthoritiesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->registerCrudPermissions('authorities');
        $this->middleware('permission:authorities.edit')->only(['changeStatus']);
    }

    public function index()
    {
        $authorities = Authority::with('translations')
            ->ordered()
            ->get();

        $crudLocales = crudLocales();

        return view('admin.authorities.index', compact('authorities', 'crudLocales'));
    }

    public function store(StoreAuthorityRequest $request)
    {
        $authority = Authority::create([
            'sort_order' => (int) (Authority::query()->max('sort_order') ?? 0) + 1,
            'is_active' => true,
        ]);

        $this->syncTranslations($authority, $request->input('translations', []));

        return redirect()
            ->route('admin.authorities.index')
            ->with('success', __('general.authority_has_been_created_successfully'));
    }

    public function update(UpdateAuthorityRequest $request, Authority $authority)
    {
        $this->syncTranslations($authority, $request->input('translations', []));

        return redirect()
            ->route('admin.authorities.index')
            ->with('success', __('general.authority_has_been_updated_successfully'));
    }

    public function destroy(Authority $authority)
    {
        $authority->delete();

        return redirect()
            ->route('admin.authorities.index')
            ->with('success', __('general.authority_has_been_deleted_successfully'));
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

        $authority = Authority::find($request->id);

        if (! $authority) {
            return response()->json([
                'error' => 1,
                'message' => __('general.something_went_wrong_please_try_again_later'),
                'data' => [],
            ]);
        }

        $authority->update(['is_active' => (bool) $request->status]);

        return response()->json([
            'error' => 0,
            'message' => __('general.status_has_been_changed_successfully'),
            'data' => [],
        ]);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    protected function syncTranslations(Authority $authority, array $translations): void
    {
        foreach (crudLocaleCodes() as $locale) {
            $authority->translateOrNew($locale)->fill([
                'name' => data_get($translations, "{$locale}.name"),
            ]);
        }

        $authority->save();
    }
}
