@php
    /** @var array $details */
    $currency = $details['currency'] ?? $order->currency;
@endphp
<div class="row g-4">
    <div class="col-lg-8 min-w-0">
        <div class="card h-100">
            <div class="card-body">
                <div class="vendor-order-detail">
                    <div class="vendor-order-detail__hero d-flex align-items-start gap-3">
                        <div class="vendor-order-detail__thumb"><i class="ti ti-file-certificate"></i></div>
                        <div class="min-w-0">
                            <h4 class="mb-0 text-break">{{ $details['document_type'] }}</h4>
                        </div>
                    </div>

                    @if ($details['notes'])
                        <div class="vendor-order-detail__notes">
                            <div class="fw-semibold mb-1">{{ __('general.order_notes') }}</div>
                            <div class="text-break">{{ $details['notes'] }}</div>
                        </div>
                    @endif

                    <div class="row g-3 mt-1">
                        <div class="col-md-6 min-w-0">
                            <div class="vendor-order-detail__meta">
                                <i class="ti ti-calendar-event"></i>
                                <span class="fw-medium vendor-order-datetime" dir="ltr">{{ $details['posted_at'] }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 min-w-0">
                            <div class="vendor-order-detail__meta">
                                <i class="ti ti-truck-delivery"></i>
                                <div class="fw-medium text-break">{{ $details['delivery_label'] ?? $details['delivery_name'] }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="vendor-order-detail__grid vendor-order-detail__grid--triple">
                        <div class="vendor-order-detail__info">
                            <span class="vendor-order-detail__info-icon vendor-order-detail__info-icon--lang"><i class="ti ti-language-hiragana"></i></span>
                            <div class="fw-medium text-break">{{ $details['language_pair'] }}</div>
                        </div>
                        <div class="vendor-order-detail__info">
                            <span class="vendor-order-detail__info-icon"><i class="ti ti-file-text"></i></span>
                            <div class="fw-medium">{{ $details['pages_label'] }}</div>
                        </div>
                        <div class="vendor-order-detail__info">
                            <span class="vendor-order-detail__info-icon"><i class="ti ti-alphabet-latin"></i></span>
                            <div class="fw-medium">{{ $details['words_label'] }}</div>
                        </div>
                    </div>

                    @if (! empty($details['add_ons']))
                        <div class="vendor-order-detail__section">
                            <div class="fw-semibold mb-2">{{ __('general.add_ons_amount') }}</div>
                            <div class="vendor-order-addon-list">
                                @foreach ($details['add_ons'] as $addOn)
                                    <span class="badge vendor-order-addon-badge">{{ $addOn['name'] }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 min-w-0">
        <div class="card vendor-order-summary h-100">
            <div class="card-body">
                <h5 class="mb-3">{{ __('general.order_summary') }}</h5>

                @php
                    $currencyHtml = $details['currency_html'] ?? currencyIconHtml($currency);
                    $moneyRow = static function (float $amount, string $currencyHtml, bool $minus = false): string {
                        $value = abs($amount);

                        return '<span class="d-inline-flex align-items-center gap-1 vendor-order-money'.($minus ? ' text-danger' : '').'">'
                            .($minus ? '<span>-</span>' : '')
                            .$currencyHtml
                            .'<span>'.number_format($value, 2, '.', ',').'</span>'
                            .'</span>';
                    };
                @endphp
                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                    <span class="text-muted flex-shrink-0">{{ __('general.order_amount') }}</span>
                    <span class="text-end min-w-0">{!! $moneyRow((float) $details['amounts']['order'], $currencyHtml) !!}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                    <span class="text-muted flex-shrink-0">{{ __('general.delivery_amount') }}</span>
                    <span class="text-end min-w-0">{!! $moneyRow((float) $details['amounts']['delivery'], $currencyHtml) !!}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                    <span class="text-muted flex-shrink-0">{{ __('general.add_ons_amount') }}</span>
                    <span class="text-end min-w-0">{!! $moneyRow((float) $details['amounts']['add_ons'], $currencyHtml) !!}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                    <span class="text-muted flex-shrink-0">{{ __('general.platform_charges') }}</span>
                    <span class="text-end min-w-0">{!! $moneyRow((float) $details['amounts']['platform_fee'], $currencyHtml, true) !!}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center gap-2 fw-semibold mb-2">
                    <span class="flex-shrink-0">{{ __('general.order_total') }}</span>
                    <span class="text-end min-w-0">{!! $moneyRow((float) $details['amounts']['total'], $currencyHtml) !!}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                    <span class="text-muted flex-shrink-0">{{ __('general.vendor_amount') }}</span>
                    <span class="text-end min-w-0">{!! $moneyRow((float) $details['amounts']['vendor_amount'], $currencyHtml) !!}</span>
                </div>
                <p class="text-muted small">{{ __('general.order_fee_breakdown_hint') }}</p>

                @if ($order->payment_link_url)
                    <a href="{{ $order->payment_link_url }}" target="_blank" rel="noopener" class="btn btn-outline-primary w-100 mb-3">
                        {{ __('general.payment_link') }}
                    </a>
                @endif

                @if ($canSendPaymentLink ?? false)
                    <form method="POST" action="{{ route('vendor.orders.send-payment-link', $order) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            {{ $order->payment_link_url
                                ? __('general.resend_payment_link')
                                : __('general.send_payment_link') }}
                        </button>
                        <p class="text-muted small mt-2 mb-0">{{ __('general.send_payment_link_hint') }}</p>
                    </form>
                @endif

                @if ($canConfirm ?? false)
                    <hr>
                    <form method="POST" action="{{ route('vendor.orders.confirm', $order) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="confirmed_amount">{{ __('general.order_total') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">{!! currencyIconHtml($currency) !!}</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="confirmed_amount"
                                    id="confirmed_amount"
                                    class="form-control"
                                    value="{{ old('confirmed_amount', $order->estimate_amount) }}"
                                    required
                                    data-fee-percent="{{ $details['amounts']['fee_percent'] ?? 0 }}"
                                >
                            </div>
                            <template data-currency-icon>{!! currencyIconHtml($currency) !!}</template>
                            <div class="border rounded p-3 mt-2 bg-light" data-fee-preview>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>{{ __('general.vendor_amount') }}</span>
                                    <strong data-fee-vendor class="d-inline-flex align-items-center gap-1">—</strong>
                                </div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>{{ __('general.platform_charges') }}</span>
                                    <strong data-fee-platform class="d-inline-flex align-items-center gap-1">—</strong>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span>{{ __('general.order_total') }}</span>
                                    <strong data-fee-total class="d-inline-flex align-items-center gap-1">—</strong>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="vendor_note">{{ __('general.vendor_note') }}</label>
                            <textarea name="vendor_note" id="vendor_note" rows="3" class="form-control">{{ old('vendor_note', $order->vendor_note) }}</textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button
                                type="submit"
                                name="confirm_action"
                                value="with_payment_link"
                                class="btn btn-primary"
                            >{{ __('general.confirm_and_send_payment_link') }}</button>
                            <button
                                type="submit"
                                name="confirm_action"
                                value="amount_only"
                                class="btn btn-secondary"
                            >{{ __('general.confirm_amount_only') }}</button>
                        </div>
                        <p class="text-muted small mt-2 mb-0">{{ __('general.confirm_amount_only_hint') }}</p>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@php
    $sourceDocuments = $details['source_documents'] ?? [];
    $deliveryDocuments = $details['delivery_documents'] ?? [];
    $canManageDelivery = ($canManageDelivery ?? false) || ! empty($details['can_manage_delivery']);
    $canComplete = ($canComplete ?? false) || ! empty($details['can_complete']);
    $orderAddOns = collect($details['add_ons'] ?? [])->filter(fn ($addOn) => (int) ($addOn['id'] ?? 0) > 0)->values();
@endphp

@if ($canAccessDocuments ?? false)
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">{{ __('general.order_source_documents') }}</h5>
            <small class="text-muted">{{ __('general.order_documents_review_hint') }}</small>
        </div>
        <div class="card-body">
            @if (count($sourceDocuments) > 0)
                <div class="vendor-order-docs">
                    @foreach ($sourceDocuments as $document)
                        @include('vendor.orders.partials.document-row', ['document' => $document, 'canDelete' => false])
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-0">{{ __('general.no_documents_found') }}</p>
            @endif
            <p class="small text-muted mt-3 mb-0">{{ __('general.order_document_security_note') }}</p>
        </div>
    </div>

    @if ($canManageDelivery || count($deliveryDocuments) > 0 || ($order->status ?? null) === \App\Models\Order::STATUS_COMPLETED)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('general.order_translated_documents') }}</h5>
                <small class="text-muted">{{ __('general.order_translated_documents_hint') }}</small>
            </div>
            <div class="card-body">
                @if (count($deliveryDocuments) > 0)
                    <div class="vendor-order-docs mb-3">
                        @foreach ($deliveryDocuments as $document)
                            @include('vendor.orders.partials.document-row', [
                                'document' => $document,
                                'canDelete' => $canManageDelivery && ! empty($document['delete_url']),
                            ])
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">{{ __('general.order_no_translated_documents') }}</p>
                @endif

                @if ($canManageDelivery)
                    <form
                        method="POST"
                        action="{{ route('vendor.orders.documents.store', $order) }}"
                        enctype="multipart/form-data"
                        class="border rounded p-3 mb-4"
                    >
                        @csrf
                        <label class="form-label" for="delivery_documents">{{ __('general.upload_translated_documents') }}</label>
                        <input
                            type="file"
                            name="documents[]"
                            id="delivery_documents"
                            class="form-control"
                            multiple
                            required
                            accept=".pdf,.docx,.jpg,.jpeg,.png"
                        >
                        <small class="text-muted d-block mt-1">{{ __('general.upload_translated_documents_hint') }}</small>
                        <button type="submit" class="btn btn-secondary mt-3">{{ __('general.upload') }}</button>
                    </form>

                    @if ($canComplete)
                        <form method="POST" action="{{ route('vendor.orders.complete', $order) }}" class="border rounded p-3">
                            @csrf
                            <h6 class="mb-2">{{ __('general.mark_order_complete') }}</h6>
                            <p class="text-muted small mb-3">{{ __('general.mark_order_complete_hint') }}</p>

                            @if ($orderAddOns->isNotEmpty())
                                <div class="mb-3">
                                    <div class="fw-semibold mb-2">{{ __('general.confirm_addons_completed') }}</div>
                                    @foreach ($orderAddOns as $addOn)
                                        <div class="form-check mb-2">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="completed_add_ons[]"
                                                value="{{ $addOn['id'] }}"
                                                id="addon_complete_{{ $addOn['id'] }}"
                                                @checked(! empty($addOn['completed']) || collect(old('completed_add_ons', []))->contains($addOn['id']))
                                                required
                                            >
                                            <label class="form-check-label vendor-order-addon-check" for="addon_complete_{{ $addOn['id'] }}">
                                                <span class="vendor-order-addon-check__name">{{ $addOn['name'] }}</span>
                                                @if (! empty($addOn['amount_html']))
                                                    <span class="vendor-order-addon-check__sep" aria-hidden="true">·</span>
                                                    <span class="vendor-order-addon-check__price">{!! $addOn['amount_html'] !!}</span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="form-check mb-3">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="confirm_delivery_ready"
                                    value="1"
                                    id="confirm_delivery_ready"
                                    @checked(old('confirm_delivery_ready'))
                                    required
                                >
                                <label class="form-check-label" for="confirm_delivery_ready">
                                    {{ __('general.confirm_delivery_ready') }}
                                </label>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary"
                                @disabled(count($deliveryDocuments) < 1)
                            >{{ __('general.submit_and_complete_order') }}</button>
                            @if (count($deliveryDocuments) < 1)
                                <p class="text-muted small mt-2 mb-0">{{ __('general.order_delivery_documents_required') }}</p>
                            @endif
                        </form>
                    @endif
                @endif
            </div>
        </div>
    @endif
@endif
