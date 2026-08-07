<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\OrderDocument;
use App\Models\VendorUser;

class VendorOrderDetailsPresenter
{
    /**
     * Safe order details for vendor preview (open, assigned, or taken by another).
     * Never includes document binaries.
     *
     * @return array<string, mixed>
     */
    public function present(Order $order, bool $includeDocuments = false, ?VendorUser $viewer = null): array
    {
        $order->loadMissing([
            'estimate',
            'estimate.addOns.addOn.translations',
            'documentType.translations',
            'sourceLanguage.translations',
            'targetLanguage.translations',
            'deliverySpeed.translations',
            'addOns.addOn.translations',
        ]);

        if ($includeDocuments) {
            $order->loadMissing(['documents' => fn ($q) => $q->whereNull('purged_at')->latest()]);
        }

        $viewerVendorId = (int) ($viewer?->vendor_id ?? 0);
        $isOpen = $order->status === Order::STATUS_OPEN && ! $order->vendor_id;
        $isMine = $viewerVendorId > 0 && (int) $order->vendor_id === $viewerVendorId;
        $viewMode = $isOpen ? 'open' : ($isMine ? 'mine' : 'taken');
        $showSummary = $viewMode !== 'taken';

        $estimate = $order->estimate;
        $currency = $order->currency ?: platformCurrency();

        $translationAmount = (float) ($estimate?->translation_amount ?? 0);
        $deliveryAmount = (float) ($estimate?->delivery_speed_amount ?? 0);
        $addOnsAmount = (float) ($estimate?->add_ons_total ?? $order->addOns->sum('amount'));
        $subtotal = round($translationAmount + $deliveryAmount + $addOnsAmount, 2);
        $feeBreakdown = orderFeeBreakdown($subtotal);
        $platformFee = (float) $feeBreakdown['platform_fee'];
        $netTotal = round(max(0, $subtotal - $platformFee), 2);

        $delivery = $order->deliverySpeed;
        // Prefer live catalog labels for the active locale; fall back to estimate snapshots.
        $deliveryName = $delivery?->displayName()
            ?: ($estimate?->delivery_speed_name ?: '—');
        $deliveryDuration = $delivery?->displayDuration() ?: null;
        $deliveryLabel = $deliveryName;
        if (filled($deliveryDuration) && $deliveryName !== '—') {
            $deliveryLabel = trim($deliveryName.' '.$deliveryDuration);
        } elseif (filled($deliveryDuration)) {
            $deliveryLabel = $deliveryDuration;
        }

        $sourceName = $order->sourceLanguage?->displayName()
            ?: ($estimate?->source_language_name ?: '—');
        $targetName = $order->targetLanguage?->displayName()
            ?: ($estimate?->target_language_name ?: '—');

        $addOns = $order->addOns->isNotEmpty()
            ? $order->addOns
            : ($estimate?->addOns ?? collect());

        $documents = [];
        $sourceDocuments = [];
        $deliveryDocuments = [];
        if ($includeDocuments) {
            foreach ($order->documents as $document) {
                $row = [
                    'uuid' => $document->uuid,
                    'kind' => $document->kind,
                    'name' => $document->original_name,
                    'mime' => $document->mime,
                    'pages' => (int) ($document->pages ?? 0),
                    'words' => (int) ($document->words ?? 0),
                    'amount' => $document->amount !== null ? (float) $document->amount : null,
                    'amount_html' => $document->amount !== null
                        ? formatMoney($document->amount, $currency)
                        : null,
                    'preview_url' => route('vendor.orders.documents.preview', [$order, $document], false),
                    'download_url' => vendorDocumentDownloadAllowed()
                        ? route('vendor.orders.documents.download', [$order, $document], false)
                        : null,
                    'delete_url' => $document->kind === OrderDocument::KIND_DELIVERY
                        ? route('vendor.orders.documents.destroy', [$order, $document], false)
                        : null,
                ];
                $documents[] = $row;
                if ($document->kind === OrderDocument::KIND_DELIVERY) {
                    $deliveryDocuments[] = $row;
                } else {
                    $sourceDocuments[] = $row;
                }
            }
        }

        $canManageDelivery = $isMine && in_array($order->status, [
            Order::STATUS_ASSIGNED,
            Order::STATUS_IN_PROGRESS,
            Order::STATUS_AWAITING_CUSTOMER_PAYMENT,
        ], true);

        $pages = (int) ($order->page_count ?? $estimate?->page_count ?? 0);
        $words = (int) ($order->word_count ?? $estimate?->word_count ?? 0);

        $statusMessage = null;
        if ($viewMode === 'taken') {
            $statusMessage = filled($order->vendor_id)
                ? __('general.order_already_taken')
                : __('general.order_no_longer_available');
        }

        return [
            'order_id' => $order->order_id,
            'status' => $order->status,
            'status_label' => formatOrderStatus($order->status),
            'status_badge_html' => orderStatusBadge($order->status),
            'payment_status' => $order->payment_status,
            'payment_status_label' => formatOrderPaymentStatus($order->payment_status),
            'payment_status_badge_html' => orderPaymentStatusBadge($order->payment_status),
            'view_mode' => $viewMode,
            'show_summary' => $showSummary,
            'status_message' => $statusMessage,
            'can_accept' => $isOpen,
            'can_confirm' => $isMine && $order->status === Order::STATUS_PENDING_VENDOR_CONFIRM,
            'can_send_payment_link' => $isMine
                && $order->confirmed_amount !== null
                && ! in_array($order->payment_status, [Order::PAYMENT_PAID, Order::PAYMENT_COVERED_BY_PLAN], true)
                && $order->status !== Order::STATUS_CANCELLED
                && $order->status !== Order::STATUS_PENDING_VENDOR_CONFIRM,
            'can_manage_delivery' => $canManageDelivery,
            'can_complete' => $canManageDelivery,
            'accept_url' => $isOpen
                ? route('vendor.orders.accept', $order, false)
                : null,
            'show_url' => $isMine
                ? route('vendor.orders.show', $order, false)
                : null,
            'complete_url' => $canManageDelivery
                ? route('vendor.orders.complete', $order, false)
                : null,
            'upload_delivery_url' => $canManageDelivery
                ? route('vendor.orders.documents.store', $order, false)
                : null,
            'document_type' => $order->documentType?->displayName()
                ?: ($estimate?->document_type_name ?: '—'),
            'posted_at' => $this->formatPostedAt($order->created_at),
            'posted_at_human' => optional($order->created_at)?->diffForHumans(),
            'delivery_name' => $deliveryName,
            'delivery_duration' => $deliveryDuration,
            'delivery_label' => $deliveryLabel,
            'notes' => filled($order->customer_note) ? $order->customer_note : null,
            'authority' => '—',
            'language_pair' => trim($sourceName.' → '.$targetName, ' →'),
            'pages' => $pages,
            'words' => $words,
            'pages_label' => trans_choice('general.pages_count', $pages, ['count' => number_format($pages)]),
            'words_label' => trans_choice('general.words_count', $words, ['count' => number_format($words)]),
            'add_ons' => $addOns->map(function ($addOn) use ($currency) {
                $catalog = $addOn->relationLoaded('addOn') ? $addOn->addOn : null;
                $name = $catalog?->displayName() ?: ($addOn->name ?: '—');
                $meta = is_array($addOn->meta ?? null) ? $addOn->meta : [];

                return [
                    'id' => (int) ($addOn->id ?? 0),
                    'name' => $name,
                    'amount' => (float) $addOn->amount,
                    'amount_html' => formatMoney($addOn->amount, $currency),
                    'completed' => ! empty($meta['vendor_completed']),
                ];
            })->values()->all(),
            'currency' => $currency,
            'currency_html' => currencyIconHtml($currency),
            'amounts' => $showSummary ? [
                'order' => $translationAmount,
                'order_html' => formatMoney($translationAmount, $currency),
                'delivery' => $deliveryAmount,
                'delivery_html' => formatMoney($deliveryAmount, $currency),
                'add_ons' => $addOnsAmount,
                'add_ons_html' => formatMoney($addOnsAmount, $currency),
                'platform_fee' => $platformFee,
                'platform_fee_html' => formatMoney(-1 * $platformFee, $currency),
                'vendor_amount' => $netTotal,
                'vendor_amount_html' => formatMoney($netTotal, $currency),
                'subtotal' => $subtotal,
                'subtotal_html' => formatMoney($subtotal, $currency),
                'total' => $netTotal,
                'total_html' => formatMoney($netTotal, $currency),
                'fee_percent' => $feeBreakdown['fee_percent'],
            ] : null,
            'documents' => $documents,
            'source_documents' => $sourceDocuments,
            'delivery_documents' => $deliveryDocuments,
            'download_allowed' => vendorDocumentDownloadAllowed(),
        ];
    }

    /**
     * Always English Gregorian format; LTR isolation in the UI keeps order in RTL layouts.
     */
    protected function formatPostedAt(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        $date = $value instanceof \Carbon\CarbonInterface
            ? $value
            : \Carbon\Carbon::parse($value);

        return $date
            ->timezone(config('app.timezone'))
            ->locale('en')
            ->format('d M Y, g:i A');
    }
}
