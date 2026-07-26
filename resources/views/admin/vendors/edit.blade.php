@extends('admin.layouts.app')

@section('content')
@php
    $translationValues = [
        'en' => [
            'legal_name' => old('translations.en.legal_name', $vendor->translate('en', false)?->legal_name),
            'business_name' => old('translations.en.business_name', $vendor->translate('en', false)?->business_name),
            'address' => old('translations.en.address', $vendor->translate('en', false)?->address),
        ],
        'ar' => [
            'legal_name' => old('translations.ar.legal_name', $vendor->translate('ar', false)?->legal_name),
            'business_name' => old('translations.ar.business_name', $vendor->translate('ar', false)?->business_name),
            'address' => old('translations.ar.address', $vendor->translate('ar', false)?->address),
        ],
    ];
@endphp
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{!! __('general.edit_vendor') !!}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.vendors.update', $vendor) }}" class="row" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('admin.vendors.partials.form-company', ['vendor' => $vendor, 'translationValues' => $translationValues])
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
