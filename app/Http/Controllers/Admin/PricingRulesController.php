<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StorePricingRuleRequest;
use App\Http\Requests\Admin\UpdatePricingRuleRequest;
use App\Models\PricingRule;
use Illuminate\Http\Request;

class PricingRulesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->registerCrudPermissions('pricing_rules');
        $this->middleware('permission:pricing_rules.edit')->only(['changeStatus']);
    }

    public function index()
    {
        $rules = PricingRule::query()
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->get();

        return view('admin.pricing-rules.index', compact('rules'));
    }

    public function create()
    {
        return view('admin.pricing-rules.create');
    }

    public function store(StorePricingRuleRequest $request)
    {
        PricingRule::create([
            ...$this->rulePayload($request),
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.pricing-rules.index')
            ->with('success', __('general.pricing_rule_has_been_created_successfully'));
    }

    public function edit(PricingRule $pricingRule)
    {
        return view('admin.pricing-rules.edit', compact('pricingRule'));
    }

    public function update(UpdatePricingRuleRequest $request, PricingRule $pricingRule)
    {
        $pricingRule->update($this->rulePayload($request));

        return redirect()
            ->route('admin.pricing-rules.index')
            ->with('success', __('general.pricing_rule_has_been_updated_successfully'));
    }

    public function destroy(PricingRule $pricingRule)
    {
        $pricingRule->delete();

        return redirect()
            ->route('admin.pricing-rules.index')
            ->with('success', __('general.pricing_rule_has_been_deleted_successfully'));
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

        $rule = PricingRule::find($request->id);

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
    protected function rulePayload(StorePricingRuleRequest|UpdatePricingRuleRequest $request): array
    {
        return [
            'name' => $request->name,
            'min_pages' => $request->filled('min_pages') ? (int) $request->min_pages : null,
            'max_pages' => $request->filled('max_pages') ? (int) $request->max_pages : null,
            'billing_unit' => $request->billing_unit,
            'rate_amount' => $request->rate_amount,
            'currency' => $request->input('currency', platformCurrency()),
            'priority' => (int) $request->input('priority', 0),
        ];
    }
}
