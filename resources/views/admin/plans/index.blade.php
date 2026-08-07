@extends('admin.layouts.app')

@section('content')
@php
    $platformCurrency = $platformCurrency ?? platformCurrency();
    $crudLocales = $crudLocales ?? crudLocales();
@endphp
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">{!! __('general.menu_plans') !!}</h5>
        </div>
        @can('plans.create')
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#planFormModal" data-mode="create">
            {!! __('general.add_plan') !!}
        </button>
        @endcan
    </div>
    <div class="card-datatable text-nowrap">
        <table class="datatables-records table">
            <thead>
                <tr>
                    <th>{!! __('general.name') !!}</th>
                    <th>{!! __('general.price') !!}</th>
                    <th>{!! __('general.platform_plan_quota') !!}</th>
                    <th>{!! __('general.menu_delivery_speeds') !!}</th>
                    <th>{!! __('general.menu_add_ons') !!}</th>
                    <th>{!! __('general.status') !!}</th>
                    <th>{!! __('general.actions') !!}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plans as $plan)
                    @php
                        $translationPayload = [];
                        foreach (crudLocaleCodes() as $localeCode) {
                            $translationPayload[$localeCode] = [
                                'name' => $plan->{"name:{$localeCode}"},
                            ];
                        }
                    @endphp
                    <tr>
                        <td>{{ $plan->displayName() }}</td>
                        <td>{!! formatMoney($plan->price_amount, $plan->currency ?: $platformCurrency) !!} / {{ $plan->billing_period }}</td>
                        <td>{{ $plan->quotaLabel() }}</td>
                        <td>{{ $plan->deliverySpeed?->displayName() ?: '—' }}</td>
                        <td>
                            @if ($plan->addOns->isEmpty())
                                —
                            @else
                                {{ $plan->addOns->map(fn ($a) => $a->displayName())->implode(', ') }}
                            @endif
                        </td>
                        <td>
                            @can('plans.edit')
                            <label class="switch switch-success changeStatus" data-status="{{ $plan->is_active ? 1 : 0 }}" data-id="{{ $plan->id }}" style="font-size: 15px !important">
                                <input type="checkbox" class="switch-input" {{ $plan->is_active ? 'checked' : '' }}>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="ti ti-check"></i></span>
                                    <span class="switch-off"><i class="ti ti-x"></i></span>
                                </span>
                            </label>
                            @else
                                @if ($plan->is_active)
                                    <span class="badge bg-label-success">{!! __('general.active') !!}</span>
                                @else
                                    <span class="badge bg-label-warning">{!! __('general.inactive') !!}</span>
                                @endif
                            @endcan
                        </td>
                        <td>
                            <div class="d-inline-block">
                                @can('plans.edit')
                                <a
                                    href="javascript:;"
                                    class="text-body"
                                    data-bs-toggle="modal"
                                    data-bs-target="#planFormModal"
                                    data-mode="edit"
                                    data-id="{{ $plan->id }}"
                                    data-price-amount="{{ $plan->price_amount }}"
                                    data-page-quota="{{ $plan->page_quota }}"
                                    data-word-quota="{{ $plan->word_quota }}"
                                    data-delivery-speed-id="{{ $plan->delivery_speed_id }}"
                                    data-add-on-ids='@json($plan->addOns->pluck('id')->values())'
                                    data-translations='@json($translationPayload)'
                                    data-update-url="{{ route('admin.plans.update', $plan) }}">
                                    <i class="text-primary ti ti-pencil"></i>
                                </a>
                                @endcan
                                @can('plans.delete')
                                <a href="javascript:;" onclick="deleteConfirmation({{ $plan->id }})" class="text-danger"><i class="ti ti-trash"></i></a>
                                <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" id="deleteForm{{ $plan->id }}">
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

@include('admin.plans.partials.form-modal', [
    'crudLocales' => $crudLocales,
    'platformCurrency' => $platformCurrency,
    'deliverySpeeds' => $deliverySpeeds,
    'addOns' => $addOns,
])
@endsection

