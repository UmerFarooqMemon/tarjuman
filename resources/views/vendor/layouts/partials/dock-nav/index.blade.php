@php
    $vendorUser = auth('vendor')->user();
    $vendorDisplayName = $vendorUser?->fullName() ?: ($vendorUser?->email ?? __('general.vendor'));
    $vendorAvatar = ($vendorUser && $vendorUser->image && file_exists(uploadsDir('front').$vendorUser->image))
        ? asset(uploadsDir('front').$vendorUser->image)
        : asset('assets/img/avatars/1.png');

    $dockLogo = siteLogoUrl();
    $dockTitle = $siteSettings->site_title ?? config('app.name', 'Tarjuman');

    $ordersActive = request()->routeIs('vendor.orders.*') && ! request()->routeIs('vendor.orders.discover');
    $dockItems = collect([
        [
            'id' => 'dashboard',
            'label' => __('general.menu_dashboard'),
            'icon' => 'ti ti-smart-home',
            'route' => 'vendor.dashboard.index',
            'url' => route('vendor.dashboard.index'),
            'active' => request()->routeIs('vendor.dashboard.*'),
            'permission' => null,
            'priority' => 1,
            'children' => [],
        ],
        [
            'id' => 'discover',
            'label' => __('general.vendor_discover'),
            'icon' => 'ti ti-world-search',
            'route' => 'vendor.orders.discover',
            'url' => route('vendor.orders.discover'),
            'active' => request()->routeIs('vendor.orders.discover'),
            'permission' => null,
            'priority' => 2,
            'children' => [],
        ],
        [
            'id' => 'orders',
            'label' => __('general.menu_orders'),
            'icon' => 'ti ti-shopping-cart',
            'route' => 'vendor.orders.index',
            'url' => route('vendor.orders.index'),
            'active' => $ordersActive,
            'permission' => null,
            'priority' => 3,
            'children' => [],
        ],
        [
            'id' => 'settings',
            'label' => __('general.menu_settings'),
            'icon' => 'ti ti-settings',
            'route' => null,
            'url' => null,
            'active' => false,
            'permission' => null,
            'priority' => 4,
            'children' => [
                [
                    'id' => 'appearance',
                    'label' => __('general.menu_appearance'),
                    'icon' => 'ti ti-palette',
                    'url' => null,
                    'action' => 'open-appearance',
                    'active' => false,
                ],
            ],
        ],
    ]);
@endphp

@php($localeSwitcher = adminLocaleSwitcher())

