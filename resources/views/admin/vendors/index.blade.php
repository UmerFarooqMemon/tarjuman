@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{!! __('general.menu_vendors') !!}</h5>
        @can('vendors.create')
        <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary">{!! __('general.add_vendor') !!}</a>
        @endcan
    </div>
    <div class="card-datatable text-nowrap">
        <table class="datatables-records table">
            <thead>
                <tr>
                    <th>{!! __('general.legal_name') !!}</th>
                    <th>{!! __('general.trn') !!}</th>
                    <th>{!! __('general.company_email') !!}</th>
                    <th>{!! __('general.company_admin_user') !!}</th>
                    <th>{!! __('general.status') !!}</th>
                    <th>{!! __('general.actions') !!}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vendors as $vendor)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if (!empty($vendor->logo) && file_exists(uploadsDir('vendors').$vendor->logo))
                                    <div class="avatar avatar-sm">
                                        <img src="{{ asset(uploadsDir('vendors').$vendor->logo) }}" alt="{{ $vendor->displayName() }}" class="rounded">
                                    </div>
                                @else
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded bg-label-secondary">
                                            {{ strtoupper(mb_substr($vendor->displayName() ?: 'V', 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                                <span>{{ $vendor->displayName() }}</span>
                            </div>
                        </td>
                        <td>{{ $vendor->trn }}</td>
                        <td>{{ $vendor->email }}</td>
                        <td>
                            @if ($vendor->owner)
                                <div>{{ $vendor->owner->fullName() }}</div>
                                <small class="text-muted">{{ $vendor->owner->email }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @can('vendors.edit')
                            <label class="switch switch-success changeStatus" data-status="{{ $vendor->is_active }}" data-id="{{ $vendor->id }}" style="font-size: 15px !important">
                                <input type="checkbox" class="switch-input" {{ $vendor->is_active ? 'checked' : '' }}>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="ti ti-check"></i></span>
                                    <span class="switch-off"><i class="ti ti-x"></i></span>
                                </span>
                            </label>
                            @else
                                @if ($vendor->is_active)
                                    <span class="badge bg-label-success">{!! __('general.active') !!}</span>
                                @else
                                    <span class="badge bg-label-warning">{!! __('general.inactive') !!}</span>
                                @endif
                            @endcan
                        </td>
                        <td>
                            <div class="d-inline-block">
                                @can('vendors.edit')
                                <a href="{{ route('admin.vendors.edit', $vendor) }}" class="text-body"><i class="text-primary ti ti-pencil"></i></a>
                                @endcan
                                @can('vendors.delete')
                                <a href="javascript:;" onclick="deleteConfirmation({{ $vendor->id }})" class="text-danger"><i class="ti ti-trash"></i></a>
                                <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST" id="deleteForm{{ $vendor->id }}">
                                    @csrf
                                    @method('DELETE')
                                </form>
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
        language: { url: langUrl },
        pageLength: 50,
        order: [],
        columnDefs: [{ orderable: false, targets: [4, 5] }],
        scrollX: true
    });

    $(document).on('click', '.changeStatus', function () {
        var $row = $(this);
        var id = $row.attr('data-id');
        var status = $row.attr('data-status') == 1 ? 0 : 1;
        var toggleButton = $row.find('.switch-input');
        var originalStatus = status == 1 ? 0 : 1;

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
                    url: "{!! route('admin.vendors.update-status') !!}",
                    type: 'POST',
                    data: { id: id, status: status, _token: "{!! csrf_token() !!}" },
                    success: function (response) {
                        if (response.error == 0) {
                            $row.attr('data-status', status);
                            Swal.fire({
                                icon: 'success',
                                title: "{!! __('general.success') !!}",
                                text: response.message,
                                confirmButtonText: "{!! __('general.ok') !!}",
                                customClass: { confirmButton: 'btn btn-success' }
                            });
                        } else {
                            toggleButton.prop('checked', originalStatus == 1);
                            Swal.fire({
                                icon: 'error',
                                title: "{!! __('general.error') !!}",
                                text: response.message,
                                confirmButtonText: "{!! __('general.ok') !!}",
                                customClass: { confirmButton: 'btn btn-success' }
                            });
                        }
                    },
                    error: function () {
                        toggleButton.prop('checked', originalStatus == 1);
                        Swal.fire({
                            icon: 'error',
                            title: "{!! __('general.error') !!}",
                            text: "{!! __('general.something_went_wrong_please_try_again_later') !!}",
                            customClass: { confirmButton: 'btn btn-success' }
                        });
                    }
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                toggleButton.prop('checked', originalStatus == 1);
            }
        });
    });
</script>
@endsection
