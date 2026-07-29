@extends('admin.layouts.app')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-1">{!! __('general.language_pairs') !!}</h5>
            <small class="text-muted">{{ $vendor->displayName() }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-label-secondary">{!! __('general.back') !!}</a>
            @can('vendors.edit')
            <button
                type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#languagePairsCreateModal">
                {!! __('general.add_language_pairs') !!}
            </button>
            @endcan
        </div>
    </div>
    <div class="card-datatable text-nowrap">
        <table class="datatables-records table">
            <thead>
                <tr>
                    <th>{!! __('general.source_language') !!}</th>
                    <th>{!! __('general.target_language') !!}</th>
                    <th>{!! __('general.pricing_rules') !!}</th>
                    <th>{!! __('general.status') !!}</th>
                    <th>{!! __('general.actions') !!}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pairs as $pair)
                    <tr>
                        <td>{{ $pair->sourceLanguage?->displayName() }} <small class="text-muted">({{ strtoupper($pair->sourceLanguage?->code) }})</small></td>
                        <td>{{ $pair->targetLanguage?->displayName() }} <small class="text-muted">({{ strtoupper($pair->targetLanguage?->code) }})</small></td>
                        <td>{{ $pair->pricingRules->count() }}</td>
                        <td>
                            @can('vendors.edit')
                            <label class="switch switch-success changeStatus" data-status="{{ $pair->is_active }}" data-id="{{ $pair->id }}" style="font-size: 15px !important">
                                <input type="checkbox" class="switch-input" {{ $pair->is_active ? 'checked' : '' }}>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="ti ti-check"></i></span>
                                    <span class="switch-off"><i class="ti ti-x"></i></span>
                                </span>
                            </label>
                            @else
                                @if ($pair->is_active)
                                    <span class="badge bg-label-success">{!! __('general.active') !!}</span>
                                @else
                                    <span class="badge bg-label-warning">{!! __('general.inactive') !!}</span>
                                @endif
                            @endcan
                        </td>
                        <td>
                            <div class="d-inline-block">
                                @can('vendors.edit')
                                <a
                                    href="javascript:;"
                                    class="text-body js-edit-language-pair"
                                    data-bs-toggle="modal"
                                    data-bs-target="#languagePairEditModal"
                                    data-id="{{ $pair->id }}"
                                    data-source-language-id="{{ $pair->source_language_id }}"
                                    data-target-language-id="{{ $pair->target_language_id }}"
                                    data-update-url="{{ route('admin.vendors.language-pairs.update', [$vendor, $pair]) }}">
                                    <i class="text-primary ti ti-pencil"></i>
                                </a>
                                <a href="javascript:;" onclick="deleteConfirmation({{ $pair->id }})" class="text-danger"><i class="ti ti-trash"></i></a>
                                <form action="{{ route('admin.vendors.language-pairs.destroy', [$vendor, $pair]) }}" method="POST" id="deleteForm{{ $pair->id }}">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">{{ __('general.no_language_pairs_yet') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('admin.vendors.language-pairs.partials.form-modals', [
    'vendor' => $vendor,
    'languages' => $languages,
])
@endsection

