@php
    /** @var string $driver */
    /** @var string $title */
    /** @var string|null $icon */
    /** @var bool $enabled */
    /** @var bool $isDefault */
    $modalId = 'gateway-config-'.$driver;
@endphp
<div class="col-lg-6">
    <div class="border rounded p-4 platform-setting-card">
        <div class="platform-gateway-card__header mb-3">
            @if (! empty($icon))
                <div class="platform-gateway-card__icon">
                    <img src="{{ $icon }}" alt="{{ $title }}" width="44" height="44" loading="lazy">
                </div>
            @endif
            <div class="platform-setting-card__copy">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h6 class="mb-0">{{ $title }}</h6>
                    <span class="badge bg-label-primary {{ $isDefault ? '' : 'd-none' }}" data-default-badge="{{ $driver }}">{{ __('general.platform_default_badge') }}</span>
                </div>
                <p class="text-muted mb-0 small mt-1">
                    {{ $enabled ? __('general.platform_gateway_configured') : __('general.platform_gateway_not_configured') }}
                </p>
            </div>
        </div>

        <div class="platform-gateway-card__actions">
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                {{ __('general.platform_configure') }}
            </button>
            <div class="platform-setting-card__control">
                <span class="small text-muted">{{ __('general.platform_set_as_default') }}</span>
                <label class="switch switch-success platform-switch">
                    <input
                        type="checkbox"
                        class="switch-input"
                        data-default-gateway-toggle
                        value="{{ $driver }}"
                        {{ $isDefault ? 'checked' : '' }}
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
