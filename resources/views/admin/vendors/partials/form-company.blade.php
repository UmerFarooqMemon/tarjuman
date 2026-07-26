@php
    $translationValues = $translationValues ?? ['en' => [], 'ar' => []];
@endphp

<div class="col-12">
    <h6 class="mb-3">{!! __('general.company_information') !!}</h6>
</div>

<div class="col-md-6">
    <div class="card border mb-4">
        <div class="card-header py-2">
            <strong>{!! __('general.language_english') !!}</strong>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label" for="legal_name_en">{!! __('general.legal_name') !!} <span class="required-fl">*</span></label>
                <input type="text" class="form-control" id="legal_name_en" name="translations[en][legal_name]" value="{{ old('translations.en.legal_name', $translationValues['en']['legal_name'] ?? '') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="business_name_en">{!! __('general.business_name') !!}</label>
                <input type="text" class="form-control" id="business_name_en" name="translations[en][business_name]" value="{{ old('translations.en.business_name', $translationValues['en']['business_name'] ?? '') }}">
            </div>
            <div class="mb-0">
                <label class="form-label" for="address_en">{!! __('general.vendor_address') !!}</label>
                <textarea class="form-control" id="address_en" name="translations[en][address]" rows="3">{{ old('translations.en.address', $translationValues['en']['address'] ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card border mb-4">
        <div class="card-header py-2">
            <strong>{!! __('general.language_arabic') !!}</strong>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label" for="legal_name_ar">{!! __('general.legal_name') !!} <span class="required-fl">*</span></label>
                <input type="text" class="form-control" id="legal_name_ar" name="translations[ar][legal_name]" value="{{ old('translations.ar.legal_name', $translationValues['ar']['legal_name'] ?? '') }}" required dir="rtl">
            </div>
            <div class="mb-3">
                <label class="form-label" for="business_name_ar">{!! __('general.business_name') !!}</label>
                <input type="text" class="form-control" id="business_name_ar" name="translations[ar][business_name]" value="{{ old('translations.ar.business_name', $translationValues['ar']['business_name'] ?? '') }}" dir="rtl">
            </div>
            <div class="mb-0">
                <label class="form-label" for="address_ar">{!! __('general.vendor_address') !!}</label>
                <textarea class="form-control" id="address_ar" name="translations[ar][address]" rows="3" dir="rtl">{{ old('translations.ar.address', $translationValues['ar']['address'] ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

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
