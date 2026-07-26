@extends('admin.layouts.app')

@section('content')
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{!! __('general.vendor_details') !!}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{!! __('general.legal_name') !!} ({!! __('general.language_english') !!})</label>
                        <div>{{ $vendor->translate('en', false)?->legal_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{!! __('general.legal_name') !!} ({!! __('general.language_arabic') !!})</label>
                        <div dir="rtl">{{ $vendor->translate('ar', false)?->legal_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{!! __('general.trn') !!}</label>
                        <div>{{ $vendor->trn }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{!! __('general.moj_registration_no') !!}</label>
                        <div>{{ $vendor->moj_registration_no }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{!! __('general.trade_license') !!}</label>
                        <div>{{ $vendor->trade_license_no }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{!! __('general.company_email') !!}</label>
                        <div>{{ $vendor->email }}</div>
                    </div>
                    @if ($vendor->owner)
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{!! __('general.company_admin_user') !!}</label>
                        <div>{{ $vendor->owner->fullName() }} ({{ $vendor->owner->email }})</div>
                    </div>
                    @endif
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{!! __('general.status') !!}</label>
                        <div>{{ $vendor->is_active ? __('general.active') : __('general.inactive') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
