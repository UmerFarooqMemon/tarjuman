@php
    /**
     * Dock navigation items.
     * - route: named route for direct navigation (no children)
     * - children: submenu entries; click opens dropdown instead of navigating
     * - permission: Spatie ability; null = always visible
     * - priority: lower = kept visible longer when collapsing into "More"
     */
    $settingsChildren = [];
    if (auth('admin')->user()?->can('site_settings.view')) {
        $settingsChildren[] = [
            'id' => 'site-settings',
            'label' => __('general.general_settings'),
            'icon' => 'ti ti-adjustments',
            'url' => route('admin.site-settings.index'),
            'active' => request()->segment(2) === 'site-settings',
        ];
    }
    $settingsChildren[] = [
        'id' => 'layout-sidebar',
        'label' => 'Sidebar',
        'icon' => 'ti ti-layout-sidebar',
        'url' => null,
        'action' => 'set-layout',
        'layout' => 'sidebar',
        'active' => false,
    ];
    $settingsChildren[] = [
        'id' => 'layout-dock',
        'label' => 'Dock',
        'icon' => 'ti ti-layout-bottombar',
        'url' => null,
        'action' => 'set-layout',
        'layout' => 'dock',
        'active' => false,
    ];

    $dockItems = collect([
        [
            'id' => 'dashboard',
            'label' => __('general.menu_dashboard'),
            'icon' => 'ti ti-smart-home',
            'route' => 'admin.dashboard.index',
            'url' => route('admin.dashboard.index'),
            'active' => request()->segment(2) === 'dashboard',
            'permission' => null,
            'priority' => 1,
            'children' => [],
        ],
        [
            'id' => 'administrators',
            'label' => __('general.menu_administrators'),
            'icon' => 'ti ti-users',
            'route' => 'admin.administrators.index',
            'url' => route('admin.administrators.index'),
            'active' => request()->segment(2) === 'administrators',
            'permission' => 'administrators.view',
            'priority' => 2,
            'children' => [],
        ],
        [
            'id' => 'roles',
            'label' => __('general.roles_and_permissions'),
            'icon' => 'ti ti-shield-lock',
            'route' => 'admin.roles.index',
            'url' => route('admin.roles.index'),
            'active' => request()->segment(2) === 'roles',
            'permission' => 'roles.view',
            'priority' => 3,
            'children' => [],
        ],
        [
            'id' => 'settings',
            'label' => __('general.menu_settings'),
            'icon' => 'ti ti-settings',
            'route' => null,
            'url' => null,
            'active' => in_array(request()->segment(2), ['site-settings'], true),
            'permission' => null,
            'priority' => 4,
            'children' => $settingsChildren,
        ],
    ])->filter(function (array $item) {
        return empty($item['permission']) || auth('admin')->user()?->can($item['permission']);
    })->values();

    $adminUser = auth('admin')->user();
    $adminDisplayName = trim(($adminUser->first_name ?? '').' '.($adminUser->last_name ?? ''));
    if ($adminDisplayName === '') {
        $adminDisplayName = $adminUser->email ?? __('general.edit_user');
    }
    $adminAvatar = ($adminUser && $adminUser->image && file_exists(uploadsDir('admin').$adminUser->image))
        ? asset(uploadsDir('admin').$adminUser->image)
        : asset('assets/img/avatars/1.png');

    $dockLogo = ($siteSettings?->logo && file_exists(uploadsDir('front').$siteSettings->logo))
        ? asset(uploadsDir('front').$siteSettings->logo)
        : asset('assets/img/logo-placeholder.png');
    $dockTitle = $siteSettings->site_title ?? config('app.name', 'Laravel');
@endphp

