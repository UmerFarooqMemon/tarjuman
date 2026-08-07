<?php

namespace App\Http\Controllers\Admin;

use App\Models\AddOn;
use App\Models\DeliverySpeed;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlansController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->registerCrudPermissions('plans');
        $this->middleware('permission:plans.edit')->only(['changeStatus']);
    }

    public function index()
    {
        $plans = Plan::query()
            ->with(['translations', 'deliverySpeed.translations', 'addOns.translations'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $platformCurrency = platformCurrency();
        $crudLocales = crudLocales();
        $deliverySpeeds = DeliverySpeed::with('translations')->ordered()->get();
        $addOns = AddOn::with('translations')->ordered()->get();

        return view('admin.plans.index', compact(
            'plans',
            'platformCurrency',
            'crudLocales',
            'deliverySpeeds',
            'addOns'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] = (int) (Plan::query()->max('sort_order') ?? 0) + 1;
        $data['is_active'] = true;

        $plan = Plan::query()->create($data);
        $this->syncTranslations($plan, $request->input('translations', []));
        $plan->addOns()->sync($request->input('add_on_ids', []));

        return redirect()
            ->route('admin.plans.index')
            ->with('success', __('general.plan_has_been_created_successfully'));
    }

    public function update(Request $request, Plan $plan)
    {
        $plan->update($this->validated($request));
        $this->syncTranslations($plan, $request->input('translations', []));
        $plan->addOns()->sync($request->input('add_on_ids', []));

        return redirect()
            ->route('admin.plans.index')
            ->with('success', __('general.plan_has_been_updated_successfully'));
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()
            ->route('admin.plans.index')
            ->with('success', __('general.plan_has_been_deleted_successfully'));
    }

    public function changeStatus(Request $request)
    {
        $plan = Plan::query()->find($request->id);
        if (! $plan) {
            return response()->json([
                'error' => 1,
                'message' => __('general.something_went_wrong_please_try_again_later'),
                'data' => [],
            ]);
        }

        $plan->update(['is_active' => (bool) $request->status]);

        return response()->json([
            'error' => 0,
            'message' => __('general.status_has_been_changed_successfully'),
            'data' => [],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        $planId = $request->route('plan')?->id;

        foreach (crudLocaleCodes() as $locale) {
            $localeRules["translations.{$locale}.name"] = [
                'required',
                'string',
                'max:190',
                Rule::unique('plan_translations', 'name')
                    ->where('locale', $locale)
                    ->ignore($planId, 'plan_id'),
            ];
        }

        $data = $request->validate(array_merge([
            'price_amount' => ['required', 'numeric', 'min:0'],
            'billing_period' => ['required', Rule::in(['monthly'])],
            'page_quota' => ['required', 'integer', 'min:1'],
            'word_quota' => ['required', 'integer', 'min:1'],
            'delivery_speed_id' => ['nullable', 'exists:delivery_speeds,id'],
            'add_on_ids' => ['nullable', 'array'],
            'add_on_ids.*' => ['integer', 'exists:add_ons,id'],
            'translations' => ['required', 'array'],
        ], $localeRules));

        return [
            'price_amount' => $data['price_amount'],
            'billing_period' => $data['billing_period'],
            'page_quota' => $data['page_quota'],
            'word_quota' => $data['word_quota'],
            'delivery_speed_id' => $data['delivery_speed_id'] ?? null,
            'currency' => platformCurrency(),
        ];
    }

    /**
     * @param  array<string, array{name?: string}>  $translations
     */
    protected function syncTranslations(Plan $plan, array $translations): void
    {
        foreach (crudLocaleCodes() as $locale) {
            $plan->translateOrNew($locale)->fill([
                'name' => data_get($translations, "{$locale}.name"),
            ]);
        }

        $plan->save();
    }
}
