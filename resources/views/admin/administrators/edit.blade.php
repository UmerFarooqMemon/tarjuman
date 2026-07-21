@extends('admin.layouts.app')

@section('content')
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{!! __('general.edit_administrator') !!}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.administrators.update', $data->id) }}" class="row number-tab-steps wizard-circle" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-icon-default-fullname">{!! __('general.first_name') !!} <span class="required-fl">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-fullname2" class="input-group-text"
                                ><i class="ti ti-user"></i
                                ></span>
                            <input
                                type="text"
                                class="form-control"
                                id="basic-icon-default-fullname"
                                name="first_name" value="{{ old('first_name', $data->first_name) }}"
                                aria-describedby="basic-icon-default-fullname2" />
                        </div>
                    </div>
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-icon-default-lastname">{!! __('general.last_name') !!}</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-lastname2" class="input-group-text"
                                ><i class="ti ti-user"></i
                                ></span>
                            <input
                                type="text"
                                class="form-control"
                                id="basic-icon-default-lastname"
                                name="last_name" value="{{ old('last_name', $data->last_name) }}"
                                aria-describedby="basic-icon-default-lastname2" />
                        </div>
                    </div>
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-icon-default-phone">{!! __('general.contact_number') !!}</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-phone2" class="input-group-text"
                                ><i class="ti ti-phone"></i
                                ></span>
                            <input
                                type="text"
                                id="basic-icon-default-phone"
                                class="form-control phone-mask"
                                name="phone" value="{{ old('phone', $data->phone) }}"
                                aria-describedby="basic-icon-default-phone2" />
                        </div>
                    </div>
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-icon-default-email">{!! __('general.email') !!}</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-mail"></i></span>
                            <input
                                type="text"
                                id="basic-icon-default-email"
                                class="form-control"
                                name="email" value="{{  $data->email }}"
                                aria-describedby="basic-icon-default-email2" readonly />
                        </div>
                    </div>
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-icon-default-password">{!! __('general.password') !!}</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-key"></i></span>
                            <input
                                type="password"
                                id="basic-icon-default-password"
                                class="form-control"
                                name="password"
                                aria-describedby="basic-icon-default-password2" />
                        </div>
                    </div>
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-icon-default-password-confirm">{!! __('general.confirm_password') !!}</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-key"></i></span>
                            <input
                                type="password"
                                id="basic-icon-default-password-confirm"
                                class="form-control"
                                name="password_confirmation"
                                aria-describedby="basic-icon-default-password-confirm2" />
                        </div>
                    </div>
                    @if (!$data->is_system_admin)
                    <div class="mb-3 col-6">
                        <label class="form-label" for="admin_role">{!! __('general.role') !!}</label>
                        <select name="role" id="admin_role" class="form-select select2">
                            <option value="">{!! __('general.select_role') !!}</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role', $data->roles->first()?->name) === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div class="mb-3 col-6">
                        <label class="form-label">{!! __('general.role') !!}</label>
                        <input type="text" class="form-control" value="{{ $data->roles->first()?->name ?? config('admin_permissions.default_role') }}" readonly>
                    </div>
                    @endif
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-icon-default-image">{!! __('general.profile_picture') !!}</label>
                        <div class="input-group input-group-merge">
                            <input type="file" name="image" id="basic-icon-default-image" class="form-control">
                        </div>
                        @if ($data->image != '' && file_exists(uploadsDir('admin') . $data->image))
                        <input type="hidden" name="previous_image" value="{!! $data->image !!}" class="form-control">
                            <br>
                            <img src="{!! asset(uploadsDir('admin') . $data->image) !!}" height="150" width="150" class="h-auto rounded-circle">
                        @endif
                    </div>
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
@endsection
