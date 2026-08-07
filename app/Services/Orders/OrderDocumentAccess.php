<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\OrderDocument;
use App\Models\VendorUser;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderDocumentAccess
{
    /**
     * Statuses where the assigned vendor may open source documents.
     *
     * @return list<string>
     */
    public function vendorAllowedStatuses(): array
    {
        return [
            Order::STATUS_PENDING_VENDOR_CONFIRM,
            Order::STATUS_ASSIGNED,
            Order::STATUS_AWAITING_CUSTOMER_PAYMENT,
            Order::STATUS_IN_PROGRESS,
            Order::STATUS_COMPLETED,
        ];
    }

    public function assertVendorCanAccess(Order $order, OrderDocument $document, VendorUser $vendorUser): void
    {
        if ((int) $document->order_id !== (int) $order->id) {
            throw new NotFoundHttpException(__('general.order_document_not_found'));
        }

        if ($document->isPurged()) {
            throw new NotFoundHttpException(__('general.order_document_purged'));
        }

        if ((int) $order->vendor_id !== (int) $vendorUser->vendor_id) {
            throw new AccessDeniedHttpException(__('general.order_document_access_denied'));
        }

        if (! in_array($order->status, $this->vendorAllowedStatuses(), true)) {
            throw new AccessDeniedHttpException(__('general.order_document_access_denied'));
        }
    }

    public function vendorCanAccess(Order $order, VendorUser $vendorUser): bool
    {
        return (int) $order->vendor_id === (int) $vendorUser->vendor_id
            && in_array($order->status, $this->vendorAllowedStatuses(), true);
    }

    public function vendorCanDownload(): bool
    {
        return vendorDocumentDownloadAllowed();
    }

    public function assertVendorCanDownload(): void
    {
        if (! $this->vendorCanDownload()) {
            throw new AccessDeniedHttpException(__('general.order_document_download_disabled'));
        }
    }
}
