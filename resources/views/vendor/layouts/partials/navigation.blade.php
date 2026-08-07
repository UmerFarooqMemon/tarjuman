<nav
    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-sm"></i>
        </a>
    </div>
    <div class="navbar-nav-right d-flex align-items-center justify-content-between w-100" id="navbar-collapse">
        <div class="navbar-nav align-items-center">
            <span class="nav-item fw-medium text-heading text-truncate px-2">
                {{ __('general.vendor_dashboard_welcome', ['name' => auth('vendor')->user()?->fullName() ?? '']) }}
            </span>
        </div>
        <ul class="navbar-nav flex-row align-items-center ms-auto">
            @php($localeSwitcher = adminLocaleSwitcher())
            @auth('vendor')
                @php($notificationsDropdown = notificationsDropdownConfig('vendor'))
                @if ($notificationsDropdown)
                    @include('partials.notifications-dropdown', $notificationsDropdown)
                @endif
            @endauth
            <li class="nav-item dropdown-locale dropdown me-2 me-xl-0">
                <a
                    class="nav-link dropdown-toggle hide-arrow"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="{{ __('general.language') }}">
                    <i class="fi fi-{{ $localeSwitcher['currentLocaleFlag'] }} fis rounded-circle fs-3 me-1"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach ($localeSwitcher['localeOptions'] as $localeOption)
                        <li>
                            <a
                                class="dropdown-item{{ $localeOption['active'] ? ' active' : '' }}"
                                href="{{ $localeOption['url'] }}"
                                hreflang="{{ $localeOption['code'] }}"
                                @if ($localeOption['active']) aria-current="true" @endif>
                                <i class="fi fi-{{ $localeOption['flag'] }} fis rounded-circle me-2 fs-3"></i>
                                <span class="align-middle">{{ $localeOption['native'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>

            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        @if (auth('vendor')->user()?->image && file_exists(uploadsDir('front') . auth('vendor')->user()->image))
                        <img src="{!! asset(uploadsDir('front') . auth('vendor')->user()->image) !!}" alt class="h-auto rounded-circle" />
                        @else
                        <img src="{!! asset('assets/img/avatars/1.png') !!}" alt class="h-auto rounded-circle" />
                        @endif
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        @if (auth('vendor')->user()?->image && file_exists(uploadsDir('front') . auth('vendor')->user()->image))
                                        <img src="{!! asset(uploadsDir('front') . auth('vendor')->user()->image) !!}" alt class="h-auto rounded-circle" />
                                        @else
                                        <img src="{!! asset('assets/img/avatars/1.png') !!}" alt class="h-auto rounded-circle" />
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-medium d-block">{{ auth('vendor')->user()?->fullName() }}</span>
                                    <small class="text-muted">{{ __('general.vendor') }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li><div class="dropdown-divider"></div></li>
                    <li>
                        <a class="dropdown-item" href="javascript:;" onclick="logout()">
                            <i class="ti ti-logout me-2 ti-sm"></i>
                            <span class="align-middle">{!! __('general.log_out') !!}</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
