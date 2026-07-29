@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{!! __('general.menu_currencies') !!}</h5>
        @can('currencies.create')
        <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#currencyFormModal"
            data-mode="create">
            {!! __('general.add_currency') !!}
        </button>
        @endcan
    </div>
    <div class="card-datatable text-nowrap">
        <table class="datatables-records table">
            <thead>
                <tr>
                    <th>{!! __('general.code') !!}</th>
                    <th>{!! __('general.name') !!}</th>
                    <th>{!! __('general.icon') !!}</th>
                    <th>{!! __('general.decimals') !!}</th>
                    <th>{!! __('general.status') !!}</th>
                    <th>{!! __('general.actions') !!}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($currencies as $currency)
                    @php
                        $translationPayload = [];
                        foreach (crudLocaleCodes() as $localeCode) {
                            $translationPayload[$localeCode] = [
                                'name' => $currency->translate($localeCode, false)?->name,
                            ];
                        }
                    @endphp
                    <tr>
                        <td>
                            <code>{{ strtoupper($currency->code) }}</code>
                            @if ($currency->isPlatformCurrency())
                                <span class="badge bg-label-primary ms-1">{!! __('general.platform_currency_badge') !!}</span>
                            @endif
                        </td>
                        <td>{{ $currency->displayName() }}</td>
                        <td>
                            <span class="currency-option">
                                {!! currencyIconHtml($currency->code, 'currency-icon') !!}
                            </span>
                        </td>
                        <td>{{ $currency->decimals }}</td>
                        <td>
                            @can('currencies.edit')
                            <label class="switch switch-success changeStatus" data-status="{{ $currency->is_active }}" data-id="{{ $currency->id }}" style="font-size: 15px !important">
                                <input type="checkbox" class="switch-input" {{ $currency->is_active ? 'checked' : '' }}>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="ti ti-check"></i></span>
                                    <span class="switch-off"><i class="ti ti-x"></i></span>
                                </span>
                            </label>
                            @else
                                @if ($currency->is_active)
                                    <span class="badge bg-label-success">{!! __('general.active') !!}</span>
                                @else
                                    <span class="badge bg-label-warning">{!! __('general.inactive') !!}</span>
                                @endif
                            @endcan
                        </td>
                        <td>
                            <div class="d-inline-block">
                                @can('currencies.edit')
                                <a
                                    href="javascript:;"
                                    class="text-body js-edit-currency"
                                    data-bs-toggle="modal"
                                    data-bs-target="#currencyFormModal"
                                    data-mode="edit"
                                    data-id="{{ $currency->id }}"
                                    data-code="{{ $currency->code }}"
                                    data-decimals="{{ $currency->decimals }}"
                                    data-translations='@json($translationPayload)'
                                    data-update-url="{{ route('admin.currencies.update', $currency) }}">
                                    <i class="text-primary ti ti-pencil"></i>
                                </a>
                                @endcan
                                @can('currencies.delete')
                                <a href="javascript:;" onclick="deleteConfirmation({{ $currency->id }})" class="text-danger"><i class="ti ti-trash"></i></a>
                                <form action="{{ route('admin.currencies.destroy', $currency) }}" method="POST" id="deleteForm{{ $currency->id }}">
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

@include('admin.currencies.partials.form-modal', ['crudLocales' => $crudLocales])
@endsection

@section('footer-js')
@php
    $currencyPreviews = $currencies->mapWithKeys(function ($currency) {
        return [
            (string) $currency->id => [
                'html' => currencyIconHtml($currency->code, 'currency-icon currency-icon--lg'),
                'label' => strtoupper($currency->code).' — '.$currency->displayName(),
            ],
        ];
    });
