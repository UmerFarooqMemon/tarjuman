@extends('admin.layouts.app')

@section('css')
<link rel="stylesheet" href="{!! asset('assets/vendor/libs/intl-tel-input/css/intlTelInput.min.css') !!}" />
<link rel="stylesheet" href="{!! asset('assets/css/admin-intl-tel-input.css') !!}" />
@endsection

@section('content')
@php
    $crudLocales = crudLocales();
    $translationValues = [];
    foreach ($crudLocales as $locale) {
        $translationValues[$locale->code] = [
            'legal_name' => old("translations.{$locale->code}.legal_name", $vendor->translate($locale->code, false)?->legal_name),
            'business_name' => old("translations.{$locale->code}.business_name", $vendor->translate($locale->code, false)?->business_name),
            'address' => old("translations.{$locale->code}.address", $vendor->translate($locale->code, false)?->address),
        ];
    }
@endphp
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">{!! __('general.edit_vendor') !!}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.vendors.update', $vendor) }}" class="row" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('admin.vendors.partials.form-company', ['vendor' => $vendor, 'translationValues' => $translationValues, 'crudLocales' => $crudLocales])
                    @include('admin.vendors.partials.form-owner', ['owner' => $vendor->owner, 'requirePassword' => false])
                    <div class="mb-3 col-12">
                        <button type="submit" class="btn btn-primary">{!! __('general.save') !!}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-js')
<script>
  document.documentElement.setAttribute('data-iti-utils', @json(asset('assets/vendor/libs/intl-tel-input/js/utils.js')));
  document.documentElement.setAttribute('data-iti-invalid-msg', @json(__('general.invalid_phone_number')));
</script>
<script src="{!! asset('assets/vendor/libs/intl-tel-input/js/intlTelInput.min.js') !!}"></script>
<script src="{!! asset('assets/js/admin-vendor-phone.js') !!}"></script>
@endsection
