@php
    $crudLocales = $crudLocales ?? crudLocales();
    $platformCurrency = $platformCurrency ?? platformCurrency();
    $deliverySpeeds = $deliverySpeeds ?? collect();
    $addOns = $addOns ?? collect();
@endphp

<div class="modal fade" id="planFormModal">
    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
        <div class="modal-content p-0 p-md-5">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4">
                    <h3 class="mb-2" id="planFormModalLabel">{!! __('general.add_plan') !!}</h3>
                </div>
                <form method="POST" action="{{ route('admin.plans.store') }}" id="planForm">
                    @csrf
                    <input type="hidden" name="_method" id="plan_form_method" value="POST">
                    <input type="hidden" name="plan_id" id="plan_id" value="{{ old('plan_id') }}">
                    <input type="hidden" name="update_url" id="plan_update_url" value="{{ old('update_url') }}">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        @foreach ($crudLocales as $locale)
                            <div class="col-md-6">
                                <div class="card border mb-3">
                                    <div class="card-header">
                                        <strong>{{ $locale->native_name ?: $locale->displayName() }}</strong>
                                        <small class="text-muted">({{ strtoupper($locale->code) }})</small>
                                    </div>
                                    <div class="card-body">
                                        <label class="form-label" for="plan_name_{{ $locale->code }}">{!! __('general.name') !!} <span class="required-fl">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="plan_name_{{ $locale->code }}"
                                            name="translations[{{ $locale->code }}][name]"
                                            value="{{ old("translations.{$locale->code}.name") }}"
                                            required
                                            @if ($locale->isRtl()) dir="rtl" @endif
                                        >
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="plan_price_amount">{!! __('general.price') !!}</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" name="price_amount" id="plan_price_amount" class="form-control" value="{{ old('price_amount') }}" required>
                            <span class="input-group-text d-inline-flex align-items-center">
                                {!! currencyIconHtml($platformCurrency) !!}
                            </span>
                        </div>
                        <input type="hidden" name="currency" value="{{ $platformCurrency }}">
                    </div>
                    <input type="hidden" name="billing_period" value="monthly">
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="plan_page_quota">{!! __('general.platform_page_quota') !!}</label>
                            <input type="number" min="1" name="page_quota" id="plan_page_quota" class="form-control" value="{{ old('page_quota') }}" required>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="plan_word_quota">{!! __('general.platform_word_quota') !!}</label>
                            <input type="number" min="1" name="word_quota" id="plan_word_quota" class="form-control" value="{{ old('word_quota') }}" required>
                        </div>
                    </div>
                    <p class="text-muted small mb-3">{!! __('general.platform_dual_quota_hint') !!}</p>

                    <div class="mb-3">
                        <label class="form-label" for="plan_delivery_speed_id">{!! __('general.menu_delivery_speeds') !!} <small class="text-muted">({{ __('general.optional') }})</small></label>
                        <select class="form-select select2" name="delivery_speed_id" id="plan_delivery_speed_id" data-allow-clear="true" data-placeholder="—">
                            <option value="">—</option>
                            @foreach ($deliverySpeeds as $speed)
                                <option value="{{ $speed->id }}" @selected((string) old('delivery_speed_id') === (string) $speed->id)>
                                    {{ $speed->displayName() }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">{!! __('general.platform_plan_delivery_speed_hint') !!}</small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label d-block">{!! __('general.menu_add_ons') !!} <small class="text-muted">({{ __('general.optional') }})</small></label>
                        <div class="row g-2" id="plan_add_on_checkboxes">
                            @forelse ($addOns as $addOn)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input plan-add-on-checkbox"
                                            type="checkbox"
                                            name="add_on_ids[]"
                                            id="plan_add_on_{{ $addOn->id }}"
                                            value="{{ $addOn->id }}"
                                            @checked(collect(old('add_on_ids', []))->contains($addOn->id))
                                        >
                                        <label class="form-check-label" for="plan_add_on_{{ $addOn->id }}">
                                            {{ $addOn->displayName() }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12"><small class="text-muted">{!! __('general.no_records_found') !!}</small></div>
                            @endforelse
                        </div>
                        <small class="text-muted d-block mt-2">{!! __('general.platform_plan_add_ons_hint') !!}</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{!! __('general.cancel') !!}</button>
                        <button type="submit" class="btn btn-primary">{!! __('general.save') !!}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
