@extends('admin.layouts.app')

@section('content')
<div class="row">
                <div class="col-xl">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{!! __('general.administrator_details') !!}</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label" for="admin-first-name">{!! __('general.first_name') !!}</label>
                                <div class="input-group input-group-merge">
                                    {!! $data->first_name !!}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="admin-last-name">{!! __('general.last_name') !!}</label>
                                <div class="input-group input-group-merge">
                                    {!! $data->last_name !!}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="admin-phone">{!! __('general.contact_number') !!}</label>
                                <div class="input-group input-group-merge">
                                    {!! $data->phone !!}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="admin-email">{!! __('general.email') !!}</label>
                                <div class="input-group input-group-merge">
                                    {!! $data->email !!}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="admin-image">{!! __('general.profile_picture') !!}</label>
                                <div class="input-group input-group-merge">
                                    @if ($data->image != '' && file_exists(uploadsDir('admin') . $data->image))
                                    <img src="{!! asset(uploadsDir('admin') . $data->image) !!}" height="150" width="150">
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="admin-status">{!! __('general.status') !!}</label>
                                <div class="input-group input-group-merge">
                                    {!! ($data->is_active > 0) ? __('general.active') : __('general.inactive') !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
@endsection
