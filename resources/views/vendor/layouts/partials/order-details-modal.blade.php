@php
    $orderModalLabels = [
        'document_type' => __('general.document_type'),
        'posted' => __('general.posted'),
        'delivery' => __('general.delivery_type'),
        'notes' => __('general.notes_remarks'),
        'order_notes' => __('general.order_notes'),
        'no_notes' => __('general.no_notes'),
        'authority' => __('general.authority'),
        'language_pair' => __('general.language_pair'),
        'pages' => __('general.pages'),
        'words' => __('general.words'),
        'add_ons' => __('general.add_ons_amount'),
        'no_add_ons' => __('general.no_add_ons'),
        'order_summary' => __('general.order_summary'),
        'order_amount' => __('general.order_amount'),
        'delivery_amount' => __('general.delivery_amount'),
        'add_ons_amount' => __('general.add_ons_amount'),
        'platform_fee' => __('general.platform_charges'),
        'order_total' => __('general.order_total'),
        'accept' => __('general.accept'),
        'open_order' => __('general.open_order'),
        'order_details' => __('general.order_details'),
        'loading' => __('general.loading'),
        'load_failed' => __('general.order_details_load_failed'),
        'already_taken' => __('general.order_already_taken'),
        'already_taken_title' => __('general.order_already_accepted_title'),
        'order_documents' => __('general.order_documents'),
        'no_documents' => __('general.no_documents_found'),
        'preview' => __('general.preview'),
        'download' => __('general.download'),
    ];
@endphp
<div
    class="modal fade"
    id="vendorOrderDetailsModal"
    tabindex="-1"
    aria-labelledby="vendorOrderDetailsModalLabel"
    aria-hidden="true"
    data-labels='@json($orderModalLabels)'>
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content vendor-order-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="vendorOrderDetailsModalLabel" data-order-modal-title>{{ __('general.order_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('general.cancel') }}"></button>
            </div>
            <div class="modal-body vendor-order-modal__body" data-order-modal-body>
                <div class="text-center text-muted py-5">{{ __('general.loading') }}</div>
            </div>
        </div>
    </div>
</div>
