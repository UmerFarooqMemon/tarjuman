<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo text-center" style="margin: 0px; padding-left: 20px;height: fit-content !important;min-height: 80px;">
        <a href="{{ route('vendor.dashboard.index') }}" class="app-brand-link text-center p-2">
            <img src="{{ siteLogoUrl() }}" class="img-fluid" style="width: 100%;" alt="">
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">
        <li class="menu-item {{ request()->routeIs('vendor.dashboard.*') ? 'active' : '' }}">
            <a href="{{ route('vendor.dashboard.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-smart-home"></i>
                <div>{!! __('general.menu_dashboard') !!}</div>
            </a>
        </li>
    </ul>
</aside>
