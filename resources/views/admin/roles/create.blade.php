@extends('admin.layouts.app')

@section('content')
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{!! __('general.add_new_role') !!}</h5>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-label-secondary">{!! __('general.back') !!}</a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.roles.store') }}" class="row">
                    @csrf
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="role_name">{!! __('general.role_name') !!} <span class="required-fl">*</span></label>
                        <input type="text" class="form-control" id="role_name" name="name" value="{{ old('name') }}" maxlength="125" required>
                    </div>

                    <div class="col-12 mb-2">
                        <h6 class="mb-0">{!! __('general.manage_permissions') !!}</h6>
                        <small class="text-muted">{!! __('general.permissions_bilingual_hint') !!}</small>
                    </div>

                    @include('admin.roles.partials.permissions', ['selected' => old('permissions', [])])

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
@include('admin.roles.partials.permissions-script')
@endsection