@section('footer-js')
<script>
    var table = $('.datatables-records').DataTable({
        language: { url: langUrl },
        pageLength: 50,
        order: [],
        columnDefs: [{ orderable: false, targets: [5, 6] }],
        scrollX: true
    });

    (function () {
        var modalEl = document.getElementById('planFormModal');
        if (!modalEl) return;

        var form = document.getElementById('planForm');
        var methodInput = document.getElementById('plan_form_method');
        var titleEl = document.getElementById('planFormModalLabel');
        var storeUrl = @json(route('admin.plans.store'));
        var createTitle = @json(__('general.add_plan'));
        var editTitle = @json(__('general.edit_plan'));
        var localeCodes = @json(crudLocaleCodes());
        var $deliverySelect = $('#plan_delivery_speed_id');

        function initDeliverySelect2() {
            if (!$deliverySelect.length || !$.fn.select2) {
                return;
            }
            if ($deliverySelect.hasClass('select2-hidden-accessible')) {
                $deliverySelect.select2('destroy');
            }
            if (!$deliverySelect.parent().hasClass('position-relative')) {
                $deliverySelect.wrap('<div class="position-relative"></div>');
            }
            $deliverySelect.select2({
                dropdownParent: $('#planFormModal'),
                allowClear: true,
                placeholder: '—',
                width: '100%'
            });
        }

        function setTranslationNames(translations) {
            localeCodes.forEach(function (code) {
                var input = form.querySelector('#plan_name_' + code);
                if (input) {
                    input.value = (translations && translations[code] && translations[code].name) ? translations[code].name : '';
                }
            });
        }

        function setAddOnSelection(ids) {
            var selected = (ids || []).map(String);
            form.querySelectorAll('.plan-add-on-checkbox').forEach(function (input) {
                input.checked = selected.indexOf(String(input.value)) !== -1;
            });
        }

        function fillForm(data) {
            form.querySelector('#plan_price_amount').value = data.price_amount != null ? data.price_amount : '';
            form.querySelector('#plan_page_quota').value = data.page_quota != null ? data.page_quota : '';
            form.querySelector('#plan_word_quota').value = data.word_quota != null ? data.word_quota : '';
            $deliverySelect.val(data.delivery_speed_id || '').trigger('change');
            setTranslationNames(data.translations || {});
            setAddOnSelection(data.add_on_ids || []);
        }

        function setCreateMode() {
            titleEl.textContent = createTitle;
            form.setAttribute('action', storeUrl);
            methodInput.value = 'POST';
            form.querySelector('#plan_id').value = '';
            form.querySelector('#plan_update_url').value = '';
            fillForm({ translations: {}, add_on_ids: [] });
        }

        function setEditMode(trigger) {
            titleEl.textContent = editTitle;
            form.setAttribute('action', trigger.getAttribute('data-update-url'));
            methodInput.value = 'PUT';
            form.querySelector('#plan_id').value = trigger.getAttribute('data-id') || '';
            form.querySelector('#plan_update_url').value = trigger.getAttribute('data-update-url') || '';
            var translations = {};
            var addOnIds = [];
            try { translations = JSON.parse(trigger.getAttribute('data-translations') || '{}'); } catch (e) {}
            try { addOnIds = JSON.parse(trigger.getAttribute('data-add-on-ids') || '[]'); } catch (e) {}
            fillForm({
                price_amount: trigger.getAttribute('data-price-amount'),
                page_quota: trigger.getAttribute('data-page-quota'),
                word_quota: trigger.getAttribute('data-word-quota'),
                delivery_speed_id: trigger.getAttribute('data-delivery-speed-id'),
                translations: translations,
                add_on_ids: addOnIds
            });
        }

        initDeliverySelect2();

        modalEl.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            if (!trigger) return;
            if (trigger.getAttribute('data-mode') === 'edit') setEditMode(trigger);
            else setCreateMode();
        });

        @if ($errors->any())
            fillForm({
                price_amount: @json(old('price_amount')),
                page_quota: @json(old('page_quota')),
                word_quota: @json(old('word_quota')),
                delivery_speed_id: @json(old('delivery_speed_id')),
                translations: @json(old('translations', [])),
                add_on_ids: @json(old('add_on_ids', []))
            });
            @if (old('_method') === 'PUT' && old('update_url'))
                titleEl.textContent = editTitle;
                form.setAttribute('action', @json(old('update_url')));
                methodInput.value = 'PUT';
                form.querySelector('#plan_id').value = @json(old('plan_id'));
                form.querySelector('#plan_update_url').value = @json(old('update_url'));
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
                    url: "{!! route('admin.plans.update-status') !!}",
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
