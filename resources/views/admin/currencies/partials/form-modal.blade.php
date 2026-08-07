@php
    $crudLocales = $crudLocales ?? crudLocales();
@endphp

<div class="modal fade" id="currencyFormModal">
    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
        <div class="modal-content p-0 p-md-5">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4">
                    <h3 class="mb-2" id="currencyFormModalLabel">{!! __('general.create_currency') !!}</h3>
                </div>
                <form method="POST" action="{{ route('admin.currencies.store') }}" id="currencyForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="currency_form_method" value="POST">
                    <input type="hidden" name="currency_id" id="currency_id" value="{{ old('currency_id') }}">
                    <input type="hidden" name="update_url" id="currency_update_url" value="{{ old('update_url') }}">

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
                                    <div class="mb-0">
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
                        </div>
                        @endforeach

                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="code">{!! __('general.code') !!} <span class="required-fl">*</span></label>
                            <input type="text" class="form-control text-uppercase @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" maxlength="3" required placeholder="AED">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="decimals">{!! __('general.decimals') !!} <span class="required-fl">*</span></label>
                            <input type="number" class="form-control @error('decimals') is-invalid @enderror" id="decimals" name="decimals" min="0" max="6" value="{{ old('decimals', 2) }}" required>
                            @error('decimals')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="icon_file">{!! __('general.icon_file') !!}</label>
                            <input type="file" class="form-control @error('icon_file') is-invalid @enderror" id="icon_file" name="icon_file" accept=".svg,image/svg+xml">
                            @error('icon_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{!! __('general.icon_file_help') !!}</div>
                        </div>
                        <div class="mb-1 col-12" id="currency_preview_wrap" hidden>
                            <label class="form-label d-block">{!! __('general.preview') !!}</label>
                            <span class="currency-option" id="currency_preview"></span>
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
