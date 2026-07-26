<div
    class="modal fade"
    id="adminAppearanceModal"
    tabindex="-1"
    aria-labelledby="adminAppearanceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminAppearanceModalLabel">{{ __('general.menu_appearance') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('general.cancel') }}"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4 pb-3 border-bottom admin-appearance-dock-row">
                    <div class="admin-appearance-dock-row__text">
                            <h6 class="mb-1">{{ __('general.dock_mode') }}</h6>
                            <p class="text-muted small mb-0">{{ __('general.dock_mode_description') }}</p>
                    </div>
                    <div class="admin-appearance-dock-toggle">
                        <label class="switch switch-primary mb-0">
                            <input type="checkbox" class="switch-input" id="appearanceDockMode" data-appearance-dock-mode>
                            <span class="switch-toggle-slider">
                                <span class="switch-on"><i class="ti ti-check"></i></span>
                                <span class="switch-off"><i class="ti ti-x"></i></span>
                            </span>
                        </label>
                    </div>
                </div>

                <div>
                    <h6 class="mb-1">{{ __('general.card_style') }}</h6>
                    <p class="text-muted small mb-3">{{ __('general.card_style_description') }}</p>

                    <div class="row g-3 admin-appearance-style-row">
                        <div class="col-md-6">
                            <label class="admin-appearance-option w-100 mb-0">
                                <input type="radio" name="appearance_card_style" value="classic" class="admin-appearance-option__input" data-appearance-card-style>
                                <span class="admin-appearance-option__card">
                                    <span class="admin-appearance-option__preview admin-appearance-option__preview--classic">
                                        <span class="admin-appearance-option__preview-bar"></span>
                                        <span class="admin-appearance-option__preview-line"></span>
                                        <span class="admin-appearance-option__preview-line admin-appearance-option__preview-line--short"></span>
                                    </span>
                                    <span class="admin-appearance-option__title">{{ __('general.card_style_classic') }}</span>
                                    <span class="admin-appearance-option__desc">{{ __('general.card_style_classic_description') }}</span>
                                </span>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="admin-appearance-option w-100 mb-0">
                                <input type="radio" name="appearance_card_style" value="glass" class="admin-appearance-option__input" data-appearance-card-style>
                                <span class="admin-appearance-option__card">
                                    <span class="admin-appearance-option__preview admin-appearance-option__preview--glass">
                                        <span class="admin-appearance-option__preview-bar"></span>
                                        <span class="admin-appearance-option__preview-line"></span>
                                        <span class="admin-appearance-option__preview-line admin-appearance-option__preview-line--short"></span>
                                    </span>
                                    <span class="admin-appearance-option__title">{{ __('general.card_style_glass') }}</span>
                                    <span class="admin-appearance-option__desc">{{ __('general.card_style_glass_description') }}</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="appearanceApplyBtn" data-appearance-apply>{{ __('general.apply') }}</button>
            </div>
        </div>
    </div>
</div>
