@extends('admin.layouts.app')

@section('css')
<style>
    .platform-setting-card {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .platform-setting-card__row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        min-width: 0;
    }
    .platform-setting-card__copy {
        flex: 1 1 auto;
        min-width: 0;
    }
    .platform-setting-card__control {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
        min-height: 1.35rem;
        max-width: 100%;
    }
    /* Vuexy .switch-toggle-slider is position:absolute — reserve width so it stays in-card */
    .platform-switch {
        position: relative;
        display: inline-block;
        width: 2.5rem;
        min-width: 2.5rem;
        height: 1.35rem;
        margin: 0;
        flex-shrink: 0;
        vertical-align: middle;
    }
    .platform-switch .switch-toggle-slider {
        left: 0;
        right: auto;
    }
    [dir="rtl"] .platform-switch .switch-toggle-slider {
        left: auto;
        right: 0;
    }
    .platform-gateway-card__actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: auto;
        padding-top: 1rem;
        min-width: 0;
    }
    .platform-gateway-card__header {
        display: flex;
        align-items: flex-start;
        gap: 0.875rem;
        min-width: 0;
    }
    .platform-gateway-card__icon {
        flex: 0 0 auto;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(75, 70, 92, 0.12);
        background: #fff;
        border: 1px solid rgba(75, 70, 92, 0.08);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.35rem;
    }
    .platform-gateway-card__icon img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
</style>
@endsection

@section('content')
@php
    $paymentMode = old('order_payment_mode', $records->order_payment_mode ?: 'later');
    $assignmentMode = old('order_assignment_mode', normalizeAssignmentMode($records->order_assignment_mode));
    $defaultGateway = old('default_payment_gateway', $records->default_payment_gateway);
    $gateways = [
        [
            'driver' => 'paytabs',
            'title' => __('general.platform_gateway_paytabs'),
            'icon' => asset('assets/img/connected-apps/paytabs.webp'),
            'enabled' => (bool) old('paytabs_enabled', $records->paytabs_enabled),
            'testMode' => (bool) old('paytabs_test_mode', $records->paytabs_test_mode ?? true),
            'fields' => [
                ['name' => 'paytabs_profile_id', 'label' => __('general.platform_paytabs_profile_id'), 'type' => 'text', 'value' => old('paytabs_profile_id', $records->paytabs_profile_id), 'secret' => false],
                ['name' => 'paytabs_server_key', 'label' => __('general.platform_paytabs_server_key'), 'type' => 'password', 'value' => '', 'secret' => true, 'has_value' => filled($records->paytabs_server_key)],
                ['name' => 'paytabs_client_key', 'label' => __('general.platform_paytabs_client_key'), 'type' => 'password', 'value' => '', 'secret' => true, 'has_value' => filled($records->paytabs_client_key)],
            ],
        ],
        [
            'driver' => 'tap',
            'title' => __('general.platform_gateway_tap'),
            'icon' => asset('assets/img/connected-apps/tap-payments.svg'),
            'enabled' => (bool) old('tap_enabled', $records->tap_enabled),
            'testMode' => (bool) old('tap_test_mode', $records->tap_test_mode ?? true),
            'fields' => [
                ['name' => 'tap_secret_key', 'label' => __('general.platform_tap_secret_key'), 'type' => 'password', 'value' => '', 'secret' => true, 'has_value' => filled($records->tap_secret_key)],
                ['name' => 'tap_public_key', 'label' => __('general.platform_tap_public_key'), 'type' => 'password', 'value' => '', 'secret' => true, 'has_value' => filled($records->tap_public_key)],
            ],
        ],
        [
            'driver' => 'noon',
            'title' => __('general.platform_gateway_noon'),
            'icon' => asset('assets/img/connected-apps/noon-payments.png'),
            'enabled' => (bool) old('noon_enabled', $records->noon_enabled),
            'testMode' => (bool) old('noon_test_mode', $records->noon_test_mode ?? true),
            'fields' => [
                ['name' => 'noon_business_id', 'label' => __('general.platform_noon_business_id'), 'type' => 'text', 'value' => old('noon_business_id', $records->noon_business_id), 'secret' => false],
                ['name' => 'noon_app_key', 'label' => __('general.platform_noon_app_key'), 'type' => 'password', 'value' => '', 'secret' => true, 'has_value' => filled($records->noon_app_key)],
                ['name' => 'noon_app_secret', 'label' => __('general.platform_noon_app_secret'), 'type' => 'password', 'value' => '', 'secret' => true, 'has_value' => filled($records->noon_app_secret)],
            ],
        ],
        [
            'driver' => 'amazon_ps',
            'title' => __('general.platform_gateway_amazon_ps'),
            'icon' => asset('assets/img/connected-apps/amazon-payments.jpg'),
            'enabled' => (bool) old('amazon_ps_enabled', $records->amazon_ps_enabled),
            'testMode' => (bool) old('amazon_ps_test_mode', $records->amazon_ps_test_mode ?? true),
            'fields' => [
                ['name' => 'amazon_ps_merchant_identifier', 'label' => __('general.platform_amazon_ps_merchant_id'), 'type' => 'text', 'value' => old('amazon_ps_merchant_identifier', $records->amazon_ps_merchant_identifier), 'secret' => false],
                ['name' => 'amazon_ps_access_code', 'label' => __('general.platform_amazon_ps_access_code'), 'type' => 'password', 'value' => '', 'secret' => true, 'has_value' => filled($records->amazon_ps_access_code)],
                ['name' => 'amazon_ps_sha_request', 'label' => __('general.platform_amazon_ps_sha_request'), 'type' => 'password', 'value' => '', 'secret' => true, 'has_value' => filled($records->amazon_ps_sha_request)],
                ['name' => 'amazon_ps_sha_response', 'label' => __('general.platform_amazon_ps_sha_response'), 'type' => 'password', 'value' => '', 'secret' => true, 'has_value' => filled($records->amazon_ps_sha_response)],
            ],
        ],
    ];
