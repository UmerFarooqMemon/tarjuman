@php
    $crudLocales = $crudLocales ?? crudLocales();
@endphp

<div class="modal fade" id="deliverySpeedFormModal" tabindex="-1" aria-labelledby="deliverySpeedFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.delivery-speeds.store') }}" id="deliverySpeedForm">
                @csrf
                <input type="hidden" name="_method" id="delivery_speed_form_method" value="POST">
                <input type="hidden" name="delivery_speed_id" id="delivery_speed_id" value="{{ old('delivery_speed_id') }}">
                <input type="hidden" name="update_url" id="delivery_speed_update_url" value="{{ old('update_url') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="deliverySpeedFormModalLabel">{!! __('general.create_delivery_speed') !!}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('general.cancel') }}"></button>
                </div>

                <div class="modal-body">
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
                                <div class="card-header py-2">
                                    <strong>{{ $locale->native_name ?: $locale->displayName() }}</strong>
                                    <small class="text-muted">({{ strtoupper($locale->code) }})</small>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label" for="name_{{ $locale->code }}">{!! __('general.name') !!} <span class="required-fl">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control @error("translations.{$locale->code}.name") is-invalid @enderror"
                                            id="name_{{ $locale->code }}"
                                            name="translations[{{ $locale->code }}][name]"
                                            value="{{ old("translations.{$locale->code}.name") }}"
                                            required
                                            @if($locale->isRtl()) dir="rtl" @endif>
                                        @error("translations.{$locale->code}.name")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label" for="duration_label_{{ $locale->code }}">{!! __('general.duration') !!} <span class="required-fl">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control @error("translations.{$locale->code}.duration_label") is-invalid @enderror"
                                            id="duration_label_{{ $locale->code }}"
                                            name="translations[{{ $locale->code }}][duration_label]"
                                            value="{{ old("translations.{$locale->code}.duration_label") }}"
                                            placeholder="{{ __('general.duration_placeholder') }}"
                                            required
                                            @if($locale->isRtl()) dir="rtl" @endif>
                                        @error("translations.{$locale->code}.duration_label")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="price_amount">{!! __('general.price') !!} <span class="required-fl">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('price_amount') is-invalid @enderror" id="price_amount" name="price_amount" step="0.0001" min="0" value="{{ old('price_amount', 0) }}" required>
                                <span class="input-group-text d-inline-flex align-items-center">
                                    {!! currencyIconHtml() !!}
                                </span>
                            </div>
                            @error('price_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="min_hours">{!! __('general.min_hours') !!}</label>
                            <input type="number" class="form-control @error('min_hours') is-invalid @enderror" id="min_hours" name="min_hours" min="0" value="{{ old('min_hours') }}" placeholder="{{ __('general.optional') }}">
                            <small class="text-muted">{!! __('general.min_hours_help') !!}</small>
                            @error('min_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="max_hours">{!! __('general.max_hours') !!}</label>
                            <input type="number" class="form-control @error('max_hours') is-invalid @enderror" id="max_hours" name="max_hours" min="0" value="{{ old('max_hours') }}" placeholder="{{ __('general.optional') }}">
                            <small class="text-muted">{!! __('general.max_hours_help') !!}</small>
                            @error('max_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{!! __('general.cancel') !!}</button>
                    <button type="submit" class="btn btn-primary">{!! __('general.save') !!}</button>
                </div>
            </form>
        </div>
    </div>
</div>
