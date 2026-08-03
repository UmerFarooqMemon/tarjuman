@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">{!! __('general.menu_add_ons') !!}</h5>
        </div>
        @can('add_ons.create')
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOnFormModal" data-mode="create">
            {!! __('general.add_add_on') !!}
        </button>
        @endcan
    </div>
    <div class="card-datatable text-nowrap">
        <table class="datatables-records table">
            <thead>
                <tr>
                    <th>{!! __('general.name') !!}</th>
                    <th>{!! __('general.pricing_mode') !!}</th>
                    <th>{!! __('general.default_amount') !!}</th>
                    <th>{!! __('general.status') !!}</th>
                    <th>{!! __('general.actions') !!}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($addOns as $addOn)
                    @php
                        $translationPayload = [];
                        foreach (crudLocaleCodes() as $localeCode) {
                            $translationPayload[$localeCode] = [
                                'name' => $addOn->{"name:{$localeCode}"},
                            ];
                        }
                    @endphp
                    <tr>
                        <td>{{ $addOn->name }}</td>
                        <td>{{ $addOn->pricingModeLabel() }}</td>
                        <td>{!! formatMoney($addOn->default_amount) !!}</td>
                        <td>
                            @can('add_ons.edit')
                            <label class="switch switch-success changeStatus" data-status="{{ $addOn->is_active }}" data-id="{{ $addOn->id }}" style="font-size: 15px !important">
                                <input type="checkbox" class="switch-input" {{ $addOn->is_active ? 'checked' : '' }}>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="ti ti-check"></i></span>
                                    <span class="switch-off"><i class="ti ti-x"></i></span>
                                </span>
                            </label>
                            @else
                                @if ($addOn->is_active)
                                    <span class="badge bg-label-success">{!! __('general.active') !!}</span>
                                @else
                                    <span class="badge bg-label-warning">{!! __('general.inactive') !!}</span>
                                @endif
                            @endcan
                        </td>
                        <td>
                            <div class="d-inline-block">
                                @can('add_ons.edit')
                                <a
                                    href="javascript:;"
                                    class="text-body"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addOnFormModal"
                                    data-mode="edit"
                                    data-id="{{ $addOn->id }}"
                                    data-pricing-mode="{{ $addOn->pricing_mode }}"
                                    data-default-amount="{{ $addOn->default_amount }}"
                                    data-translations='@json($translationPayload)'
                                    data-update-url="{{ route('admin.add-ons.update', $addOn) }}">
                                    <i class="text-primary ti ti-pencil"></i>
                                </a>
                                @endcan
                                @can('add_ons.delete')
                                <a href="javascript:;" onclick="deleteConfirmation({{ $addOn->id }})" class="text-danger"><i class="ti ti-trash"></i></a>
                                <form action="{{ route('admin.add-ons.destroy', $addOn) }}" method="POST" id="deleteForm{{ $addOn->id }}">
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

@include('admin.add-ons.partials.form-modal', ['crudLocales' => $crudLocales])
@endsection

@section('footer-js')
<script>
    var table = $('.datatables-records').DataTable({
        language: { url: langUrl },
        pageLength: 50,
        order: [],
        columnDefs: [{ orderable: false, targets: [3, 4] }],
        scrollX: true
    });

    (function () {
        var modalEl = document.getElementById('addOnFormModal');
        if (!modalEl) return;

        var form = document.getElementById('addOnForm');
        var methodInput = document.getElementById('add_on_form_method');
        var titleEl = document.getElementById('addOnFormModalLabel');
        var storeUrl = @json(route('admin.add-ons.store'));
        var createTitle = @json(__('general.create_add_on'));
        var editTitle = @json(__('general.edit_add_on'));
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
            form.querySelector('#pricing_mode').value = data.pricing_mode || 'fixed';
            form.querySelector('#default_amount').value = data.default_amount != null ? data.default_amount : 0;
            setTranslationNames(data.translations || {});
        }

        function setCreateMode() {
            titleEl.textContent = createTitle;
            form.setAttribute('action', storeUrl);
            methodInput.value = 'POST';
            form.querySelector('#add_on_id').value = '';
            form.querySelector('#add_on_update_url').value = '';
            fillForm({ pricing_mode: 'fixed', default_amount: 0, translations: {} });
        }

        function setEditMode(trigger) {
            titleEl.textContent = editTitle;
            form.setAttribute('action', trigger.getAttribute('data-update-url'));
            methodInput.value = 'PUT';
            form.querySelector('#add_on_id').value = trigger.getAttribute('data-id') || '';
            form.querySelector('#add_on_update_url').value = trigger.getAttribute('data-update-url') || '';
            var translations = {};
            try { translations = JSON.parse(trigger.getAttribute('data-translations') || '{}'); } catch (e) {}
            fillForm({
                pricing_mode: trigger.getAttribute('data-pricing-mode'),
                default_amount: trigger.getAttribute('data-default-amount'),
                translations: translations
            });
        }

        modalEl.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            if (!trigger) return;
            if (trigger.getAttribute('data-mode') === 'edit') setEditMode(trigger);
            else setCreateMode();
        });

        @if ($errors->any())
            fillForm({
                pricing_mode: @json(old('pricing_mode', 'fixed')),
                default_amount: @json(old('default_amount', 0)),
                translations: @json(old('translations', []))
            });
            @if (old('_method') === 'PUT' && old('update_url'))
                titleEl.textContent = editTitle;
                form.setAttribute('action', @json(old('update_url')));
                methodInput.value = 'PUT';
                form.querySelector('#add_on_id').value = @json(old('add_on_id'));
                form.querySelector('#add_on_update_url').value = @json(old('update_url'));
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
                    url: "{!! route('admin.add-ons.update-status') !!}",
                    type: 'POST',
                    data: { id: id, status: status, _token: "{!! csrf_token() !!}" },
                    success: function (response) {
                        if (response.error == 0) {
                            $row.attr('data-status', status);
                            Swal.fire({ icon: 'success', title: "{!! __('general.success') !!}", text: response.message, confirmButtonText: "{!! __('general.ok') !!}", customClass: { confirmButton: 'btn btn-success' } });
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
