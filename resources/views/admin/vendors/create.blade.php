@extends('admin.layouts.app')

@section('css')
<link rel="stylesheet" href="{!! asset('assets/vendor/libs/intl-tel-input/css/intlTelInput.min.css') !!}" />
<style>
  .iti { width: 100%; }
  .iti .form-control { width: 100%; }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{!! __('general.create_vendor') !!}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.vendors.store') }}" class="row" enctype="multipart/form-data">
                    @csrf
                    @include('admin.vendors.partials.form-company', ['vendor' => null])
                    @include('admin.vendors.partials.form-owner', ['requirePassword' => true])
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
