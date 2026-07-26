@extends('admin.layouts.app')

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
