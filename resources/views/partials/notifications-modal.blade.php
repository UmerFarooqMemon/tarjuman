{{-- Shared admin/vendor “See all” notifications modal --}}
<div
    class="modal fade"
    id="notificationsAllModal"
    tabindex="-1"
    aria-labelledby="notificationsAllModalLabel"
    aria-hidden="true"
    data-notifications-modal
>
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content notifications-all-modal__content">
            <div class="notifications-all-modal__chrome">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('general.cancel') }}"></button>
            </div>
            <div class="modal-body notifications-all-modal__body">
                <div class="notifications-all-modal__header text-center">
                    <h3 class="mb-1" id="notificationsAllModalLabel">{{ __('general.notifications') }}</h3>
                    <p class="text-muted small mb-0">{{ __('general.notifications_modal_hint') }}</p>
                </div>

                <ul class="nav nav-tabs notifications-all-modal__tabs" role="tablist" data-notifications-modal-tabs>
                    <li class="nav-item" role="presentation">
                        <button
                            type="button"
                            class="nav-link active"
                            id="notifications-tab-unread"
                            data-bs-toggle="tab"
                            data-bs-target="#notifications-pane-unread"
                            data-notifications-tab="unread"
                            role="tab"
                            aria-controls="notifications-pane-unread"
                            aria-selected="true"
                        >
                            {{ __('general.notifications_tab_unread') }}
                            <span class="badge bg-label-primary ms-1 d-none" data-notifications-modal-unread-badge>0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button
                            type="button"
                            class="nav-link"
                            id="notifications-tab-read"
                            data-bs-toggle="tab"
                            data-bs-target="#notifications-pane-read"
                            data-notifications-tab="read"
                            role="tab"
                            aria-controls="notifications-pane-read"
                            aria-selected="false"
                        >
                            {{ __('general.notifications_tab_read') }}
                        </button>
                    </li>
                </ul>

                <div class="tab-content notifications-all-modal__tab-content">
                    <div
                        class="tab-pane fade show active notifications-all-modal__pane"
                        id="notifications-pane-unread"
                        role="tabpanel"
                        aria-labelledby="notifications-tab-unread"
                        tabindex="0"
                    >
                        <div class="notifications-all-modal__toolbar">
                            <a href="javascript:void(0)" class="small" data-notifications-mark-all>
                                {{ __('general.mark_all_as_read') }}
                            </a>
                        </div>
                        <ul class="list-group list-group-flush notifications-modal-list" data-notifications-modal-list="unread">
                            <li class="list-group-item text-center text-muted py-4" data-notifications-modal-empty="unread">
                                {{ __('general.no_unread_notifications') }}
                            </li>
                        </ul>
                    </div>
                    <div
                        class="tab-pane fade notifications-all-modal__pane"
                        id="notifications-pane-read"
                        role="tabpanel"
                        aria-labelledby="notifications-tab-read"
                        tabindex="0"
                    >
                        <ul class="list-group list-group-flush notifications-modal-list" data-notifications-modal-list="read">
                            <li class="list-group-item text-center text-muted py-4" data-notifications-modal-empty="read">
                                {{ __('general.no_read_notifications') }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
