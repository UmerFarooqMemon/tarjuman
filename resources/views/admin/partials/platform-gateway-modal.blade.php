@php
    /** @var string $driver */
    /** @var string $title */
    /** @var string|null $icon */
    /** @var bool $enabled */
    /** @var bool $testMode */
    /** @var list<array{name: string, label: string, type: string, value: mixed, secret?: bool, has_value?: bool}> $fields */
    $enabledName = $driver === 'amazon_ps' ? 'amazon_ps_enabled' : $driver.'_enabled';
    $testModeName = $driver === 'amazon_ps' ? 'amazon_ps_test_mode' : $driver.'_test_mode';
    $modalId = 'gateway-config-'.$driver;
    $formId = 'platform-settings-form';
@endphp

<div class="modal fade" id="{{ $modalId }}" data-platform-gateway-modal>
    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
        <div class="modal-content p-0 p-md-5">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4">
                    @if (! empty($icon))
                        <div class="platform-gateway-card__icon mx-auto mb-3">
                            <img src="{{ $icon }}" alt="" width="44" height="44" loading="lazy">
                        </div>
                    @endif
                    <h3 class="mb-2">{{ __('general.platform_configure') }} — {{ $title }}</h3>
                </div>

                <div class="platform-setting-card__row mb-4">
                    <div class="platform-setting-card__copy">
                        <h6 class="mb-0">{{ __('general.active') }}</h6>
                        <p class="text-muted mb-0 small">{{ __('general.platform_gateway_enable_hint') }}</p>
                    </div>
                    <div class="platform-setting-card__control">
                        <label class="switch switch-success platform-switch">
                            <input
                                type="checkbox"
                                class="switch-input"
                                form="{{ $formId }}"
                                name="{{ $enabledName }}"
                                value="1"
                                {{ $enabled ? 'checked' : '' }}
                            >
                            <span class="switch-toggle-slider">
                                <span class="switch-on"><i class="ti ti-check"></i></span>
                                <span class="switch-off"><i class="ti ti-x"></i></span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="row">
                    @foreach ($fields as $field)
                        <div class="mb-3 col-12">
                            <label class="form-label" for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                            <input
                                type="{{ $field['type'] }}"
                                class="form-control"
                                id="{{ $field['name'] }}"
                                form="{{ $formId }}"
                                name="{{ $field['name'] }}"
                                value="{{ $field['value'] }}"
                                autocomplete="off"
                                @if (! empty($field['secret'])) placeholder="{{ ! empty($field['has_value']) ? __('general.platform_secret_kept') : '' }}" @endif
                            >
                        </div>
                    @endforeach

                    <div class="mb-3 col-12">
                        <div class="platform-setting-card__row">
                            <div class="platform-setting-card__copy">
                                <label class="form-label mb-0" for="{{ $testModeName }}">{{ __('general.platform_test_mode') }}</label>
                            </div>
                            <div class="platform-setting-card__control">
                                <label class="switch switch-success platform-switch">
                                    <input
                                        type="checkbox"
                                        class="switch-input"
                                        id="{{ $testModeName }}"
                                        form="{{ $formId }}"
                                        name="{{ $testModeName }}"
                                        value="1"
                                        {{ $testMode ? 'checked' : '' }}
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

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{!! __('general.cancel') !!}</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{!! __('general.done') !!}</button>
                </div>
            </div>
        </div>
    </div>
</div>
