<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreVendorPricingRuleRequest;
use App\Http\Requests\Admin\UpdateVendorPricingRuleRequest;
use App\Models\Language;
use App\Models\Vendor;
use App\Models\VendorLanguagePair;
use App\Models\VendorPricingRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class VendorPricingRulesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:vendors.view')->only(['index']);
        $this->middleware('permission:vendors.edit')->only([
            'create', 'store', 'edit', 'update', 'destroy', 'changeStatus',
        ]);
    }

    public function index(Vendor $vendor)
    {
        $rules = $vendor->pricingRules()
            ->with(['languagePair.sourceLanguage.translations', 'languagePair.targetLanguage.translations'])
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->get();

        return view('admin.vendors.pricing-rules.index', compact('vendor', 'rules'));
    }

    public function create(Vendor $vendor)
    {
        $pairs = $this->pairsForForm($vendor, activeOnly: true);

        return view('admin.vendors.pricing-rules.create', compact('vendor', 'pairs'));
    }

    public function store(StoreVendorPricingRuleRequest $request, Vendor $vendor)
    {
        $vendor->pricingRules()->create([
            ...$this->rulePayload($request),
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.vendors.pricing-rules.index', $vendor)
            ->with('success', __('general.pricing_rule_has_been_created_successfully'));
    }

    public function edit(Vendor $vendor, VendorPricingRule $pricingRule)
    {
        $this->ensureRuleBelongsToVendor($vendor, $pricingRule);

        $pairs = $this->pairsForForm($vendor, activeOnly: false);

        return view('admin.vendors.pricing-rules.edit', compact('vendor', 'pricingRule', 'pairs'));
    }

    public function update(UpdateVendorPricingRuleRequest $request, Vendor $vendor, VendorPricingRule $pricingRule)
    {
        $this->ensureRuleBelongsToVendor($vendor, $pricingRule);

        $pricingRule->update($this->rulePayload($request));

        return redirect()
            ->route('admin.vendors.pricing-rules.index', $vendor)
            ->with('success', __('general.pricing_rule_has_been_updated_successfully'));
    }

    public function destroy(Vendor $vendor, VendorPricingRule $pricingRule)
    {
        $this->ensureRuleBelongsToVendor($vendor, $pricingRule);

        $pricingRule->delete();

        return redirect()
            ->route('admin.vendors.pricing-rules.index', $vendor)
            ->with('success', __('general.pricing_rule_has_been_deleted_successfully'));
    }

    public function changeStatus(Request $request, Vendor $vendor)
    {
        if (! isset($request->id, $request->status)) {
            return response()->json([
                'error' => 1,
                'message' => __('general.something_went_wrong_please_try_again_later'),
                'data' => [],
            ]);
        }

        $rule = VendorPricingRule::where('vendor_id', $vendor->id)->find($request->id);

        if (! $rule) {
            return response()->json([
                'error' => 1,
                'message' => __('general.something_went_wrong_please_try_again_later'),
                'data' => [],
            ]);
        }

        $rule->update(['is_active' => (bool) $request->status]);

        return response()->json([
            'error' => 0,
            'message' => __('general.status_has_been_changed_successfully'),
            'data' => [],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rulePayload(StoreVendorPricingRuleRequest|UpdateVendorPricingRuleRequest $request): array
    {
        return [
            'vendor_language_pair_id' => $request->vendor_language_pair_id,
            'name' => $request->name,
            'min_pages' => $request->filled('min_pages') ? (int) $request->min_pages : null,
            'max_pages' => $request->filled('max_pages') ? (int) $request->max_pages : null,
            'billing_unit' => $request->billing_unit,
            'rate_amount' => $request->rate_amount,
            'currency' => $request->input('currency', platformCurrency()),
            'priority' => (int) $request->input('priority', 0),
        ];
    }

    protected function ensureRuleBelongsToVendor(Vendor $vendor, VendorPricingRule $pricingRule): void
    {
        abort_unless($pricingRule->vendor_id === $vendor->id, 404);
    }

    /**
     * @return Collection<int, VendorLanguagePair>
     */
    protected function pairsForForm(Vendor $vendor, bool $activeOnly): Collection
    {
        $languagesById = Language::cachedAll()->keyBy('id');

        return $vendor->cachedLanguagePairs($activeOnly)
            ->each(function (VendorLanguagePair $pair) use ($languagesById) {
                $pair->setRelation('sourceLanguage', $languagesById->get($pair->source_language_id));
                $pair->setRelation('targetLanguage', $languagesById->get($pair->target_language_id));
            });
    }
}