@endphp
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('general.menu_platform_settings') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.platform-settings.update', $records->id) }}" id="platform-settings-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="default_payment_gateway" id="default_payment_gateway" value="{{ $defaultGateway }}">

                    <ul class="nav nav-pills mb-4 flex-wrap gap-1" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link active" id="platform-basic-tab" data-bs-toggle="pill" data-bs-target="#platform-basic" role="tab" aria-controls="platform-basic" aria-selected="true">
                                {{ __('general.platform_tab_basic') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" id="platform-connected-tab" data-bs-toggle="pill" data-bs-target="#platform-connected" role="tab" aria-controls="platform-connected" aria-selected="false">
                                {{ __('general.platform_tab_connected_apps') }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="platform-basic" role="tabpanel" aria-labelledby="platform-basic-tab" tabindex="0">
                            @php
                                $downloadAllowed = filter_var(
                                    old('vendor_document_download_allowed', $records->vendor_document_download_allowed ?? false),
                                    FILTER_VALIDATE_BOOLEAN
                                );
                            @endphp
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="border rounded p-4 h-100">
                                        <h6 class="mb-3">{{ __('general.platform_modes') }}</h6>

                                        <div class="platform-setting-card__row py-3 border-bottom">
                                            <div class="platform-setting-card__copy">
                                                <h6 class="mb-1">{{ __('general.platform_quick_payment') }}</h6>
                                                <p class="text-muted mb-0 small">{{ __('general.platform_quick_payment_hint') }}</p>
                                            </div>
                                            <div class="platform-setting-card__control">
                                                <label class="switch switch-success platform-switch">
                                                    <input type="radio" class="switch-input" name="order_payment_mode" value="quick" {{ $paymentMode === 'quick' ? 'checked' : '' }}>
                                                    <span class="switch-toggle-slider">
                                                        <span class="switch-on"><i class="ti ti-check"></i></span>
                                                        <span class="switch-off"><i class="ti ti-x"></i></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="platform-setting-card__row py-3 border-bottom">
                                            <div class="platform-setting-card__copy">
                                                <h6 class="mb-1">{{ __('general.platform_pay_later') }}</h6>
                                                <p class="text-muted mb-0 small">{{ __('general.platform_pay_later_hint') }}</p>
                                            </div>
                                            <div class="platform-setting-card__control">
                                                <label class="switch switch-success platform-switch">
                                                    <input type="radio" class="switch-input" name="order_payment_mode" value="later" {{ $paymentMode === 'later' ? 'checked' : '' }}>
                                                    <span class="switch-toggle-slider">
                                                        <span class="switch-on"><i class="ti ti-check"></i></span>
                                                        <span class="switch-off"><i class="ti ti-x"></i></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="platform-setting-card__row py-3 border-bottom">
                                            <div class="platform-setting-card__copy">
                                                <h6 class="mb-1">{{ __('general.platform_assignment_manual') }}</h6>
                                                <p class="text-muted mb-0 small">{{ __('general.platform_assignment_manual_hint') }}</p>
                                            </div>
                                            <div class="platform-setting-card__control">
                                                <label class="switch switch-success platform-switch">
                                                    <input type="radio" class="switch-input" name="order_assignment_mode" value="manual" {{ $assignmentMode === 'manual' ? 'checked' : '' }}>
                                                    <span class="switch-toggle-slider">
                                                        <span class="switch-on"><i class="ti ti-check"></i></span>
                                                        <span class="switch-off"><i class="ti ti-x"></i></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="platform-setting-card__row py-3 border-bottom">
                                            <div class="platform-setting-card__copy">
                                                <h6 class="mb-1">{{ __('general.platform_assignment_open') }}</h6>
                                                <p class="text-muted mb-0 small">{{ __('general.platform_assignment_open_hint') }}</p>
                                            </div>
                                            <div class="platform-setting-card__control">
                                                <label class="switch switch-success platform-switch">
                                                    <input type="radio" class="switch-input" name="order_assignment_mode" value="open" {{ $assignmentMode === 'open' ? 'checked' : '' }}>
                                                    <span class="switch-toggle-slider">
                                                        <span class="switch-on"><i class="ti ti-check"></i></span>
                                                        <span class="switch-off"><i class="ti ti-x"></i></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="platform-setting-card__row py-3">
                                            <div class="platform-setting-card__copy">
                                                <h6 class="mb-1">{{ __('general.platform_vendor_document_download') }}</h6>
                                                <p class="text-muted mb-0 small">{{ __('general.platform_vendor_document_download_hint') }}</p>
                                            </div>
                                            <div class="platform-setting-card__control">
                                                <input type="hidden" name="vendor_document_download_allowed" value="0">
                                                <label class="switch switch-success platform-switch">
                                                    <input
                                                        type="checkbox"
                                                        class="switch-input"
                                                        name="vendor_document_download_allowed"
                                                        value="1"
                                                        {{ $downloadAllowed ? 'checked' : '' }}
                                                    >
                                                    <span class="switch-toggle-slider">
                                                        <span class="switch-on"><i class="ti ti-check"></i></span>
                                                        <span class="switch-off"><i class="ti ti-x"></i></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="border rounded p-4 h-100">
                                        <h6 class="mb-3">{{ __('general.platform_fields') }}</h6>

                                        <div class="mb-4">
                                            <label class="form-label" for="order_source_retention_days">{{ __('general.platform_source_retention_days') }}</label>
                                            <p class="text-muted mb-2 small">{{ __('general.platform_source_retention_hint') }}</p>
                                            <input type="number" min="1" max="3650" class="form-control" id="order_source_retention_days" name="order_source_retention_days" value="{{ old('order_source_retention_days', $records->order_source_retention_days ?? 90) }}" required>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label" for="order_delivery_retention_days">{{ __('general.platform_delivery_retention_days') }}</label>
                                            <p class="text-muted mb-2 small">{{ __('general.platform_delivery_retention_hint') }}</p>
                                            <input type="number" min="1" max="3650" class="form-control" id="order_delivery_retention_days" name="order_delivery_retention_days" value="{{ old('order_delivery_retention_days', $records->order_delivery_retention_days ?? 1095) }}" required>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label" for="vendor_payout_schedule">{{ __('general.platform_vendor_payout_schedule') }}</label>
                                            <p class="text-muted mb-2 small">{{ __('general.platform_vendor_payout_schedule_hint') }}</p>
                                            <select class="form-select" id="vendor_payout_schedule" name="vendor_payout_schedule" required>
                                                <option value="weekly" @selected(old('vendor_payout_schedule', $records->vendor_payout_schedule ?: 'weekly') === 'weekly')>{{ __('general.platform_payout_weekly') }}</option>
                                                <option value="monthly" @selected(old('vendor_payout_schedule', $records->vendor_payout_schedule ?: 'weekly') === 'monthly')>{{ __('general.platform_payout_monthly') }}</option>
                                            </select>
                                        </div>

                                        <div class="mb-0">
                                            <label class="form-label" for="platform_fee_percent">{{ __('general.platform_fee_percent') }}</label>
                                            <p class="text-muted mb-2 small">{{ __('general.platform_fee_percent_hint') }}</p>
                                            <div class="input-group">
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="100"
                                                    class="form-control"
                                                    id="platform_fee_percent"
                                                    name="platform_fee_percent"
                                                    value="{{ old('platform_fee_percent', $records->platform_fee_percent ?? 10) }}"
                                                    required
                                                >
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="platform-connected" role="tabpanel" aria-labelledby="platform-connected-tab" tabindex="0">
                            <p class="text-muted mb-4">{{ __('general.platform_connected_apps_hint') }}</p>
                            <div class="row g-4">
                                @foreach ($gateways as $gateway)
                                    @include('admin.partials.platform-gateway-card', [
                                        'driver' => $gateway['driver'],
                                        'title' => $gateway['title'],
                                        'icon' => $gateway['icon'],
                                        'enabled' => $gateway['enabled'],
                                        'isDefault' => $defaultGateway === $gateway['driver'],
                                    ])
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">{{ __('general.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@foreach ($gateways as $gateway)
    @include('admin.partials.platform-gateway-modal', [
        'driver' => $gateway['driver'],
        'title' => $gateway['title'],
        'icon' => $gateway['icon'],
        'enabled' => $gateway['enabled'],
        'testMode' => $gateway['testMode'],
        'fields' => $gateway['fields'],
    ])
@endforeach
@endsection

@section('footer-js')
<script>
(function () {
    document.querySelectorAll('[data-platform-gateway-modal]').forEach(function (modalEl) {
        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
    });

    var defaultInput = document.getElementById('default_payment_gateway');

    document.querySelectorAll('[data-default-gateway-toggle]').forEach(function (input) {
        input.addEventListener('change', function () {
            if (!input.checked) {
                if (defaultInput.value === input.value) {
                    defaultInput.value = '';
                }
                return;
            }

            defaultInput.value = input.value;
            document.querySelectorAll('[data-default-gateway-toggle]').forEach(function (other) {
                if (other !== input) {
                    other.checked = false;
                }
            });
            document.querySelectorAll('[data-default-badge]').forEach(function (badge) {
                badge.classList.toggle('d-none', badge.getAttribute('data-default-badge') !== input.value);
            });
        });
    });
})();
</script>
@endsection
