@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{!! __('general.menu_languages') !!}</h5>
        @can('languages.create')
        <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#languageFormModal"
            data-mode="create">
            {!! __('general.add_language') !!}
        </button>
        @endcan
    </div>
    <div class="card-datatable text-nowrap">
        <table class="datatables-records table">
            <thead>
                <tr>
                    <th>{!! __('general.code') !!}</th>
                    <th>{!! __('general.native_name') !!}</th>
                    <th>{!! __('general.direction') !!}</th>
                    <th>{!! __('general.sort_order') !!}</th>
                    <th>{!! __('general.status') !!}</th>
                    <th>{!! __('general.actions') !!}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($languages as $language)
                    <tr>
                        <td><code>{{ strtoupper($language->code) }}</code></td>
                        <td @if($language->isRtl()) dir="rtl" @endif>{{ $language->native_name ?: '—' }}</td>
                        <td>{{ strtoupper($language->direction) }}</td>
                        <td>{{ $language->sort_order }}</td>
                        <td>
                            @can('languages.edit')
                            <label class="switch switch-success changeStatus" data-status="{{ $language->is_active }}" data-id="{{ $language->id }}" style="font-size: 15px !important">
                                <input type="checkbox" class="switch-input" {{ $language->is_active ? 'checked' : '' }}>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="ti ti-check"></i></span>
                                    <span class="switch-off"><i class="ti ti-x"></i></span>
                                </span>
                            </label>
                            @else
                                @if ($language->is_active)
                                    <span class="badge bg-label-success">{!! __('general.active') !!}</span>
                                @else
                                    <span class="badge bg-label-warning">{!! __('general.inactive') !!}</span>
                                @endif
                            @endcan
                        </td>
                        <td>
                            <div class="d-inline-block">
                                @can('languages.edit')
                                <a
                                    href="javascript:;"
                                    class="text-body js-edit-language"
                                    data-bs-toggle="modal"
                                    data-bs-target="#languageFormModal"
                                    data-mode="edit"
                                    data-id="{{ $language->id }}"
                                    data-code="{{ $language->code }}"
                                    data-native-name="{{ $language->native_name }}"
                                    data-direction="{{ $language->direction }}"
                                    data-sort-order="{{ $language->sort_order }}"
                                    data-code-locked="{{ $language->hasLockedCode() ? 1 : 0 }}"
                                    data-update-url="{{ route('admin.languages.update', $language) }}">
                                    <i class="text-primary ti ti-pencil"></i>
                                </a>
                                @endcan
                                @can('languages.delete')
                                @unless ($language->hasLockedCode())
                                <a href="javascript:;" onclick="deleteConfirmation({{ $language->id }})" class="text-danger"><i class="ti ti-trash"></i></a>
                                <form action="{{ route('admin.languages.destroy', $language) }}" method="POST" id="deleteForm{{ $language->id }}">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endunless
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@include('admin.languages.partials.form-modal')
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

    (function () {
        var modalEl = document.getElementById('languageFormModal');
        if (!modalEl) {
            return;
        }

        var form = document.getElementById('languageForm');
        var methodInput = document.getElementById('language_form_method');
        var titleEl = document.getElementById('languageFormModalLabel');
        var codeInput = form.querySelector('#code');
        var codeLockedHelp = document.getElementById('code_locked_help');
        var storeUrl = @json(route('admin.languages.store'));
        var createTitle = @json(__('general.create_language'));
        var editTitle = @json(__('general.edit_language'));

        function setCodeLocked(locked) {
            codeInput.readOnly = !!locked;
            codeInput.classList.toggle('bg-light', !!locked);
            if (codeLockedHelp) {
                codeLockedHelp.hidden = !locked;
            }
        }

        function fillForm(data) {
            codeInput.value = data.code || '';
            form.querySelector('#native_name').value = data.native_name || '';
            form.querySelector('#direction').value = data.direction || 'ltr';
            form.querySelector('#sort_order').value = data.sort_order != null ? data.sort_order : 0;
            setCodeLocked(!!Number(data.code_locked));
        }

        function setCreateMode() {
            titleEl.textContent = createTitle;
            form.setAttribute('action', storeUrl);
            methodInput.value = 'POST';
            form.querySelector('#language_id').value = '';
            form.querySelector('#language_update_url').value = '';
            fillForm({
                code: '',
                native_name: '',
                direction: 'ltr',
                sort_order: 0,
                code_locked: 0
            });
            form.querySelectorAll('.is-invalid').forEach(function (el) {
                el.classList.remove('is-invalid');
            });
        }

        function setEditMode(trigger) {
            titleEl.textContent = editTitle;
            form.setAttribute('action', trigger.getAttribute('data-update-url'));
            methodInput.value = 'PUT';
            form.querySelector('#language_id').value = trigger.getAttribute('data-id') || '';
            form.querySelector('#language_update_url').value = trigger.getAttribute('data-update-url') || '';
            fillForm({
                code: trigger.getAttribute('data-code'),
                native_name: trigger.getAttribute('data-native-name'),
                direction: trigger.getAttribute('data-direction'),
                sort_order: trigger.getAttribute('data-sort-order'),
                code_locked: trigger.getAttribute('data-code-locked')
            });
        }

        modalEl.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            if (!trigger) {
                return;
            }

            if (trigger.getAttribute('data-mode') === 'edit') {
                setEditMode(trigger);
            } else {
                setCreateMode();
            }
        });

        @if ($errors->any())
            fillForm({
                code: @json(old('code')),
                native_name: @json(old('native_name')),
                direction: @json(old('direction', 'ltr')),
                sort_order: @json(old('sort_order', 0)),
                code_locked: @json(in_array(strtolower((string) old('code')), crudLocaleCodes(), true) ? 1 : 0)
            });

            @if (old('_method') === 'PUT' && old('update_url'))
                titleEl.textContent = editTitle;
                form.setAttribute('action', @json(old('update_url')));
                methodInput.value = 'PUT';
                form.querySelector('#language_id').value = @json(old('language_id'));
                form.querySelector('#language_update_url').value = @json(old('update_url'));
            @else
                titleEl.textContent = createTitle;
                form.setAttribute('action', storeUrl);
                methodInput.value = 'POST';
                form.querySelector('#language_id').value = '';
                form.querySelector('#language_update_url').value = '';
            @endif

            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        @endif
    })();

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
                    url: "{!! route('admin.languages.update-status') !!}",
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