@endphp
<script>
    var currencyPreviews = @json($currencyPreviews);

    var table = $('.datatables-records').DataTable({
        language: { url: langUrl },
        pageLength: 50,
        order: [],
        columnDefs: [{ orderable: false, targets: [2, 4, 5] }],
        scrollX: true
    });

    (function () {
        var modalEl = document.getElementById('currencyFormModal');
        if (!modalEl) {
            return;
        }

        var form = document.getElementById('currencyForm');
        var methodInput = document.getElementById('currency_form_method');
        var titleEl = document.getElementById('currencyFormModalLabel');
        var previewWrap = document.getElementById('currency_preview_wrap');
        var previewEl = document.getElementById('currency_preview');
        var storeUrl = @json(route('admin.currencies.store'));
        var createTitle = @json(__('general.create_currency'));
        var editTitle = @json(__('general.edit_currency'));
        var localeCodes = @json(crudLocaleCodes());

        function setTranslationNames(translations) {
            localeCodes.forEach(function (code) {
                var input = form.querySelector('#name_' + code);
                if (input) {
                    input.value = (translations && translations[code] && translations[code].name) ? translations[code].name : '';
                }
            });
        }

        function clearFileInput() {
            var fileInput = form.querySelector('#icon_file');
            if (fileInput) {
                fileInput.value = '';
            }
        }

        function setPreview(html, label) {
            if (!html) {
                previewWrap.hidden = true;
                previewEl.innerHTML = '';
                return;
            }

            previewWrap.hidden = false;
            previewEl.innerHTML = html + ' <strong class="ms-1">' + (label || '') + '</strong>';
        }

        function fillForm(data) {
            form.querySelector('#code').value = data.code || '';
            form.querySelector('#decimals').value = data.decimals != null ? data.decimals : 2;
            setTranslationNames(data.translations || {});
            clearFileInput();
            setPreview(data.preview_html || '', data.preview_label || '');
        }

        function setCreateMode() {
            titleEl.textContent = createTitle;
            form.setAttribute('action', storeUrl);
            methodInput.value = 'POST';
            form.querySelector('#currency_id').value = '';
            form.querySelector('#currency_update_url').value = '';
            fillForm({
                code: '',
                decimals: 2,
                translations: {},
                preview_html: '',
                preview_label: ''
            });
            form.querySelectorAll('.is-invalid').forEach(function (el) {
                el.classList.remove('is-invalid');
            });
        }

        function setEditMode(trigger) {
            titleEl.textContent = editTitle;
            form.setAttribute('action', trigger.getAttribute('data-update-url'));
            methodInput.value = 'PUT';
            form.querySelector('#currency_id').value = trigger.getAttribute('data-id') || '';
            form.querySelector('#currency_update_url').value = trigger.getAttribute('data-update-url') || '';

            var translations = {};
            try {
                translations = JSON.parse(trigger.getAttribute('data-translations') || '{}');
            } catch (e) {
                translations = {};
            }

            fillForm({
                code: trigger.getAttribute('data-code'),
                decimals: trigger.getAttribute('data-decimals'),
                translations: translations,
                preview_html: (currencyPreviews[trigger.getAttribute('data-id')] || {}).html || '',
                preview_label: (currencyPreviews[trigger.getAttribute('data-id')] || {}).label || ''
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
                decimals: @json(old('decimals', 2)),
                translations: @json(old('translations', [])),
                preview_html: '',
                preview_label: ''
            });

            @if (old('_method') === 'PUT' && old('update_url'))
                titleEl.textContent = editTitle;
                form.setAttribute('action', @json(old('update_url')));
                methodInput.value = 'PUT';
                form.querySelector('#currency_id').value = @json(old('currency_id'));
                form.querySelector('#currency_update_url').value = @json(old('update_url'));
            @else
                titleEl.textContent = createTitle;
                form.setAttribute('action', storeUrl);
                methodInput.value = 'POST';
                form.querySelector('#currency_id').value = '';
                form.querySelector('#currency_update_url').value = '';
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
                    url: "{!! route('admin.currencies.update-status') !!}",
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
