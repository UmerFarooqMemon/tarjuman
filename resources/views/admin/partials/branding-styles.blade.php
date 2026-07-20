@php
    $settings = $siteSettings ?? null;
    $primarySolid = $settings?->brandingSolid('primary_color', '#7367F0') ?? '#7367F0';
    $secondarySolid = $settings?->brandingSolid('secondary_color', '#A8AAAE') ?? '#A8AAAE';
    $primaryGradient = $settings?->brandingBackground('primary_color', '#7367F0') ?? '#7367F0';
    $secondaryGradient = $settings?->brandingBackground('secondary_color', '#A8AAAE') ?? '#A8AAAE';
    $primaryButtonGradient = $settings?->brandingBackground('primary_button_color', $primarySolid) ?? $primarySolid;
    $secondaryButtonGradient = $settings?->brandingBackground('secondary_button_color', $secondarySolid) ?? $secondarySolid;
    $primaryButtonText = $settings?->brandingSolid('primary_button_text_color', '#FFFFFF') ?? '#FFFFFF';
    $secondaryButtonText = $settings?->brandingSolid('secondary_button_text_color', '#FFFFFF') ?? '#FFFFFF';
    $primaryButtonBorder = $settings?->brandingSolid('primary_button_border_color', $primarySolid) ?? $primarySolid;
    $secondaryButtonBorder = $settings?->brandingSolid('secondary_button_border_color', $secondarySolid) ?? $secondarySolid;
