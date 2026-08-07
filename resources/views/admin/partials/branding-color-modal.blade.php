<div class="modal fade" id="brandingColorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-simple">
        <div class="modal-content p-3 p-md-5">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4">
                    <h3 class="mb-2">{!! __('general.pick_color') !!}</h3>
                </div>

                <div id="brandingColorModalPreview" class="rounded border mb-3" style="height: 56px;"></div>
                <div id="brandingColorModalPicker" class="d-flex justify-content-center"></div>

                <div class="modal-footer px-0">
                    <button type="button" class="btn btn-label-secondary" id="brandingColorModalCancel">{!! __('general.cancel') !!}</button>
                    <button type="button" class="btn btn-primary" id="brandingColorModalSet">{!! __('general.save') !!}</button>
                </div>
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
        max-width: 100%;
        box-sizing: border-box;
        padding-inline: 0.5rem;
        overflow: hidden;
    }

    #brandingColorModalPicker .IroColorPicker {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
    }

    #brandingColorModalPicker .IroSlider,
    #brandingColorModalPicker .IroSliderGradient {
        max-width: 100% !important;
        box-sizing: border-box;
    }
</style>
