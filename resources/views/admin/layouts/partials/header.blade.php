<!-- Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo text-center" style="margin: 0px; padding-left: 20px;height: fit-content !important;">
        <a href="javascript:;" class="app-brand-link text-center">
            @if ($siteSettings?->logo && file_exists(uploadsDir('front') . $siteSettings->logo))
            <img src="{!! asset(uploadsDir('front') . $siteSettings->logo) !!}" class="img-fluid" style="width: 100%;">
            @else
            <img src="{!! asset('assets/img/logo-placeholder.png') !!}" class="img-fluid" style="width: 100%;">
            @endif
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">
        <li class="menu-item {{ request()->segment(2) == 'dashboard' ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-smart-home"></i>
                <div data-i18n="Dashboard">{!! __('general.menu_dashboard') !!}</div>
            </a>
        </li>

        @can('administrators.view')
        <li class="menu-item {{ request()->segment(2) == 'administrators' ? 'active' : '' }}">
            <a href="{{ route('admin.administrators.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-users"></i>
                <div data-i18n="Administrators">{!! __('general.menu_administrators') !!}</div>
            </a>
        </li>
        @endcan

        @can('roles.view')
        <li class="menu-item {{ request()->segment(2) == 'roles' ? 'active' : '' }}">
            <a href="{{ route('admin.roles.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-shield-lock"></i>
                <div data-i18n="{!! __('general.roles_and_permissions') !!}">{!! __('general.roles_and_permissions') !!}</div>
            </a>
        </li>
        @endcan

        @can('site_settings.view')
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">{!! __('general.menu_settings') !!}</span>
        </li>

        <li class="menu-item {{ in_array(request()->segment(2), ['site-settings'], true) ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-settings"></i>
                <div data-i18n="Settings">{!! __('general.menu_settings') !!}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->segment(2) == 'site-settings' ? 'active' : '' }}">
                    <a href="{{ route('admin.site-settings.index') }}" class="menu-link">
                        <div data-i18n="Site Settings">{!! __('general.general_settings') !!}</div>
                    </a>
                </li>
            </ul>
        </li>
        @endcan
    </ul>
</aside>
<!-- / Menu -->
