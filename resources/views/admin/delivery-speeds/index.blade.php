@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">{!! __('general.menu_delivery_speeds') !!}</h5>
        </div>
        @can('delivery_speeds.create')
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deliverySpeedFormModal" data-mode="create">
            {!! __('general.add_delivery_speed') !!}
        </button>
        @endcan
    </div>
    <div class="card-datatable text-nowrap">
        <table class="datatables-records table">
            <thead>
                <tr>
                    <th>{!! __('general.name') !!}</th>
                    <th>{!! __('general.duration') !!}</th>
                    <th>{!! __('general.price') !!}</th>
                    <th>{!! __('general.hours_range') !!}</th>
                    <th>{!! __('general.status') !!}</th>
                    <th>{!! __('general.actions') !!}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deliverySpeeds as $deliverySpeed)
                    @php
                        $translationPayload = [];
                        foreach (crudLocaleCodes() as $localeCode) {
                            $translationPayload[$localeCode] = [
                                'name' => $deliverySpeed->{"name:{$localeCode}"},
                                'duration_label' => $deliverySpeed->{"duration_label:{$localeCode}"},
                            ];
                        }
                        $hoursLabel = collect([
                            $deliverySpeed->min_hours !== null ? '≥ '.$deliverySpeed->min_hours.'h' : null,
                            $deliverySpeed->max_hours !== null ? '≤ '.$deliverySpeed->max_hours.'h' : null,
                        ])->filter()->implode(' · ') ?: '—';
                    @endphp
                    <tr>
                        <td>{{ $deliverySpeed->name }}</td>
                        <td>{{ $deliverySpeed->duration_label }}</td>
                        <td>{!! formatMoney($deliverySpeed->price_amount) !!}</td>
                        <td>{{ $hoursLabel }}</td>
                        <td>
                            @can('delivery_speeds.edit')
                            <label class="switch switch-success changeStatus" data-status="{{ $deliverySpeed->is_active }}" data-id="{{ $deliverySpeed->id }}" style="font-size: 15px !important">
                                <input type="checkbox" class="switch-input" {{ $deliverySpeed->is_active ? 'checked' : '' }}>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="ti ti-check"></i></span>
                                    <span class="switch-off"><i class="ti ti-x"></i></span>
                                </span>
                            </label>
                            @else
                                @if ($deliverySpeed->is_active)
                                    <span class="badge bg-label-success">{!! __('general.active') !!}</span>
                                @else
                                    <span class="badge bg-label-warning">{!! __('general.inactive') !!}</span>
                                @endif
                            @endcan
                        </td>
                        <td>
                            <div class="d-inline-block">
                                @can('delivery_speeds.edit')
                                <a
                                    href="javascript:;"
                                    class="text-body"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deliverySpeedFormModal"
                                    data-mode="edit"
                                    data-id="{{ $deliverySpeed->id }}"
                                    data-price-amount="{{ $deliverySpeed->price_amount }}"
                                    data-min-hours="{{ $deliverySpeed->min_hours }}"
                                    data-max-hours="{{ $deliverySpeed->max_hours }}"
                                    data-translations='@json($translationPayload)'
                                    data-update-url="{{ route('admin.delivery-speeds.update', $deliverySpeed) }}">
                                    <i class="text-primary ti ti-pencil"></i>
                                </a>
                                @endcan
                                @can('delivery_speeds.delete')
                                <a href="javascript:;" onclick="deleteConfirmation({{ $deliverySpeed->id }})" class="text-danger"><i class="ti ti-trash"></i></a>
                                <form action="{{ route('admin.delivery-speeds.destroy', $deliverySpeed) }}" method="POST" id="deleteForm{{ $deliverySpeed->id }}">
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

@include('admin.delivery-speeds.partials.form-modal', ['crudLocales' => $crudLocales])
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
        var modalEl = document.getElementById('deliverySpeedFormModal');
        if (!modalEl) return;

        var form = document.getElementById('deliverySpeedForm');
        var methodInput = document.getElementById('delivery_speed_form_method');
        var titleEl = document.getElementById('deliverySpeedFormModalLabel');
        var storeUrl = @json(route('admin.delivery-speeds.store'));
        var createTitle = @json(__('general.create_delivery_speed'));
        var editTitle = @json(__('general.edit_delivery_speed'));
        var localeCodes = @json(crudLocaleCodes());

        function setTranslations(translations) {
            localeCodes.forEach(function (code) {
                var nameInput = form.querySelector('#name_' + code);
                var durationInput = form.querySelector('#duration_label_' + code);
                if (nameInput) {
                    nameInput.value = (translations && translations[code] && translations[code].name) ? translations[code].name : '';
                }
                if (durationInput) {
                    durationInput.value = (translations && translations[code] && translations[code].duration_label) ? translations[code].duration_label : '';
                }
            });
        }

        function fillForm(data) {
            form.querySelector('#price_amount').value = data.price_amount != null ? data.price_amount : 0;
            form.querySelector('#min_hours').value = data.min_hours != null && data.min_hours !== '' ? data.min_hours : '';
            form.querySelector('#max_hours').value = data.max_hours != null && data.max_hours !== '' ? data.max_hours : '';
            setTranslations(data.translations || {});
        }

        function setCreateMode() {
            titleEl.textContent = createTitle;
            form.setAttribute('action', storeUrl);
            methodInput.value = 'POST';
            form.querySelector('#delivery_speed_id').value = '';
            form.querySelector('#delivery_speed_update_url').value = '';
            fillForm({ price_amount: 0, min_hours: '', max_hours: '', translations: {} });
        }

        function setEditMode(trigger) {
            titleEl.textContent = editTitle;
            form.setAttribute('action', trigger.getAttribute('data-update-url'));
            methodInput.value = 'PUT';
            form.querySelector('#delivery_speed_id').value = trigger.getAttribute('data-id') || '';
            form.querySelector('#delivery_speed_update_url').value = trigger.getAttribute('data-update-url') || '';
            var translations = {};
            try { translations = JSON.parse(trigger.getAttribute('data-translations') || '{}'); } catch (e) {}
            fillForm({
                price_amount: trigger.getAttribute('data-price-amount'),
                min_hours: trigger.getAttribute('data-min-hours'),
                max_hours: trigger.getAttribute('data-max-hours'),
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
                price_amount: @json(old('price_amount', 0)),
                min_hours: @json(old('min_hours')),
                max_hours: @json(old('max_hours')),
                translations: @json(old('translations', []))
            });
            @if (old('_method') === 'PUT' && old('update_url'))
                titleEl.textContent = editTitle;
                form.setAttribute('action', @json(old('update_url')));
                methodInput.value = 'PUT';
                form.querySelector('#delivery_speed_id').value = @json(old('delivery_speed_id'));
                form.querySelector('#delivery_speed_update_url').value = @json(old('update_url'));
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
                    url: "{!! route('admin.delivery-speeds.update-status') !!}",
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
