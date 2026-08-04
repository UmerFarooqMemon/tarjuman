<!-- Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo text-center" style="margin: 0px; padding-left: 20px;height: fit-content !important;min-height: 80px;">
        <a href="javascript:;" class="app-brand-link text-center p-2">
            <img src="{{ siteLogoUrl() }}" class="img-fluid" style="width: 100%;" alt="{{ $siteSettings->site_title ?? config('app.name') }}">
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">
        <li class="menu-item {{ request()->routeIs('admin.dashboard.*') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-smart-home"></i>
                <div>{!! __('general.menu_dashboard') !!}</div>
            </a>
        </li>

        @can('administrators.view')
        <li class="menu-item {{ request()->routeIs('admin.administrators.*') ? 'active' : '' }}">
            <a href="{{ route('admin.administrators.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-users"></i>
                <div>{!! __('general.menu_administrators') !!}</div>
            </a>
        </li>
        @endcan

        @can('authorities.view')
        <li class="menu-item {{ request()->routeIs('admin.authorities.*') ? 'active' : '' }}">
            <a href="{{ route('admin.authorities.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-building-bank"></i>
                <div>{!! __('general.menu_authorities') !!}</div>
            </a>
        </li>
        @endcan

        @can('roles.view')
        <li class="menu-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
            <a href="{{ route('admin.roles.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-shield-lock"></i>
                <div>{!! __('general.roles_and_permissions') !!}</div>
            </a>
        </li>
        @endcan

        @can('pricing_rules.view')
        <li class="menu-item {{ request()->routeIs('admin.pricing-rules.*') ? 'active' : '' }}">
            <a href="{{ route('admin.pricing-rules.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-coin"></i>
                <div>{!! __('general.menu_pricing_rules') !!}</div>
            </a>
        </li>
        @endcan

        @can('vendors.view')
        <li class="menu-item {{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}">
            <a href="{{ route('admin.vendors.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-building-store"></i>
                <div>{!! __('general.menu_vendors') !!}</div>
            </a>
        </li>
        @endcan

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">{!! __('general.menu_settings') !!}</span>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.site-settings.*', 'admin.languages.*', 'admin.currencies.*', 'admin.document-types.*', 'admin.add-ons.*', 'admin.delivery-speeds.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-settings"></i>
                <div>{!! __('general.menu_settings') !!}</div>
            </a>
            <ul class="menu-sub">
                @can('site_settings.view')
                <li class="menu-item {{ request()->routeIs('admin.site-settings.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.site-settings.index') }}" class="menu-link">
                        <div>{!! __('general.general_settings') !!}</div>
                    </a>
                </li>
                @endcan
                @can('languages.view')
                <li class="menu-item {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.languages.index') }}" class="menu-link">
                        <div>{!! __('general.menu_languages') !!}</div>
                    </a>
                </li>
                @endcan
                @can('currencies.view')
                <li class="menu-item {{ request()->routeIs('admin.currencies.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.currencies.index') }}" class="menu-link">
                        <div>{!! __('general.menu_currencies') !!}</div>
                    </a>
                </li>
                @endcan
                @can('document_types.view')
                <li class="menu-item {{ request()->routeIs('admin.document-types.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.document-types.index') }}" class="menu-link">
                        <div>{!! __('general.menu_document_types') !!}</div>
                    </a>
                </li>
                @endcan
                @can('add_ons.view')
                <li class="menu-item {{ request()->routeIs('admin.add-ons.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.add-ons.index') }}" class="menu-link">
                        <div>{!! __('general.menu_add_ons') !!}</div>
                    </a>
                </li>
                @endcan
                @can('delivery_speeds.view')
                <li class="menu-item {{ request()->routeIs('admin.delivery-speeds.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.delivery-speeds.index') }}" class="menu-link">
                        <div>{!! __('general.menu_delivery_speeds') !!}</div>
                    </a>
                </li>
                @endcan
                <li class="menu-item">
                    <a href="javascript:void(0);" class="menu-link" data-open-admin-appearance>
                        <div>{!! __('general.menu_appearance') !!}</div>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</aside>
<!-- / Menu -->
