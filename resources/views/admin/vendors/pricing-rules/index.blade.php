@extends('admin.layouts.app')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-1">{!! __('general.pricing_rules') !!}</h5>
            <small class="text-muted">{{ $vendor->displayName() }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-label-secondary">{!! __('general.back') !!}</a>
            @can('vendors.edit')
            <a href="{{ route('admin.vendors.pricing-rules.create', $vendor) }}" class="btn btn-primary">{!! __('general.add_pricing_rule') !!}</a>
            @endcan
        </div>
    </div>
    <div class="card-body border-bottom">
        <p class="mb-0 text-muted small">{!! __('general.pricing_rules_help') !!}</p>
    </div>
    <div class="card-datatable text-nowrap">
        <table class="datatables-records table">
            <thead>
                <tr>
                    <th>{!! __('general.language_pair') !!}</th>
                    <th>{!! __('general.rule_name') !!}</th>
                    <th>{!! __('general.page_range') !!}</th>
                    <th>{!! __('general.billing_unit') !!}</th>
                    <th>{!! __('general.rate') !!}</th>
                    <th>{!! __('general.priority') !!}</th>
                    <th>{!! __('general.status') !!}</th>
                    <th>{!! __('general.actions') !!}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rules as $rule)
                    <tr>
                        <td>{{ $rule->languagePair?->label() ?? '—' }}</td>
                        <td>{{ $rule->name ?: '—' }}</td>
                        <td>{{ $rule->pageRangeLabel() }}</td>
                        <td>{{ __('general.billing_unit_'.$rule->billing_unit) }}</td>
                        <td>
                            <span class="d-inline-flex align-items-center gap-1">
                                {!! currencyIconHtml($rule->currency) !!}
                                <span>{{ number_format((float) $rule->rate_amount, 4) }} {{ $rule->currency }}</span>
                            </span>
                        </td>
                        <td>{{ $rule->priority }}</td>
                        <td>
                            @can('vendors.edit')
                            <label class="switch switch-success changeStatus" data-status="{{ $rule->is_active }}" data-id="{{ $rule->id }}" style="font-size: 15px !important">
                                <input type="checkbox" class="switch-input" {{ $rule->is_active ? 'checked' : '' }}>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="ti ti-check"></i></span>
                                    <span class="switch-off"><i class="ti ti-x"></i></span>
                                </span>
                            </label>
                            @else
                                @if ($rule->is_active)
                                    <span class="badge bg-label-success">{!! __('general.active') !!}</span>
                                @else
                                    <span class="badge bg-label-warning">{!! __('general.inactive') !!}</span>
                                @endif
                            @endcan
                        </td>
                        <td>
                            <div class="d-inline-block">
                                @can('vendors.edit')
                                <a href="{{ route('admin.vendors.pricing-rules.edit', [$vendor, $rule]) }}" class="text-body"><i class="text-primary ti ti-pencil"></i></a>
                                <a href="javascript:;" onclick="deleteConfirmation({{ $rule->id }})" class="text-danger"><i class="ti ti-trash"></i></a>
                                <form action="{{ route('admin.vendors.pricing-rules.destroy', [$vendor, $rule]) }}" method="POST" id="deleteForm{{ $rule->id }}">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">{{ __('general.no_pricing_rules_yet') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('footer-js')
<script>
    @if ($rules->isNotEmpty())
    $('.datatables-records').DataTable({
        language: { url: langUrl },
        pageLength: 50,
        order: [],
        columnDefs: [{ orderable: false, targets: [6, 7] }],
        scrollX: true
    });
    @endif

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
                    url: "{!! route('admin.vendors.pricing-rules.update-status', $vendor) !!}",
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
                        }
                    },
                    error: function () {
                        toggleButton.prop('checked', originalStatus == 1);
                    }
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                toggleButton.prop('checked', originalStatus == 1);
            }
        });
    });
</script>
@endsection
