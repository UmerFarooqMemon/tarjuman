<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreAddOnRequest;
use App\Http\Requests\Admin\UpdateAddOnRequest;
use App\Models\AddOn;
use Illuminate\Http\Request;

class AddOnsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->registerCrudPermissions('add_ons');
        $this->middleware('permission:add_ons.edit')->only(['changeStatus']);
    }

    public function index()
    {
        $addOns = AddOn::with('translations')
            ->ordered()
            ->get();

        $crudLocales = crudLocales();

        return view('admin.add-ons.index', compact('addOns', 'crudLocales'));
    }

    public function store(StoreAddOnRequest $request)
    {
        $addOn = AddOn::create([
            'pricing_mode' => $request->pricing_mode,
            'default_amount' => $request->default_amount,
            'sort_order' => (int) (AddOn::query()->max('sort_order') ?? 0) + 1,
            'is_active' => true,
        ]);

        $this->syncTranslations($addOn, $request->input('translations', []));

        return redirect()
            ->route('admin.add-ons.index')
            ->with('success', __('general.add_on_has_been_created_successfully'));
    }

    public function update(UpdateAddOnRequest $request, AddOn $addOn)
    {
        $addOn->update([
            'pricing_mode' => $request->pricing_mode,
            'default_amount' => $request->default_amount,
        ]);

        $this->syncTranslations($addOn, $request->input('translations', []));

        return redirect()
            ->route('admin.add-ons.index')
            ->with('success', __('general.add_on_has_been_updated_successfully'));
    }

    public function destroy(AddOn $addOn)
    {
        $addOn->delete();

        return redirect()
            ->route('admin.add-ons.index')
            ->with('success', __('general.add_on_has_been_deleted_successfully'));
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

        $addOn = AddOn::find($request->id);

        if (! $addOn) {
            return response()->json([
                'error' => 1,
                'message' => __('general.something_went_wrong_please_try_again_later'),
                'data' => [],
            ]);
        }

        $addOn->update(['is_active' => (bool) $request->status]);

        return response()->json([
            'error' => 0,
            'message' => __('general.status_has_been_changed_successfully'),
            'data' => [],
        ]);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    protected function syncTranslations(AddOn $addOn, array $translations): void
    {
        foreach (crudLocaleCodes() as $locale) {
            $addOn->translateOrNew($locale)->fill([
                'name' => data_get($translations, "{$locale}.name"),
            ]);
        }

        $addOn->save();
    }
}
