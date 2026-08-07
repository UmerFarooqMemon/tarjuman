@php
    $crudLocales = $crudLocales ?? crudLocales();
@endphp

<div class="modal fade" id="addOnFormModal">
    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
        <div class="modal-content p-0 p-md-5">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4">
                    <h3 class="mb-2" id="addOnFormModalLabel">{!! __('general.create_add_on') !!}</h3>
                </div>
                <form method="POST" action="{{ route('admin.add-ons.store') }}" id="addOnForm">
                    @csrf
                    <input type="hidden" name="_method" id="add_on_form_method" value="POST">
                    <input type="hidden" name="add_on_id" id="add_on_id" value="{{ old('add_on_id') }}">
                    <input type="hidden" name="update_url" id="add_on_update_url" value="{{ old('update_url') }}">

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
                            </div>
                        </div>
                        @endforeach

                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="pricing_mode">{!! __('general.pricing_mode') !!} <span class="required-fl">*</span></label>
                            <select class="form-select @error('pricing_mode') is-invalid @enderror" id="pricing_mode" name="pricing_mode" required>
                                <option value="fixed" @selected(old('pricing_mode', 'fixed') === 'fixed')>{!! __('general.pricing_mode_fixed') !!}</option>
                                <option value="per_page" @selected(old('pricing_mode') === 'per_page')>{!! __('general.pricing_mode_per_page') !!}</option>
                            </select>
                            @error('pricing_mode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="default_amount">{!! __('general.default_amount') !!} <span class="required-fl">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('default_amount') is-invalid @enderror" id="default_amount" name="default_amount" step="0.0001" min="0" value="{{ old('default_amount', 0) }}" required>
                                <span class="input-group-text d-inline-flex align-items-center">
                                    {!! currencyIconHtml() !!}
                                </span>
                            </div>
                            @error('default_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
</div>
