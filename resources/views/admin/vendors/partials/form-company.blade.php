@php
    $crudLocales = $crudLocales ?? crudLocales();
    $translationValues = $translationValues ?? [];
@endphp

<div class="col-12">
    <h6 class="mb-3">{!! __('general.company_information') !!}</h6>
</div>

@foreach ($crudLocales as $locale)
<div class="col-md-6">
    <div class="card border mb-4">
        <div class="card-header py-2">
            <strong>{{ $locale->native_name ?: $locale->displayName() }}</strong>
            <small class="text-muted">({{ strtoupper($locale->code) }})</small>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label" for="legal_name_{{ $locale->code }}">{!! __('general.legal_name') !!} <span class="required-fl">*</span></label>
                <input
                    type="text"
                    class="form-control"
                    id="legal_name_{{ $locale->code }}"
                    name="translations[{{ $locale->code }}][legal_name]"
                    value="{{ old("translations.{$locale->code}.legal_name", $translationValues[$locale->code]['legal_name'] ?? '') }}"
                    required
                    @if($locale->isRtl()) dir="rtl" @endif>
            </div>
            <div class="mb-3">
                <label class="form-label" for="business_name_{{ $locale->code }}">{!! __('general.business_name') !!}</label>
                <input
                    type="text"
                    class="form-control"
                    id="business_name_{{ $locale->code }}"
                    name="translations[{{ $locale->code }}][business_name]"
                    value="{{ old("translations.{$locale->code}.business_name", $translationValues[$locale->code]['business_name'] ?? '') }}"
                    @if($locale->isRtl()) dir="rtl" @endif>
            </div>
            <div class="mb-0">
                <label class="form-label" for="address_{{ $locale->code }}">{!! __('general.vendor_address') !!}</label>
                <textarea
                    class="form-control"
                    id="address_{{ $locale->code }}"
                    name="translations[{{ $locale->code }}][address]"
                    rows="3"
                    @if($locale->isRtl()) dir="rtl" @endif>{{ old("translations.{$locale->code}.address", $translationValues[$locale->code]['address'] ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>
@endforeach

<div class="mb-3 col-md-6">
    <label class="form-label" for="trn">{!! __('general.trn') !!} <span class="required-fl">*</span></label>
    <input type="text" class="form-control" id="trn" name="trn" value="{{ old('trn', optional($vendor)->trn) }}" required>
</div>
<div class="mb-3 col-md-6">
    <label class="form-label" for="moj_registration_no">{!! __('general.moj_registration_no') !!} <span class="required-fl">*</span></label>
    <input type="text" class="form-control" id="moj_registration_no" name="moj_registration_no" value="{{ old('moj_registration_no', optional($vendor)->moj_registration_no) }}" required>
</div>
<div class="mb-3 col-md-6">
    <label class="form-label" for="trade_license_no">{!! __('general.trade_license') !!} <span class="required-fl">*</span></label>
    <input type="text" class="form-control" id="trade_license_no" name="trade_license_no" value="{{ old('trade_license_no', optional($vendor)->trade_license_no) }}" required>
</div>
<div class="mb-3 col-md-6">
    <label class="form-label" for="trade_license_expiry">{!! __('general.trade_license_expiry') !!}</label>
    <input type="date" class="form-control" id="trade_license_expiry" name="trade_license_expiry" value="{{ old('trade_license_expiry', optional($vendor)->trade_license_expiry?->format('Y-m-d')) }}">
</div>
<div class="mb-3 col-md-6">
    <label class="form-label" for="email">{!! __('general.company_email') !!} <span class="required-fl">*</span></label>
    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', optional($vendor)->email) }}" required>
</div>
<div class="mb-3 col-md-6">
    <label class="form-label" for="phone">{!! __('general.contact_number') !!}</label>
    <input type="text" class="form-control phone-mask" id="phone" name="phone" value="{{ old('phone', optional($vendor)->phone) }}">
</div>
<div class="mb-3 col-md-6">
    <label class="form-label" for="logo">{!! __('general.business_logo') !!}</label>
    <input type="file" class="form-control" id="logo" name="logo" accept="image/jpeg,image/jpg,image/png">
    @if (!empty($vendor?->logo) && file_exists(uploadsDir('vendors').$vendor->logo))
        <input type="hidden" name="previous_logo" value="{{ $vendor->logo }}">
        <div class="mt-2">
            <img src="{{ asset(uploadsDir('vendors').$vendor->logo) }}" alt="" height="80">
        </div>
    @endif
</div>