<nav class="admin-dock" id="adminDock" role="navigation" aria-label="{{ __('general.menu_dashboard') }} navigation">
    {{-- Desktop floating dock --}}
    <div class="admin-dock__desktop d-none d-md-flex" aria-hidden="false">
        <div class="admin-dock__glass">
            <div class="admin-dock__brand">
                <a
                    href="{{ route('admin.dashboard.index') }}"
                    class="admin-dock__brand-link"
                    data-dock-tip="{{ $dockTitle }}"
                    aria-label="{{ $dockTitle }}">
                    <img src="{{ $dockLogo }}" alt="{{ $dockTitle }}" class="admin-dock__brand-img">
                </a>
            </div>

            <div class="admin-dock__divider" aria-hidden="true"></div>

            <div class="admin-dock__scroller" data-dock-scroller>
                <ul class="admin-dock__items list-unstyled mb-0" data-dock-primary>
                    @foreach ($dockItems as $item)
                        @include('admin.layouts.partials.dock-nav.item', ['item' => $item, 'context' => 'desktop'])
                    @endforeach
                </ul>

                {{-- Overflow / More bucket (filled by JS when needed) --}}
                <div class="admin-dock__more d-none" data-dock-more>
                    <button
                        type="button"
                        class="admin-dock__btn"
                        data-dock-toggle="more"
                        data-dock-tip="More"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="dockMoreMenu"
                        aria-label="More">
                        <span class="admin-dock__icon"><i class="ti ti-dots"></i></span>
                        <span class="admin-dock__label">More</span>
                    </button>
                    <div class="admin-dock__dropdown" id="dockMoreMenu" data-dock-panel="more" hidden role="menu">
                        <ul class="list-unstyled mb-0" data-dock-more-list></ul>
                    </div>
                </div>
            </div>

            <div class="admin-dock__actions">
                <div class="admin-dock__divider" aria-hidden="true"></div>

                {{-- Theme switcher --}}
                <div class="admin-dock__theme" data-dock-theme-wrap>
                    <button
                        type="button"
                        class="admin-dock__btn"
                        data-dock-toggle="theme"
                        data-dock-tip="Theme"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="dockThemeMenu"
                        aria-label="Theme">
                        <span class="admin-dock__icon"><i class="ti ti-sun" data-dock-theme-icon></i></span>
                        <span class="admin-dock__label">Theme</span>
                    </button>
                    <div class="admin-dock__dropdown admin-dock__dropdown--theme" id="dockThemeMenu" data-dock-panel="theme" hidden role="menu">
                        <button type="button" class="admin-dock__dropdown-item" role="menuitem" data-dock-theme="light">
                            <i class="ti ti-sun me-2"></i>Light
                        </button>
                        <button type="button" class="admin-dock__dropdown-item" role="menuitem" data-dock-theme="dark">
                            <i class="ti ti-moon me-2"></i>Dark
                        </button>
                        <button type="button" class="admin-dock__dropdown-item" role="menuitem" data-dock-theme="system">
                            <i class="ti ti-device-desktop me-2"></i>System
                        </button>
                    </div>
                </div>

                <div class="admin-dock__divider" aria-hidden="true"></div>

                {{-- Profile (far right) --}}
                <div class="admin-dock__profile" data-dock-profile>
                    <button
                        type="button"
                        class="admin-dock__btn admin-dock__btn--profile"
                        data-dock-toggle="profile"
                        data-dock-tip="{{ $adminDisplayName }}"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="dockProfileMenu"
                        aria-label="{{ $adminDisplayName }}">
                        <span class="admin-dock__avatar">
                            <img src="{{ $adminAvatar }}" alt="">
                        </span>
                        <span class="admin-dock__label">{{ $adminDisplayName }}</span>
                    </button>
                    <div class="admin-dock__dropdown admin-dock__dropdown--profile" id="dockProfileMenu" data-dock-panel="profile" hidden role="menu">
                        <a class="admin-dock__dropdown-item" role="menuitem" href="{{ route('admin.update-profile') }}">
                            <i class="ti ti-user me-2"></i>{{ __('general.my_profile') }}
                        </a>
                        @can('site_settings.view')
                        <a class="admin-dock__dropdown-item" role="menuitem" href="{{ route('admin.site-settings.index') }}">
                            <i class="ti ti-settings me-2"></i>{{ __('general.menu_settings') }}
                        </a>
                        @endcan
                        <div class="admin-dock__dropdown-divider"></div>
                        <button type="button" class="admin-dock__dropdown-item text-danger" role="menuitem" onclick="logout()">
                            <i class="ti ti-logout me-2"></i>{{ __('general.log_out') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile compact bar --}}
    <div class="admin-dock__mobile d-flex d-md-none" aria-hidden="false">
        <a
            href="{{ route('admin.dashboard.index') }}"
            class="admin-dock__mobile-brand"
            aria-label="{{ $dockTitle }}">
            <img src="{{ $dockLogo }}" alt="{{ $dockTitle }}">
        </a>

        <button
            type="button"
            class="admin-dock__mobile-menu btn"
            data-dock-open-drawer
            aria-haspopup="dialog"
            aria-controls="adminDockDrawer"
            aria-expanded="false">
            <i class="ti ti-menu-2"></i>
            <span>Menu</span>
        </button>

        <div class="admin-dock__mobile-actions">
            <button
                type="button"
                class="admin-dock__mobile-theme-btn btn"
                data-dock-toggle="theme-mobile"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="dockThemeMenuMobile"
                aria-label="Theme">
                <i class="ti ti-sun" data-dock-theme-icon></i>
            </button>
            <div class="admin-dock__dropdown admin-dock__dropdown--theme" id="dockThemeMenuMobile" data-dock-panel="theme-mobile" hidden role="menu">
                <button type="button" class="admin-dock__dropdown-item" role="menuitem" data-dock-theme="light">
                    <i class="ti ti-sun me-2"></i>Light
                </button>
                <button type="button" class="admin-dock__dropdown-item" role="menuitem" data-dock-theme="dark">
                    <i class="ti ti-moon me-2"></i>Dark
                </button>
                <button type="button" class="admin-dock__dropdown-item" role="menuitem" data-dock-theme="system">
                    <i class="ti ti-device-desktop me-2"></i>System
                </button>
            </div>

            <button
                type="button"
                class="admin-dock__mobile-profile btn"
                data-dock-toggle="profile-mobile"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="dockProfileMenuMobile"
                aria-label="{{ $adminDisplayName }}">
                <img src="{{ $adminAvatar }}" alt="" class="rounded-circle">
            </button>
        </div>

        <div class="admin-dock__dropdown admin-dock__dropdown--mobile-profile" id="dockProfileMenuMobile" data-dock-panel="profile-mobile" hidden role="menu">
            <a class="admin-dock__dropdown-item" role="menuitem" href="{{ route('admin.update-profile') }}">
                <i class="ti ti-user me-2"></i>{{ __('general.edit_user') }}
            </a>
            <div class="admin-dock__dropdown-divider"></div>
            <button type="button" class="admin-dock__dropdown-item text-danger" role="menuitem" onclick="logout()">
                <i class="ti ti-logout me-2"></i>{{ __('general.log_out') }}
            </button>
        </div>
    </div>
</nav>

{{-- Mobile bottom sheet drawer --}}
<div class="admin-dock-drawer" id="adminDockDrawer" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-label="Navigation menu">
    <div class="admin-dock-drawer__backdrop" data-dock-close-drawer></div>
    <div class="admin-dock-drawer__sheet" data-dock-sheet>
        <div class="admin-dock-drawer__handle" data-dock-sheet-handle aria-hidden="true"></div>
        <div class="admin-dock-drawer__header">
            <h6 class="mb-0">Menu</h6>
            <button type="button" class="btn btn-icon btn-sm" data-dock-close-drawer aria-label="{{ __('general.cancel') }}">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="admin-dock-drawer__body">
            <ul class="admin-dock-drawer__grid list-unstyled mb-0" data-dock-mobile-grid>
                @foreach ($dockItems as $item)
                    @include('admin.layouts.partials.dock-nav.item', ['item' => $item, 'context' => 'mobile'])
                @endforeach
            </ul>
        </div>
    </div>
</div>
