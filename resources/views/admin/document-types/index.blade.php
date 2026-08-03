@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">{!! __('general.menu_document_types') !!}</h5>
        </div>
        @can('document_types.create')
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#documentTypeFormModal" data-mode="create">
            {!! __('general.add_document_type') !!}
        </button>
        @endcan
    </div>
    <div class="card-datatable text-nowrap">
        <table class="datatables-records table">
            <thead>
                <tr>
                    <th>{!! __('general.name') !!}</th>
                    <th>{!! __('general.status') !!}</th>
                    <th>{!! __('general.actions') !!}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($documentTypes as $documentType)
                    @php
                        $translationPayload = [];
                        foreach (crudLocaleCodes() as $localeCode) {
                            $translationPayload[$localeCode] = [
                                'name' => $documentType->{"name:{$localeCode}"},
                            ];
                        }
                    @endphp
                    <tr>
                        <td>{{ $documentType->name }}</td>
                        <td>
                            @can('document_types.edit')
                            <label class="switch switch-success changeStatus" data-status="{{ $documentType->is_active }}" data-id="{{ $documentType->id }}" style="font-size: 15px !important">
                                <input type="checkbox" class="switch-input" {{ $documentType->is_active ? 'checked' : '' }}>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="ti ti-check"></i></span>
                                    <span class="switch-off"><i class="ti ti-x"></i></span>
                                </span>
                            </label>
                            @else
                                @if ($documentType->is_active)
                                    <span class="badge bg-label-success">{!! __('general.active') !!}</span>
                                @else
                                    <span class="badge bg-label-warning">{!! __('general.inactive') !!}</span>
                                @endif
                            @endcan
                        </td>
                        <td>
                            <div class="d-inline-block">
                                @can('document_types.edit')
                                <a
                                    href="javascript:;"
                                    class="text-body"
                                    data-bs-toggle="modal"
                                    data-bs-target="#documentTypeFormModal"
                                    data-mode="edit"
                                    data-id="{{ $documentType->id }}"
                                    data-translations='@json($translationPayload)'
                                    data-update-url="{{ route('admin.document-types.update', $documentType) }}">
                                    <i class="text-primary ti ti-pencil"></i>
                                </a>
                                @endcan
                                @can('document_types.delete')
                                <a href="javascript:;" onclick="deleteConfirmation({{ $documentType->id }})" class="text-danger"><i class="ti ti-trash"></i></a>
                                <form action="{{ route('admin.document-types.destroy', $documentType) }}" method="POST" id="deleteForm{{ $documentType->id }}">
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

@include('admin.document-types.partials.form-modal', ['crudLocales' => $crudLocales])
@endsection

@section('footer-js')
<script>
    var table = $('.datatables-records').DataTable({
        language: { url: langUrl },
        pageLength: 50,
        order: [],
        columnDefs: [{ orderable: false, targets: [1, 2] }],
        scrollX: true
    });

    (function () {
        var modalEl = document.getElementById('documentTypeFormModal');
        if (!modalEl) return;

        var form = document.getElementById('documentTypeForm');
        var methodInput = document.getElementById('document_type_form_method');
        var titleEl = document.getElementById('documentTypeFormModalLabel');
        var storeUrl = @json(route('admin.document-types.store'));
        var createTitle = @json(__('general.create_document_type'));
        var editTitle = @json(__('general.edit_document_type'));
        var localeCodes = @json(crudLocaleCodes());

        function setTranslationNames(translations) {
            localeCodes.forEach(function (code) {
                var input = form.querySelector('#name_' + code);
                if (input) {
                    input.value = (translations && translations[code] && translations[code].name) ? translations[code].name : '';
                }
            });
        }

        function fillForm(data) {
            setTranslationNames(data.translations || {});
        }

        function setCreateMode() {
            titleEl.textContent = createTitle;
            form.setAttribute('action', storeUrl);
            methodInput.value = 'POST';
            form.querySelector('#document_type_id').value = '';
            form.querySelector('#document_type_update_url').value = '';
            fillForm({ translations: {} });
        }

        function setEditMode(trigger) {
            titleEl.textContent = editTitle;
            form.setAttribute('action', trigger.getAttribute('data-update-url'));
            methodInput.value = 'PUT';
            form.querySelector('#document_type_id').value = trigger.getAttribute('data-id') || '';
            form.querySelector('#document_type_update_url').value = trigger.getAttribute('data-update-url') || '';
            var translations = {};
            try { translations = JSON.parse(trigger.getAttribute('data-translations') || '{}'); } catch (e) {}
            fillForm({ translations: translations });
        }

        modalEl.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            if (!trigger) return;
            if (trigger.getAttribute('data-mode') === 'edit') setEditMode(trigger);
            else setCreateMode();
        });

        @if ($errors->any())
            fillForm({
                translations: @json(old('translations', []))
            });
            @if (old('_method') === 'PUT' && old('update_url'))
                titleEl.textContent = editTitle;
                form.setAttribute('action', @json(old('update_url')));
                methodInput.value = 'PUT';
                form.querySelector('#document_type_id').value = @json(old('document_type_id'));
                form.querySelector('#document_type_update_url').value = @json(old('update_url'));
            @else
                setCreateMode();
                fillForm({ translations: @json(old('translations', [])) });
            @endif
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
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
            customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    url: "{!! route('admin.document-types.update-status') !!}",
                    type: 'POST',
                    data: { id: id, status: status, _token: "{!! csrf_token() !!}" },
                    success: function (response) {
                        if (response.error == 0) {
                            $row.attr('data-status', status);
                            Swal.fire({ icon: 'success', title: "{!! __('general.success') !!}", text: response.message, confirmButtonText: "{!! __('general.ok') !!}", customClass: { confirmButton: 'btn btn-success' } });
                        } else {
                            toggleButton.prop('checked', originalStatus == 1);
                            Swal.fire({ icon: 'error', title: "{!! __('general.error') !!}", text: response.message, customClass: { confirmButton: 'btn btn-success' } });
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
