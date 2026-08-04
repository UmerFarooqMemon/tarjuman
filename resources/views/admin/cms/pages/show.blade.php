@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ route('admin.cms.pages.index') }}" class="btn btn-sm btn-label-secondary mb-2">
            <i class="ti ti-arrow-left me-1"></i>{!! __('general.cms_back_to_pages') !!}
        </a>
        <h4 class="mb-0">{{ $page->title }}</h4>
        <small class="text-muted"><code>{{ $page->slug }}</code> · {{ $page->preview_path }}</small>
    </div>
    <a
        href="{{ cms_frontend_url($page->preview_path) }}"
        target="_blank"
        rel="noopener"
        class="btn btn-outline-primary">
        <i class="ti ti-external-link me-1"></i>{!! __('general.cms_preview_page') !!}
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{!! __('general.cms_sections') !!}</h5>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{!! __('general.name') !!}</th>
                    <th>Type</th>
                    <th>{!! __('general.status') !!}</th>
                    <th>{!! __('general.actions') !!}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($page->sections as $section)
                    <tr>
                        <td>{{ $section->sort_order }}</td>
                        <td>{{ $section->schemaLabel() }}</td>
                        <td><code>{{ $section->type }}</code></td>
                        <td>
                            @can('cms_pages.edit')
                                <label
                                    class="switch switch-success changeStatus"
                                    data-status="{{ $section->is_enabled ? 1 : 0 }}"
                                    data-url="{{ route('admin.cms.pages.sections.toggle', [$page, $section]) }}"
                                    style="font-size: 15px !important">
                                    <input type="checkbox" class="switch-input" {{ $section->is_enabled ? 'checked' : '' }}>
                                    <span class="switch-toggle-slider">
                                        <span class="switch-on"><i class="ti ti-check"></i></span>
                                        <span class="switch-off"><i class="ti ti-x"></i></span>
                                    </span>
                                </label>
                            @else
                                @if ($section->is_enabled)
                                    <span class="badge bg-label-success">{!! __('general.active') !!}</span>
                                @else
                                    <span class="badge bg-label-warning">{!! __('general.inactive') !!}</span>
                                @endif
                            @endcan
                        </td>
                        <td>
                            @can('cms_pages.edit')
                                <a
                                    href="{{ route('admin.cms.pages.sections.edit', [$page, $section]) }}"
                                    class="btn btn-sm btn-icon btn-dark"
                                    title="{!! __('general.cms_edit_section') !!}">
                                    <i class="ti ti-pencil"></i>
                                </a>
                            @endcan
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
    $(document).on('click', '.changeStatus', function () {
        var $row = $(this);
        var status = $row.attr('data-status') == 1 ? 0 : 1;
        var toggleButton = $row.find('.switch-input');
        var originalStatus = status == 1 ? 0 : 1;
        var url = $row.attr('data-url');

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
                    url: url,
                    type: 'POST',
                    data: { status: status, _token: "{!! csrf_token() !!}" },
                    headers: { 'Accept': 'application/json' },
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
