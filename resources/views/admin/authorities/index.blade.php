@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">{!! __('general.menu_authorities') !!}</h5>
        </div>
        @can('authorities.create')
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#authorityFormModal" data-mode="create">
            {!! __('general.add_authority') !!}
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
                @foreach ($authorities as $authority)
                    @php
                        $translationPayload = [];
                        foreach (crudLocaleCodes() as $localeCode) {
                            $translationPayload[$localeCode] = [
                                'name' => $authority->{"name:{$localeCode}"},
                            ];
                        }
                    @endphp
                    <tr>
                        <td>{{ $authority->name }}</td>
                        <td>
                            @can('authorities.edit')
                            <label class="switch switch-success changeStatus" data-status="{{ $authority->is_active }}" data-id="{{ $authority->id }}" style="font-size: 15px !important">
                                <input type="checkbox" class="switch-input" {{ $authority->is_active ? 'checked' : '' }}>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="ti ti-check"></i></span>
                                    <span class="switch-off"><i class="ti ti-x"></i></span>
                                </span>
                            </label>
                            @else
                                @if ($authority->is_active)
                                    <span class="badge bg-label-success">{!! __('general.active') !!}</span>
                                @else
                                    <span class="badge bg-label-warning">{!! __('general.inactive') !!}</span>
                                @endif
                            @endcan
                        </td>
                        <td>
                            <div class="d-inline-block">
                                @can('authorities.edit')
                                <a
                                    href="javascript:;"
                                    class="text-body"
                                    data-bs-toggle="modal"
                                    data-bs-target="#authorityFormModal"
                                    data-mode="edit"
                                    data-id="{{ $authority->id }}"
                                    data-translations='@json($translationPayload)'
                                    data-update-url="{{ route('admin.authorities.update', $authority) }}">
                                    <i class="text-primary ti ti-pencil"></i>
                                </a>
                                @endcan
                                @can('authorities.delete')
                                <a href="javascript:;" onclick="deleteConfirmation({{ $authority->id }})" class="text-danger"><i class="ti ti-trash"></i></a>
                                <form action="{{ route('admin.authorities.destroy', $authority) }}" method="POST" id="deleteForm{{ $authority->id }}">
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

@include('admin.authorities.partials.form-modal', ['crudLocales' => $crudLocales])
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
        var modalEl = document.getElementById('authorityFormModal');
        if (!modalEl) return;

        var form = document.getElementById('authorityForm');
        var methodInput = document.getElementById('authority_form_method');
        var titleEl = document.getElementById('authorityFormModalLabel');
        var storeUrl = @json(route('admin.authorities.store'));
        var createTitle = @json(__('general.create_authority'));
        var editTitle = @json(__('general.edit_authority'));
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
            form.querySelector('#authority_id').value = '';
            form.querySelector('#authority_update_url').value = '';
            fillForm({ translations: {} });
        }

        function setEditMode(trigger) {
            titleEl.textContent = editTitle;
            form.setAttribute('action', trigger.getAttribute('data-update-url'));
            methodInput.value = 'PUT';
            form.querySelector('#authority_id').value = trigger.getAttribute('data-id') || '';
            form.querySelector('#authority_update_url').value = trigger.getAttribute('data-update-url') || '';
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
                form.querySelector('#authority_id').value = @json(old('authority_id'));
                form.querySelector('#authority_update_url').value = @json(old('update_url'));
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
                    url: "{!! route('admin.authorities.update-status') !!}",
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