<nav class="admin-dock" id="adminDock" role="navigation" aria-label="{{ __('general.navigation_menu') }}">
    <div class="admin-dock__desktop d-none d-md-flex" aria-hidden="false">
        <div class="admin-dock__glass">
            <div class="admin-dock__brand">
                <a
                    href="{{ route('vendor.dashboard.index') }}"
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

                <div class="admin-dock__more d-none" data-dock-more>
                    <button
                        type="button"
                        class="admin-dock__btn"
                        data-dock-toggle="more"
                        data-dock-tip="{{ __('general.more') }}"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="dockMoreMenu"
                        aria-label="{{ __('general.more') }}">
                        <span class="admin-dock__icon"><i class="ti ti-dots"></i></span>
                        <span class="admin-dock__label">{{ __('general.more') }}</span>
                    </button>
                    <div class="admin-dock__dropdown" id="dockMoreMenu" data-dock-panel="more" hidden role="menu">
                        <ul class="list-unstyled mb-0" data-dock-more-list></ul>
                    </div>
                </div>
            </div>

            <div class="admin-dock__actions">
                <div class="admin-dock__divider" aria-hidden="true"></div>

                @auth('vendor')
                @php($notificationsDropdown = notificationsDropdownConfig('vendor'))
                @if ($notificationsDropdown)
                <div
                    class="admin-dock__notifications"
                    data-dock-notifications-wrap
                    data-notifications-root
                    data-notifications-guard="vendor"
                    data-notifications-index-url="{{ $notificationsDropdown['notificationsIndexUrl'] }}"
                    data-notifications-mark-all-url="{{ $notificationsDropdown['notificationsMarkAllUrl'] }}"
                    data-notifications-mark-read-url-template="{{ $notificationsDropdown['notificationsMarkReadUrlTemplate'] }}"
                    data-notifications-destroy-url-template="{{ $notificationsDropdown['notificationsDestroyUrlTemplate'] }}"
                    data-broadcast-auth-url="{{ $notificationsDropdown['broadcastAuthUrl'] }}"
                    data-broadcast-channel="{{ $notificationsDropdown['broadcastChannel'] }}"
                >
                    <button
                        type="button"
                        class="admin-dock__btn"
                        data-dock-toggle="notifications"
                        data-dock-tip="{{ __('general.notifications') }}"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="dockNotificationsMenu"
                        aria-label="{{ __('general.notifications') }}">
                        <span class="admin-dock__icon">
                            <i class="ti ti-bell"></i>
                            <span class="badge bg-danger rounded-pill badge-notifications d-none" data-notifications-badge>0</span>
                        </span>
                        <span class="admin-dock__label">{{ __('general.notifications') }}</span>
                    </button>
                    <div class="admin-dock__dropdown admin-dock__dropdown--notifications" id="dockNotificationsMenu" data-dock-panel="notifications" hidden>
                        <div class="admin-dock__notifications-header d-flex align-items-center px-3 py-2 border-bottom">
                            <h6 class="text-body mb-0 me-auto">{{ __('general.notifications') }}</h6>
                            <div class="d-flex align-items-center gap-2">
                                <a
                                    href="javascript:void(0)"
                                    class="small text-primary text-nowrap"
                                    data-notifications-see-all
                                >{{ __('general.see_all') }}</a>
                                <a
                                    href="javascript:void(0)"
                                    class="text-body"
                                    data-notifications-refresh
                                    title="{{ __('general.refresh') }}"
                                ><i class="ti ti-refresh fs-4"></i></a>
                                <a
                                    href="javascript:void(0)"
                                    class="text-body"
                                    data-notifications-mark-all
                                    title="{{ __('general.mark_all_as_read') }}"
                                ><i class="ti ti-mail-opened fs-4"></i></a>
                            </div>
                        </div>
                        <div class="dropdown-notifications-list scrollable-container admin-dock__notifications-list">
                            <ul class="list-group list-group-flush" data-notifications-list>
                                <li class="list-group-item text-center text-muted py-4" data-notifications-empty>
                                    {{ __('general.no_unread_notifications') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="admin-dock__divider" aria-hidden="true"></div>
                @endif
                @endauth

                <div class="admin-dock__locale" data-dock-locale-wrap>
                    <button
                        type="button"
                        class="admin-dock__btn"
                        data-dock-toggle="locale"
                        data-dock-tip="{{ __('general.language') }}"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="dockLocaleMenu"
                        aria-label="{{ __('general.language') }}">
                        <span class="admin-dock__icon admin-dock__icon--flag">
                            <i class="fi fi-{{ $localeSwitcher['currentLocaleFlag'] }} fis rounded-circle"></i>
                        </span>
                        <span class="admin-dock__label">{{ $localeSwitcher['currentLocaleNative'] }}</span>
                    </button>
                    <div class="admin-dock__dropdown admin-dock__dropdown--locale" id="dockLocaleMenu" data-dock-panel="locale" hidden role="menu">
                        @foreach ($localeSwitcher['localeOptions'] as $localeOption)
                            <a
                                class="admin-dock__dropdown-item{{ $localeOption['active'] ? ' is-active' : '' }}"
                                role="menuitem"
                                href="{{ $localeOption['url'] }}"
                                hreflang="{{ $localeOption['code'] }}"
                                @if ($localeOption['active']) aria-current="true" @endif>
                                <i class="fi fi-{{ $localeOption['flag'] }} fis rounded-circle me-2"></i>
                                {{ $localeOption['native'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="admin-dock__divider" aria-hidden="true"></div>

                <div class="admin-dock__theme" data-dock-theme-wrap>
                    <button
                        type="button"
                        class="admin-dock__btn"
                        data-dock-toggle="theme"
                        data-dock-tip="{{ __('general.theme') }}"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="dockThemeMenu"
                        aria-label="{{ __('general.theme') }}">
                        <span class="admin-dock__icon"><i class="ti ti-sun" data-dock-theme-icon></i></span>
                        <span class="admin-dock__label">{{ __('general.theme') }}</span>
                    </button>
                    <div class="admin-dock__dropdown admin-dock__dropdown--theme" id="dockThemeMenu" data-dock-panel="theme" hidden role="menu">
                        <button type="button" class="admin-dock__dropdown-item" role="menuitem" data-dock-theme="light">
                            <i class="ti ti-sun me-2"></i>{{ __('general.light_mode') }}
                        </button>
                        <button type="button" class="admin-dock__dropdown-item" role="menuitem" data-dock-theme="dark">
                            <i class="ti ti-moon me-2"></i>{{ __('general.dark_mode') }}
                        </button>
                        <button type="button" class="admin-dock__dropdown-item" role="menuitem" data-dock-theme="system">
                            <i class="ti ti-device-desktop me-2"></i>{{ __('general.system_mode') }}
                        </button>
                    </div>
                </div>

                <div class="admin-dock__divider" aria-hidden="true"></div>

                <div class="admin-dock__profile" data-dock-profile>
                    <button
                        type="button"
                        class="admin-dock__btn admin-dock__btn--profile"
                        data-dock-toggle="profile"
                        data-dock-tip="{{ $vendorDisplayName }}"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="dockProfileMenu"
                        aria-label="{{ $vendorDisplayName }}">
                        <span class="admin-dock__avatar">
                            <img src="{{ $vendorAvatar }}" alt="">
                        </span>
                        <span class="admin-dock__label">{{ $vendorDisplayName }}</span>
                    </button>
                    <div class="admin-dock__dropdown admin-dock__dropdown--profile" id="dockProfileMenu" data-dock-panel="profile" hidden role="menu">
                        <div class="admin-dock__dropdown-item" role="menuitem">
                            <i class="ti ti-user me-2"></i>{{ $vendorDisplayName }}
                        </div>
                        <div class="admin-dock__dropdown-divider"></div>
                        <button type="button" class="admin-dock__dropdown-item text-danger" role="menuitem" onclick="logout()">
                            <i class="ti ti-logout me-2"></i>{{ __('general.log_out') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-dock__mobile d-md-none" aria-hidden="false">
        <button
            type="button"
            class="admin-dock__mobile-menu btn"
            data-dock-open-drawer
            aria-haspopup="dialog"
            aria-controls="adminDockDrawer"
            aria-expanded="false"
            aria-label="{{ __('general.menu') }}">
            <i class="ti ti-menu-2"></i>
        </button>

        <a
            href="{{ route('vendor.dashboard.index') }}"
            class="admin-dock__mobile-brand"
            aria-label="{{ $dockTitle }}">
            <img src="{{ $dockLogo }}" alt="{{ $dockTitle }}">
        </a>

        <div class="admin-dock__mobile-actions">
            @auth('vendor')
            @php($notificationsDropdownMobile = notificationsDropdownConfig('vendor'))
            @if ($notificationsDropdownMobile)
            <div
                class="admin-dock__mobile-notifications position-relative"
                data-notifications-root
                data-notifications-guard="vendor"
                data-notifications-index-url="{{ $notificationsDropdownMobile['notificationsIndexUrl'] }}"
                data-notifications-mark-all-url="{{ $notificationsDropdownMobile['notificationsMarkAllUrl'] }}"
                data-notifications-mark-read-url-template="{{ $notificationsDropdownMobile['notificationsMarkReadUrlTemplate'] }}"
                data-notifications-destroy-url-template="{{ $notificationsDropdownMobile['notificationsDestroyUrlTemplate'] }}"
                data-broadcast-auth-url="{{ $notificationsDropdownMobile['broadcastAuthUrl'] }}"
                data-broadcast-channel="{{ $notificationsDropdownMobile['broadcastChannel'] }}"
            >
                <button
                    type="button"
                    class="admin-dock__mobile-locale-btn btn position-relative"
                    data-dock-toggle="notifications-mobile"
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-controls="dockNotificationsMenuMobile"
                    aria-label="{{ __('general.notifications') }}">
                    <i class="ti ti-bell"></i>
                    <span class="badge bg-danger rounded-pill badge-notifications d-none" data-notifications-badge>0</span>
                </button>
                <div class="admin-dock__dropdown admin-dock__dropdown--notifications" id="dockNotificationsMenuMobile" data-dock-panel="notifications-mobile" hidden>
                    <div class="admin-dock__notifications-header d-flex align-items-center px-3 py-2 border-bottom">
                        <h6 class="text-body mb-0 me-auto">{{ __('general.notifications') }}</h6>
                        <div class="d-flex align-items-center gap-2">
                            <a href="javascript:void(0)" class="small text-primary text-nowrap" data-notifications-see-all>{{ __('general.see_all') }}</a>
                            <a href="javascript:void(0)" class="text-body" data-notifications-refresh title="{{ __('general.refresh') }}"><i class="ti ti-refresh fs-4"></i></a>
                            <a href="javascript:void(0)" class="text-body" data-notifications-mark-all title="{{ __('general.mark_all_as_read') }}"><i class="ti ti-mail-opened fs-4"></i></a>
                        </div>
                    </div>
                    <div class="dropdown-notifications-list scrollable-container admin-dock__notifications-list">
                        <ul class="list-group list-group-flush" data-notifications-list>
                            <li class="list-group-item text-center text-muted py-4" data-notifications-empty>
                                {{ __('general.no_unread_notifications') }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            @endif
            @endauth

            <button
                type="button"
                class="admin-dock__mobile-locale-btn btn"
                data-dock-toggle="locale-mobile"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="dockLocaleMenuMobile"
                aria-label="{{ __('general.language') }}">
                <i class="fi fi-{{ $localeSwitcher['currentLocaleFlag'] }} fis rounded-circle"></i>
            </button>
            <div class="admin-dock__dropdown admin-dock__dropdown--locale" id="dockLocaleMenuMobile" data-dock-panel="locale-mobile" hidden role="menu">
                @foreach ($localeSwitcher['localeOptions'] as $localeOption)
                    <a
                        class="admin-dock__dropdown-item{{ $localeOption['active'] ? ' is-active' : '' }}"
                        role="menuitem"
                        href="{{ $localeOption['url'] }}"
                        hreflang="{{ $localeOption['code'] }}"
                        @if ($localeOption['active']) aria-current="true" @endif>
                        <i class="fi fi-{{ $localeOption['flag'] }} fis rounded-circle me-2"></i>
                        {{ $localeOption['native'] }}
                    </a>
                @endforeach
            </div>

            <button
                type="button"
                class="admin-dock__mobile-theme-btn btn"
                data-dock-toggle="theme-mobile"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="dockThemeMenuMobile"
                aria-label="{{ __('general.theme') }}">
                <i class="ti ti-sun" data-dock-theme-icon></i>
            </button>
            <div class="admin-dock__dropdown admin-dock__dropdown--theme" id="dockThemeMenuMobile" data-dock-panel="theme-mobile" hidden role="menu">
                <button type="button" class="admin-dock__dropdown-item" role="menuitem" data-dock-theme="light">
                    <i class="ti ti-sun me-2"></i>{{ __('general.light_mode') }}
                </button>
                <button type="button" class="admin-dock__dropdown-item" role="menuitem" data-dock-theme="dark">
                    <i class="ti ti-moon me-2"></i>{{ __('general.dark_mode') }}
                </button>
                <button type="button" class="admin-dock__dropdown-item" role="menuitem" data-dock-theme="system">
                    <i class="ti ti-device-desktop me-2"></i>{{ __('general.system_mode') }}
                </button>
            </div>

            <button
                type="button"
                class="admin-dock__mobile-profile btn"
                data-dock-toggle="profile-mobile"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="dockProfileMenuMobile"
                aria-label="{{ $vendorDisplayName }}">
                <img src="{{ $vendorAvatar }}" alt="" class="rounded-circle">
            </button>
        </div>

        <div class="admin-dock__dropdown admin-dock__dropdown--mobile-profile" id="dockProfileMenuMobile" data-dock-panel="profile-mobile" hidden role="menu">
            <div class="admin-dock__dropdown-item" role="menuitem">
                <i class="ti ti-user me-2"></i>{{ $vendorDisplayName }}
            </div>
            <div class="admin-dock__dropdown-divider"></div>
            <button type="button" class="admin-dock__dropdown-item text-danger" role="menuitem" onclick="logout()">
                <i class="ti ti-logout me-2"></i>{{ __('general.log_out') }}
            </button>
        </div>
    </div>
</nav>

<div class="admin-dock-drawer" id="adminDockDrawer" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-label="{{ __('general.navigation_menu') }}">
    <div class="admin-dock-drawer__backdrop" data-dock-close-drawer></div>
    <div class="admin-dock-drawer__sheet" data-dock-sheet>
        <div class="admin-dock-drawer__handle" data-dock-sheet-handle aria-hidden="true"></div>
        <div class="admin-dock-drawer__header">
            <a href="{{ route('vendor.dashboard.index') }}" class="admin-dock-drawer__brand d-flex align-items-center text-body text-decoration-none" aria-label="{{ $dockTitle }}">
                <img src="{{ $dockLogo }}" alt="{{ $dockTitle }}" style="max-height: 28px; width: auto;">
            </a>
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
