<div class="modal fade" id="brandingColorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{!! __('general.pick_color') !!}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="brandingColorModalPreview" class="rounded border mb-3" style="height: 56px;"></div>
                <div id="brandingColorModalPicker" class="d-flex justify-content-center"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" id="brandingColorModalCancel">{!! __('general.cancel') !!}</button>
                <button type="button" class="btn btn-primary" id="brandingColorModalSet">{!! __('general.save') !!}</button>
            </div>
        </div>
    </div>
</div>

<style>
    .branding-slider-label {
        display: block;
        margin: 0.75rem 0 0.35rem;
        font-size: 0.8125rem;
        color: #697a8d;
    }

    #brandingColorModalPicker {
        width: 100%;
    }

    #brandingColorModalPicker .IroColorPicker {
        width: 100% !important;
    }
</style>