@section('footer-js')
<script>
    @if ($pairs->isNotEmpty())
    $('.datatables-records').DataTable({
        language: { url: langUrl },
        pageLength: 50,
        order: [],
        columnDefs: [{ orderable: false, targets: [3, 4] }],
        scrollX: true
    });
    @endif

    (function ($) {
        var existingPairKeys = @json($existingPairKeys ?? []);
        var duplicateMsg = @json(__('general.language_pair_already_exists'));
        var duplicateInFormMsg = @json(__('general.language_pair_duplicate_in_form'));
        var sameLanguageMsg = @json(__('general.source_and_target_must_differ'));

        var createModalEl = document.getElementById('languagePairsCreateModal');
        var editModalEl = document.getElementById('languagePairEditModal');
        var container = document.getElementById('language-pairs-rows');
        var template = document.getElementById('language-pair-row-template');
        var addButton = document.getElementById('add-language-pair-row');
        var createForm = document.getElementById('languagePairsCreateForm');
        var editForm = document.getElementById('languagePairEditForm');
        var nextIndex = container ? container.querySelectorAll('[data-pair-row]').length : 0;

        function initSelect2($select, $parent) {
            if (!$select.length || $select.hasClass('select2-hidden-accessible')) {
                return;
            }

            if (!$select.parent().hasClass('position-relative')) {
                $select.wrap('<div class="position-relative"></div>');
            }

            $select.select2({
                placeholder: "{!! __('general.select') !!}",
                allowClear: false,
                width: '100%',
                dropdownParent: $parent || $select.parent()
            });
        }

        function destroySelect2(scope) {
            $(scope).find('select[data-pair-select], select[data-edit-pair-select]').each(function () {
                var $select = $(this);
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }
                if ($select.parent().hasClass('position-relative')) {
                    $select.unwrap();
                }
            });
        }

        function refreshRemoveButtons() {
            if (!container) {
                return;
            }

            var rows = container.querySelectorAll('[data-pair-row]');
            rows.forEach(function (row) {
                var button = row.querySelector('[data-remove-pair-row]');
                if (button) {
                    button.disabled = rows.length <= 1;
                }
            });
        }

        function pairKey(sourceId, targetId) {
            return String(sourceId) + ':' + String(targetId);
        }

        function validateCreateForm() {
            var rows = container.querySelectorAll('[data-pair-row]');
            var seen = {};

            for (var i = 0; i < rows.length; i++) {
                var source = rows[i].querySelector('[name*="[source_language_id]"]');
                var target = rows[i].querySelector('[name*="[target_language_id]"]');
                var sourceId = source ? source.value : '';
                var targetId = target ? target.value : '';

                if (!sourceId || !targetId) {
                    continue;
                }

                if (sourceId === targetId) {
                    Swal.fire({
                        icon: 'error',
                        title: "{!! __('general.error') !!}",
                        text: sameLanguageMsg,
                        customClass: { confirmButton: 'btn btn-success' }
                    });
                    return false;
                }

                var key = pairKey(sourceId, targetId);

                if (seen[key]) {
                    Swal.fire({
                        icon: 'error',
                        title: "{!! __('general.error') !!}",
                        text: duplicateInFormMsg,
                        customClass: { confirmButton: 'btn btn-success' }
                    });
                    return false;
                }

                if (existingPairKeys.indexOf(key) !== -1) {
                    Swal.fire({
                        icon: 'error',
                        title: "{!! __('general.error') !!}",
                        text: duplicateMsg,
                        customClass: { confirmButton: 'btn btn-success' }
                    });
                    return false;
                }

                seen[key] = true;
            }

            return true;
        }

        function validateEditForm(ignorePairId) {
            var sourceId = document.getElementById('edit_source_language_id').value;
            var targetId = document.getElementById('edit_target_language_id').value;

            if (!sourceId || !targetId) {
                return true;
            }

            if (sourceId === targetId) {
                Swal.fire({
                    icon: 'error',
                    title: "{!! __('general.error') !!}",
                    text: sameLanguageMsg,
                    customClass: { confirmButton: 'btn btn-success' }
                });
                return false;
            }

            var key = pairKey(sourceId, targetId);
            var originalKey = null;

            @foreach ($pairs as $pair)
                if (String(ignorePairId) === @json((string) $pair->id)) {
                    originalKey = @json($pair->source_language_id.':'.$pair->target_language_id);
                }
            @endforeach

            if (key !== originalKey && existingPairKeys.indexOf(key) !== -1) {
                Swal.fire({
                    icon: 'error',
                    title: "{!! __('general.error') !!}",
                    text: duplicateMsg,
                    customClass: { confirmButton: 'btn btn-success' }
                });
                return false;
            }

            return true;
        }

        if (createModalEl && container && template && addButton) {
            createModalEl.addEventListener('shown.bs.modal', function () {
                $(createModalEl).find('select[data-pair-select]').each(function () {
                    initSelect2($(this), $(createModalEl));
                });
                refreshRemoveButtons();
            });

            createModalEl.addEventListener('hidden.bs.modal', function () {
                destroySelect2(createModalEl);
            });

            addButton.addEventListener('click', function () {
                var html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex));
                var wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                var row = wrapper.firstElementChild;
                container.appendChild(row);
                nextIndex += 1;

                $(row).find('select[data-pair-select]').each(function () {
                    initSelect2($(this), $(createModalEl));
                });

                refreshRemoveButtons();
            });

            container.addEventListener('click', function (event) {
                var button = event.target.closest('[data-remove-pair-row]');
                if (!button || button.disabled) {
                    return;
                }

                var row = button.closest('[data-pair-row]');
                if (row && container.querySelectorAll('[data-pair-row]').length > 1) {
                    destroySelect2(row);
                    row.remove();
                    refreshRemoveButtons();
                }
            });

            createForm.addEventListener('submit', function (event) {
                if (!validateCreateForm()) {
                    event.preventDefault();
                }
            });
        }

        if (editModalEl && editForm) {
            editModalEl.addEventListener('show.bs.modal', function (event) {
                var trigger = event.relatedTarget;
                if (!trigger || !trigger.classList.contains('js-edit-language-pair')) {
                    return;
                }

                editForm.setAttribute('action', trigger.getAttribute('data-update-url'));
                document.getElementById('language_pair_edit_id').value = trigger.getAttribute('data-id') || '';
                document.getElementById('language_pair_update_url').value = trigger.getAttribute('data-update-url') || '';
                document.getElementById('edit_source_language_id').value = trigger.getAttribute('data-source-language-id') || '';
                document.getElementById('edit_target_language_id').value = trigger.getAttribute('data-target-language-id') || '';
            });

            editModalEl.addEventListener('shown.bs.modal', function () {
                $(editModalEl).find('select[data-edit-pair-select]').each(function () {
                    initSelect2($(this), $(editModalEl));
                });
            });

            editModalEl.addEventListener('hidden.bs.modal', function () {
                destroySelect2(editModalEl);
            });

            editForm.addEventListener('submit', function (event) {
                var pairId = document.getElementById('language_pair_edit_id').value;
                if (!validateEditForm(pairId)) {
                    event.preventDefault();
                }
            });
        }

        @if ($errors->any())
            @if (old('_method') === 'PUT' && old('update_url'))
                editForm.setAttribute('action', @json(old('update_url')));
                document.getElementById('language_pair_edit_id').value = @json(old('pair_id'));
                document.getElementById('language_pair_update_url').value = @json(old('update_url'));
                document.getElementById('edit_source_language_id').value = @json(old('source_language_id'));
                document.getElementById('edit_target_language_id').value = @json(old('target_language_id'));
                bootstrap.Modal.getOrCreateInstance(editModalEl).show();
            @else
                bootstrap.Modal.getOrCreateInstance(createModalEl).show();
            @endif
        @endif
    })(jQuery);

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
                    url: "{!! route('admin.vendors.language-pairs.update-status', $vendor) !!}",
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