@endphp
<style>
    :root {
        --bs-primary: {{ $primarySolid }};
        --bs-secondary: {{ $secondarySolid }};
        --admin-primary: {{ $primarySolid }};
        --admin-secondary: {{ $secondarySolid }};
        --admin-primary-gradient: {{ $primaryGradient }};
        --admin-secondary-gradient: {{ $secondaryGradient }};
        --admin-primary-btn: {{ $primaryButtonGradient }};
        --admin-secondary-btn: {{ $secondaryButtonGradient }};
        --admin-primary-btn-text: {{ $primaryButtonText }};
        --admin-secondary-btn-text: {{ $secondaryButtonText }};
        --admin-primary-btn-border: {{ $primaryButtonBorder }};
        --admin-secondary-btn-border: {{ $secondaryButtonBorder }};
        --bs-dropdown-link-hover-color: {{ $primarySolid }};
        --bs-dropdown-link-hover-bg: color-mix(in srgb, {{ $primarySolid }} 8%, transparent);
        --bs-dropdown-link-active-color: #fff;
        --bs-dropdown-link-active-bg: {{ $primarySolid }};
    }

    .btn-primary,
    .btn-primary:hover,
    .btn-primary:focus,
    .btn-primary:active,
    .btn-primary:disabled,
    .btn-primary.disabled {
        background-image: var(--admin-primary-btn) !important;
        background-color: var(--admin-primary) !important;
        border-color: var(--admin-primary-btn-border) !important;
        color: var(--admin-primary-btn-text) !important;
        background-size: 100% 100% !important;
    }

    .btn-secondary,
    .btn-secondary:hover,
    .btn-secondary:focus,
    .btn-secondary:active,
    .btn-secondary:disabled,
    .btn-secondary.disabled {
        background-image: var(--admin-secondary-btn) !important;
        background-color: var(--admin-secondary) !important;
        border-color: var(--admin-secondary-btn-border) !important;
        color: var(--admin-secondary-btn-text) !important;
        background-size: 100% 100% !important;
    }

    .bg-primary,
    .badge.bg-primary,
    .menu-item.active > .menu-link:not(.menu-toggle),
    .page-item.active .page-link {
        background-image: var(--admin-primary-gradient) !important;
        background-color: var(--admin-primary) !important;
        border-color: var(--admin-primary) !important;
        background-size: 100% 100% !important;
    }

    .text-primary,
    a.text-primary {
        color: var(--admin-primary) !important;
    }

    .bg-label-primary {
        background-color: color-mix(in srgb, var(--admin-primary) 16%, transparent) !important;
        color: var(--admin-primary) !important;
    }

    .switch-success .switch-input:checked ~ .switch-toggle-slider {
        background: var(--admin-primary-gradient) !important;
        background-color: var(--admin-primary) !important;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--admin-primary) !important;
        box-shadow: 0 0 0 0.2rem color-mix(in srgb, var(--admin-primary) 25%, transparent) !important;
    }

    .form-check-input:checked {
        background-color: var(--admin-primary) !important;
        border-color: var(--admin-primary) !important;
    }

    a:not(.btn):not(.menu-link):not(.dropdown-item) {
        color: var(--admin-primary);
    }

    a:not(.btn):not(.menu-link):not(.dropdown-item):hover {
        color: var(--admin-primary);
        filter: brightness(0.9);
    }

    .auth-cover-bg-color,
    .authentication-bg .auth-cover-bg {
        background-image: var(--admin-primary-gradient) !important;
        background-color: var(--admin-primary) !important;
        background-size: cover !important;
    }

    /* Bootstrap / Vuexy dropdowns */
    .dropdown-item:hover,
    .dropdown-item:focus {
        color: var(--admin-primary) !important;
        background-color: color-mix(in srgb, var(--admin-primary) 8%, transparent) !important;
    }

    .dropdown-item.active,
    .dropdown-item:active,
    .dropdown-menu > li.active > .dropdown-item,
    .dropdown-menu > .active > .dropdown-item {
        color: #fff !important;
        background-color: var(--admin-primary) !important;
        background-image: var(--admin-primary-gradient) !important;
        background-size: 100% 100% !important;
    }

    /* Select2 */
    .select2-results__option[role=option][aria-selected=true],
    .select2-container--default .select2-results__option--selected {
        background-color: var(--admin-primary) !important;
        background-image: var(--admin-primary-gradient) !important;
        background-size: 100% 100% !important;
        color: #fff !important;
    }

    .select2-container--default .select2-results__option--highlighted:not([aria-selected=true]),
    .select2-results__option--highlighted[role=option]:not([aria-selected=true]) {
        background-color: color-mix(in srgb, var(--admin-primary) 8%, transparent) !important;
        color: var(--admin-primary) !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        color: var(--admin-primary) !important;
        background-color: color-mix(in srgb, var(--admin-primary) 16%, transparent) !important;
    }

    .select2-container--default.select2-container--focus .select2-selection,
    .select2-container--default.select2-container--open .select2-selection,
    .light-style .select2-container--default.select2-container--focus .select2-selection,
    .light-style .select2-container--default.select2-container--open .select2-selection {
        border-color: var(--admin-primary) !important;
        box-shadow: 0 0 0 0.2rem color-mix(in srgb, var(--admin-primary) 25%, transparent) !important;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: var(--admin-primary) !important;
        outline: 0;
        box-shadow: 0 0 0 0.2rem color-mix(in srgb, var(--admin-primary) 25%, transparent) !important;
    }

    /* Bootstrap-select */
    .bootstrap-select .dropdown-menu li a:hover,
    .bootstrap-select .dropdown-menu li a:focus,
    .bootstrap-select .dropdown-menu .dropdown-item:hover,
    .bootstrap-select .dropdown-menu .dropdown-item:focus {
        color: var(--admin-primary) !important;
        background-color: color-mix(in srgb, var(--admin-primary) 8%, transparent) !important;
    }

    .bootstrap-select .dropdown-menu li.selected a,
    .bootstrap-select .dropdown-menu li.active a,
    .bootstrap-select .dropdown-menu .dropdown-item.active,
    .bootstrap-select .dropdown-menu .dropdown-item:active {
        color: #fff !important;
        background-color: var(--admin-primary) !important;
        background-image: var(--admin-primary-gradient) !important;
        background-size: 100% 100% !important;
    }

    .bootstrap-select .dropdown-toggle:focus,
    .bootstrap-select > .dropdown-toggle:focus,
    .bootstrap-select .btn.show {
        border-color: var(--admin-primary) !important;
        box-shadow: 0 0 0 0.2rem color-mix(in srgb, var(--admin-primary) 25%, transparent) !important;
    }
</style>
