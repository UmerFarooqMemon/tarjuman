@extends('admin.layouts.app')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{!! __('general.administrators') !!}</h5>
        @can('administrators.create')
        <small class="text-muted float-end"><a href="{{ route('admin.administrators.create') }}" class="btn btn-primary">{!! __('general.create') !!}</a></small>
        @endcan
    </div>
    <div class="card-datatable text-nowrap">
      <table class="datatables-records table">
        <thead>
          <tr>
            <th>{!! __('general.name') !!}</th>
            <th>{!! __('general.email') !!}</th>
            <th>{!! __('general.role') !!}</th>
            <th>{!! __('general.status') !!}</th>
            <th>{!! __('general.actions') !!}</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($data as $key => $admin)
                <tr>
                    <td>{!! $admin->first_name !!} {!! $admin->last_name !!}</td>
                    <td>{!! $admin->email !!}</td>
                    <td>
                        @forelse ($admin->roles as $role)
                            <span class="badge bg-label-primary">{{ $role->name }}</span>
                        @empty
                            <span class="text-muted">—</span>
                        @endforelse
                    </td>
                    <td>
                        @if(!$admin->is_system_admin)
                        @can('administrators.edit')
                        <label class="switch switch-success changeStatus" data-status="{!! $admin->is_active !!}" data-id="{!! $admin->id !!}" style="font-size: 15px !important">
                            <input type="checkbox" name="status" class="switch-input" value="" {!! ($admin->is_active == 1) ? 'checked' : '' !!}>
                            <span class="switch-toggle-slider">
                                <span class="switch-on">
                                    <i class="ti ti-check"></i>
                                </span>
                                <span class="switch-off">
                                    <i class="ti ti-x"></i>
                                </span>
                            </span>
                        </label>
                        @else
                            @if ($admin->is_active)
                                <span class="badge bg-label-success">{!! __('general.active') !!}</span>
                            @else
                                <span class="badge bg-label-warning">{!! __('general.inactive') !!}</span>
                            @endif
                        @endcan
                        @else
                            @if ($admin->is_active)
                                <span class="badge bg-label-success">{!! __('general.active') !!}</span>
                            @else
                            <span class="badge bg-label-warning">{!! __('general.inactive') !!}</span>
                            @endif
                        @endif
                    </td>
                    <td>
                        <div class="d-inline-block">
                            @can('administrators.edit')
                            @if(auth('admin')->user()->is_system_admin || auth('admin')->id() == $admin->id || !$admin->is_system_admin)
                                <a href="{!! route('admin.administrators.edit', $admin->id) !!}" class="item-edit text-body"><i class="text-primary ti ti-pencil"></i></a>
                            @endif
                            @endcan

                            @can('administrators.delete')
                            @if(!$admin->is_system_admin && auth('admin')->id() != $admin->id)
                            <a href="javascript:;" onclick="deleteConfirmation({!! $admin->id !!})" class="text-danger"><i class="ti ti-trash"></i></a>
                            <form action="{!! URL::route('admin.administrators.destroy', $admin->id) !!}" method="POST" id="deleteForm{!! $admin->id !!}">
                                @csrf
                                @method('DELETE')
                            </form>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('footer-js')
<script>
    var table = $('.datatables-records').DataTable({
        language: {
            url: langUrl
        },
        pageLength: 50,
        order: [],
        "columnDefs": [{
                "orderable": false,
                "targets": [4]
            },
        ],
        scrollX: true
    });
    $(document).on('click', '.changeStatus', function(event) {
        var $row = $(this);
        var id = $row.attr('data-id');
        var status = $row.attr('data-status');
        status = (status == 1) ? 0 : 1;
        var toggleButton = $row.find('.switch-input');
        var originalStatus = (status == 1) ? 0 : 1;

        Swal.fire({
            title: "{!! __('general.are_you_sure') !!}",
            text: "{!! __('general.you_wont_be_able_to_revert_this') !!}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: "{!! __('general.yes_proceed') !!}",
            cancelButtonText: "{!! __('general.cancel') !!}",
            customClass: {
                confirmButton: 'btn btn-primary me-3',
                cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    url: "{!! route('admin.update-status') !!}",
                    type: "POST",
                    data: {'id': id, 'status': status, '_token': "{!! csrf_token() !!}"},
                    success: function (response) {
                        if (response.error == 0) {
                            $row.attr('data-status', status);
                            Swal.fire({
                                icon: 'success',
                                title: "{!! __('general.success') !!}",
                                text: response.message,
                                confirmButtonText: "{!! __('general.ok') !!}",
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            });
                        } else {
                            toggleButton.prop('checked', originalStatus == 1);
                            Swal.fire({
                                icon: 'error',
                                title: "{!! __('general.error') !!}",
                                text: response.message,
                                confirmButtonText: "{!! __('general.ok') !!}",
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            });
                        }
                    },
                    error: function() {
                        toggleButton.prop('checked', originalStatus == 1);
                        Swal.fire({
                            icon: 'error',
                            title: "{!! __('general.error') !!}",
                            text: "{!! __('general.something_went_wrong_please_try_again_later') !!}",
                            customClass: {
                                confirmButton: 'btn btn-success'
                            }
                        });
                    }
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                toggleButton.prop('checked', originalStatus == 1);

                Swal.fire({
                    title: "{!! __('general.cancelled') !!}",
                    text: "{!! __('general.your_data_is_safe') !!}",
                    confirmButtonText: "{!! __('general.ok') !!}",
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn btn-success'
                    }
                });
            }
        });
    });
</script>
@endsection
