@extends('admin.layouts.app')

@section('content')
<div class="row">
                <div class="col-xl">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"> Administrator's Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label" for="basic-icon-default-fullname">First Name</label>
                                <div class="input-group input-group-merge">
                                    {!! $data->first_name !!}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="basic-icon-default-phone">Last Name</label>
                                <div class="input-group input-group-merge">
                                    {!! $data->last_name !!}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="basic-icon-default-email">Contact Number</label>
                                <div class="input-group input-group-merge">
                                    {!! $data->phone !!}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="basic-icon-default-email">Email</label>
                                <div class="input-group input-group-merge">
                                    {!! $data->email !!}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="basic-icon-default-email">Profile/Image</label>
                                <div class="input-group input-group-merge">
                                    @if ($data->image != '' && file_exists(uploadsDir('admin') . $data->image))
                                    <img src="{!! asset(uploadsDir('admin') . $data->image) !!}" height="150" width="150">
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="basic-icon-default-email">Status</label>
                                <div class="input-group input-group-merge">
                                    {!! ($data->is_active > 0) ? 'Active' : 'Inactive' !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
@endsection