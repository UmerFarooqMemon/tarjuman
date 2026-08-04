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

    $fontEnFile = is_string($settings?->font_en) && $settings->font_en !== '' && is_file(public_path(uploadsDir('front').$settings->font_en))
        ? $settings->font_en
        : null;
    $fontArFile = is_string($settings?->font_ar) && $settings->font_ar !== '' && is_file(public_path(uploadsDir('front').$settings->font_ar))
        ? $settings->font_ar
        : null;
    $fontEnUrl = $fontEnFile ? asset(uploadsDir('front').$fontEnFile) : null;
    $fontArUrl = $fontArFile ? asset(uploadsDir('front').$fontArFile) : null;
    $fontEnFormat = siteFontFormat($fontEnFile);
    $fontArFormat = siteFontFormat($fontArFile);
    $activeFontStack = siteFontCssStack();
@endphp
<style>
    @if ($fontEnUrl && $fontEnFormat)
    @font-face {
        font-family: "Tarjuman EN";
        src: url("{{ $fontEnUrl }}") format("{{ $fontEnFormat }}");
        font-style: normal;
        font-weight: 100 900;
        font-display: swap;
    }
    @endif
    @if ($fontArUrl && $fontArFormat)
    @font-face {
        font-family: "Tarjuman AR";
        src: url("{{ $fontArUrl }}") format("{{ $fontArFormat }}");
        font-style: normal;
        font-weight: 100 900;
        font-display: swap;
    }
    @endif

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
        --bs-body-font-family: {!! $activeFontStack !!};
        --admin-font-family: {!! $activeFontStack !!};
    }

    html,
    body {
        font-family: var(--admin-font-family) !important;
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
    .page-item.active .page-link {
        background-image: var(--admin-primary-gradient) !important;
        background-color: var(--admin-primary) !important;
        border-color: var(--admin-primary) !important;
        background-size: 100% 100% !important;
    }

    /* Nav pills / tabs */
    .nav-pills .nav-link.active,
    .nav-pills .nav-link.active:hover,
    .nav-pills .nav-link.active:focus,
    .nav-pills .nav-link.active:active,
    .nav.nav-pills .nav-link.active,
    .nav.nav-pills .nav-link.active:hover,
    .nav.nav-pills .nav-link.active:focus {
        background-image: var(--admin-primary-gradient) !important;
        background-color: var(--admin-primary) !important;
        border-color: var(--admin-primary) !important;
        background-size: 100% 100% !important;
        color: #fff !important;
        box-shadow: 0 2px 6px 0 color-mix(in srgb, var(--admin-primary) 40%, transparent);
    }

    .nav-pills .nav-link:not(.active):hover,
    .nav-pills .nav-link:not(.active):focus {
        color: var(--admin-primary) !important;
        background-color: color-mix(in srgb, var(--admin-primary) 12%, transparent) !important;
    }

    .nav-tabs .nav-link.active,
    .nav-tabs .nav-link.active:hover,
    .nav-tabs .nav-link.active:focus {
        color: var(--admin-primary) !important;
        border-bottom-color: var(--admin-primary) !important;
    }

    .nav-tabs .nav-link:hover,
    .nav-tabs .nav-link:focus {
        color: var(--admin-primary) !important;
    }

    /*
     * Vuexy RTL theme sets a hardcoded gradient with !important on active menu
     * items; match that specificity so branding wins in Arabic/RTL too.
     */
    .menu-item.active > .menu-link:not(.menu-toggle),
    .bg-menu-theme.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle),
    .menu.bg-primary.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle),
    [dir="rtl"] .bg-menu-theme.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle),
    [dir="rtl"] .menu.bg-primary.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle),
    .bg-menu-theme.menu-horizontal .menu-inner > .menu-item.active > .menu-link.menu-toggle,
    .menu.bg-primary.menu-horizontal .menu-inner > .menu-item.active > .menu-link.menu-toggle,
    [dir="rtl"] .bg-menu-theme.menu-horizontal .menu-inner > .menu-item.active > .menu-link.menu-toggle,
    [dir="rtl"] .menu.bg-primary.menu-horizontal .menu-inner > .menu-item.active > .menu-link.menu-toggle {
        background: var(--admin-primary-gradient) !important;
        background-color: var(--admin-primary) !important;
        border-color: var(--admin-primary) !important;
        background-size: 100% 100% !important;
        color: #fff !important;
        box-shadow: 0 2px 6px 0 color-mix(in srgb, var(--admin-primary) 48%, transparent);
    }

    .text-primary,
    a.text-primary {
        color: var(--admin-primary) !important;
    }

    .bg-label-primary {
        background-color: color-mix(in srgb, var(--admin-primary) 16%, transparent) !important;
        color: var(--admin-primary) !important;
    }

    .switch-success .switch-input:checked ~ .switch-toggle-slider,
    .switch-primary .switch-input:checked ~ .switch-toggle-slider {
        background: var(--admin-primary-gradient) !important;
        background-color: var(--admin-primary) !important;
        border-color: var(--admin-primary) !important;
        box-shadow: 0 2px 6px 0 color-mix(in srgb, var(--admin-primary) 40%, transparent) !important;
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
        /*color: var(--admin-primary);*/
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
