@php
    /** @var string $notificationsIndexUrl */
    /** @var string $notificationsMarkAllUrl */
    /** @var string $notificationsMarkReadUrlTemplate  e.g. /admin/notifications/__ID__/read */
    /** @var string $notificationsDestroyUrlTemplate */
    /** @var string $broadcastAuthUrl */
    /** @var string $broadcastChannel  e.g. App.Models.Admin.1 */
    /** @var string|null $userId */
@endphp
<li
    class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1"
    data-notifications-root
    data-notifications-guard="{{ $guard ?? 'admin' }}"
    data-notifications-index-url="{{ $notificationsIndexUrl }}"
    data-notifications-mark-all-url="{{ $notificationsMarkAllUrl }}"
    data-notifications-mark-read-url-template="{{ $notificationsMarkReadUrlTemplate }}"
    data-notifications-destroy-url-template="{{ $notificationsDestroyUrlTemplate }}"
    data-broadcast-auth-url="{{ $broadcastAuthUrl }}"
    data-broadcast-channel="{{ $broadcastChannel }}"
>
    <a
        class="nav-link dropdown-toggle hide-arrow"
        href="javascript:void(0);"
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        aria-expanded="false"
        aria-label="{{ __('general.notifications') }}">
        <span class="position-relative d-inline-flex">
            <i class="ti ti-bell ti-md"></i>
            <span class="badge bg-danger rounded-pill badge-notifications d-none" data-notifications-badge>0</span>
        </span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end py-0">
        <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
                <h5 class="text-body mb-0 me-auto">{{ __('general.notifications') }}</h5>
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
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ __('general.refresh') }}"
                    ><i class="ti ti-refresh fs-4"></i></a>
                    <a
                        href="javascript:void(0)"
                        class="dropdown-notifications-all text-body"
                        data-notifications-mark-all
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ __('general.mark_all_as_read') }}"
                    ><i class="ti ti-mail-opened fs-4"></i></a>
                </div>
            </div>
        </li>
        <li class="dropdown-notifications-list scrollable-container">
            <ul class="list-group list-group-flush" data-notifications-list>
                <li class="list-group-item text-center text-muted py-4" data-notifications-empty>
                    {{ __('general.no_unread_notifications') }}
                </li>
            </ul>
        </li>
    </ul>
</li>
