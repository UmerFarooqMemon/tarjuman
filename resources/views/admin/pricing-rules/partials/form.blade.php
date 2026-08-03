@php
    $rule = $pricingRule ?? null;
@endphp

<div class="mb-3 col-md-6">
    <label class="form-label" for="name">{!! __('general.rule_name') !!}</label>
    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', optional($rule)->name) }}" placeholder="{{ __('general.pricing_rule_name_placeholder') }}">
    <small class="text-muted">{!! __('general.pricing_rule_applies_universally') !!}</small>
</div>
<div class="mb-3 col-md-3">
    <label class="form-label" for="min_pages">{!! __('general.min_pages') !!}</label>
    <input type="number" class="form-control" id="min_pages" name="min_pages" min="1" value="{{ old('min_pages', optional($rule)->min_pages) }}" placeholder="{{ __('general.optional') }}">
    <small class="text-muted">{!! __('general.min_pages_help') !!}</small>
</div>
<div class="mb-3 col-md-3">
    <label class="form-label" for="max_pages">{!! __('general.max_pages') !!}</label>
    <input type="number" class="form-control" id="max_pages" name="max_pages" min="1" value="{{ old('max_pages', optional($rule)->max_pages) }}" placeholder="{{ __('general.optional') }}">
    <small class="text-muted">{!! __('general.max_pages_help') !!}</small>
</div>
<div class="mb-3 col-md-3">
    <label class="form-label" for="billing_unit">{!! __('general.billing_unit') !!} <span class="required-fl">*</span></label>
    <select class="form-select" id="billing_unit" name="billing_unit" required>
        <option value="word" @selected(old('billing_unit', optional($rule)->billing_unit) === 'word')>{!! __('general.billing_unit_word') !!}</option>
        <option value="page" @selected(old('billing_unit', optional($rule)->billing_unit) === 'page')>{!! __('general.billing_unit_page') !!}</option>
    </select>
</div>
<div class="mb-3 col-md-3">
    <label class="form-label" for="rate_amount">{!! __('general.rate_amount') !!} <span class="required-fl">*</span></label>
    <div class="input-group">
        <input type="number" class="form-control" id="rate_amount" name="rate_amount" step="0.0001" min="0" value="{{ old('rate_amount', optional($rule)->rate_amount) }}" required>
        <span class="input-group-text d-inline-flex align-items-center">
            {!! currencyIconHtml(old('currency', optional($rule)->currency ?? platformCurrency())) !!}
        </span>
    </div>
    <input type="hidden" name="currency" value="{{ old('currency', optional($rule)->currency ?? platformCurrency()) }}">
</div>
<div class="mb-3 col-md-3">
    <label class="form-label" for="priority">{!! __('general.priority') !!}</label>
    <input type="number" class="form-control" id="priority" name="priority" min="0" value="{{ old('priority', optional($rule)->priority ?? 0) }}">
    <small class="text-muted">{!! __('general.priority_help') !!}</small>
</div>
