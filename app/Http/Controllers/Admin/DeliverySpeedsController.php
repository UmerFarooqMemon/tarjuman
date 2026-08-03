<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreDeliverySpeedRequest;
use App\Http\Requests\Admin\UpdateDeliverySpeedRequest;
use App\Models\DeliverySpeed;
use Illuminate\Http\Request;

class DeliverySpeedsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->registerCrudPermissions('delivery_speeds');
        $this->middleware('permission:delivery_speeds.edit')->only(['changeStatus']);
    }

    public function index()
    {
        $deliverySpeeds = DeliverySpeed::with('translations')
            ->ordered()
            ->get();

        $crudLocales = crudLocales();

        return view('admin.delivery-speeds.index', compact('deliverySpeeds', 'crudLocales'));
    }

    public function store(StoreDeliverySpeedRequest $request)
    {
        $deliverySpeed = DeliverySpeed::create([
            'price_amount' => $request->price_amount,
            'min_hours' => $request->filled('min_hours') ? (int) $request->min_hours : null,
            'max_hours' => $request->filled('max_hours') ? (int) $request->max_hours : null,
            'sort_order' => (int) (DeliverySpeed::query()->max('sort_order') ?? 0) + 1,
            'is_active' => true,
        ]);

        $this->syncTranslations($deliverySpeed, $request->input('translations', []));

        return redirect()
            ->route('admin.delivery-speeds.index')
            ->with('success', __('general.delivery_speed_has_been_created_successfully'));
    }

    public function update(UpdateDeliverySpeedRequest $request, DeliverySpeed $deliverySpeed)
    {
        $deliverySpeed->update([
            'price_amount' => $request->price_amount,
            'min_hours' => $request->filled('min_hours') ? (int) $request->min_hours : null,
            'max_hours' => $request->filled('max_hours') ? (int) $request->max_hours : null,
        ]);

        $this->syncTranslations($deliverySpeed, $request->input('translations', []));

        return redirect()
            ->route('admin.delivery-speeds.index')
            ->with('success', __('general.delivery_speed_has_been_updated_successfully'));
    }

    public function destroy(DeliverySpeed $deliverySpeed)
    {
        $deliverySpeed->delete();

        return redirect()
            ->route('admin.delivery-speeds.index')
            ->with('success', __('general.delivery_speed_has_been_deleted_successfully'));
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

        $deliverySpeed = DeliverySpeed::find($request->id);

        if (! $deliverySpeed) {
            return response()->json([
                'error' => 1,
                'message' => __('general.something_went_wrong_please_try_again_later'),
                'data' => [],
            ]);
        }

        $deliverySpeed->update(['is_active' => (bool) $request->status]);

        return response()->json([
            'error' => 0,
            'message' => __('general.status_has_been_changed_successfully'),
            'data' => [],
        ]);
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    protected function syncTranslations(DeliverySpeed $deliverySpeed, array $translations): void
    {
        foreach (crudLocaleCodes() as $locale) {
            $deliverySpeed->translateOrNew($locale)->fill([
                'name' => data_get($translations, "{$locale}.name"),
                'duration_label' => data_get($translations, "{$locale}.duration_label"),
            ]);
        }

        $deliverySpeed->save();
    }
}
