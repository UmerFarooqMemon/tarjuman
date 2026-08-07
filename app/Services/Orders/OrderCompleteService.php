<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\OrderDocument;
use App\Models\OrderEvent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderCompleteService
{
    public function __construct(
        protected SecureOrderFileStore $files,
        protected OrderNotificationDispatcher $notifier,
    ) {}

    /**
     * Statuses where the assigned vendor may upload delivery files / complete.
     *
     * @return list<string>
     */
    public function completableStatuses(): array
    {
        return [
            Order::STATUS_ASSIGNED,
            Order::STATUS_IN_PROGRESS,
            Order::STATUS_AWAITING_CUSTOMER_PAYMENT,
        ];
    }

    public function vendorCanManageDelivery(Order $order, int $vendorId): bool
    {
        return $vendorId > 0
            && (int) $order->vendor_id === $vendorId
            && in_array($order->status, $this->completableStatuses(), true);
    }

    public function vendorCanComplete(Order $order, int $vendorId): bool
    {
        return $this->vendorCanManageDelivery($order, $vendorId);
    }

    /**
     * @param  list<UploadedFile>  $files
     * @return list<OrderDocument>
     */
    public function uploadDeliveryDocuments(Order $order, array $files, int $vendorUserId): array
    {
        if ($files === []) {
            throw ValidationException::withMessages([
                'documents' => [__('general.order_delivery_documents_required')],
            ]);
        }

        $retentionDays = max(1, (int) (siteSettings()?->order_delivery_retention_days ?: 1095));
        $created = [];

        DB::transaction(function () use ($order, $files, $vendorUserId, $retentionDays, &$created) {
            foreach ($files as $file) {
                $stored = $this->files->store($file, $order, OrderDocument::KIND_DELIVERY);
                $document = OrderDocument::query()->create([
                    'uuid' => $stored['uuid'],
                    'order_id' => $order->id,
                    'kind' => OrderDocument::KIND_DELIVERY,
                    'disk_path' => $stored['disk_path'],
                    'original_name' => $stored['original_name'],
                    'mime' => $stored['mime'],
                    'checksum_sha256' => $stored['checksum_sha256'],
                    'encryption' => $stored['encryption'],
                    'size' => $stored['size'],
                    'pages' => 0,
                    'words' => 0,
                    'amount' => null,
                    'retained_until' => now()->addDays($retentionDays),
                ]);
                $created[] = $document;
            }

            OrderEvent::query()->create([
                'order_id' => $order->id,
                'type' => 'delivery_uploaded',
                'actor_type' => 'vendor_user',
                'actor_id' => $vendorUserId,
                'payload' => [
                    'documents_count' => count($created),
                    'document_uuids' => collect($created)->pluck('uuid')->values()->all(),
                ],
            ]);
        });

        return $created;
    }

    public function deleteDeliveryDocument(Order $order, OrderDocument $document, int $vendorUserId): void
    {
        if ((int) $document->order_id !== (int) $order->id) {
            throw ValidationException::withMessages([
                'document' => [__('general.order_document_not_found')],
            ]);
        }

        if ($document->kind !== OrderDocument::KIND_DELIVERY) {
            throw ValidationException::withMessages([
                'document' => [__('general.order_delivery_document_delete_denied')],
            ]);
        }

        if ($document->isPurged()) {
            throw ValidationException::withMessages([
                'document' => [__('general.order_document_purged')],
            ]);
        }

        DB::transaction(function () use ($order, $document, $vendorUserId) {
            $uuid = $document->uuid;
            $this->files->purge($document);

            OrderEvent::query()->create([
                'order_id' => $order->id,
                'type' => 'delivery_removed',
                'actor_type' => 'vendor_user',
                'actor_id' => $vendorUserId,
                'payload' => [
                    'document_uuid' => $uuid,
                ],
            ]);
        });
    }

    /**
     * @param  list<int>  $completedAddOnIds
     */
    public function complete(Order $order, array $completedAddOnIds, bool $confirmReady, int $vendorUserId): Order
    {
        if (! $confirmReady) {
            throw ValidationException::withMessages([
                'confirm_delivery_ready' => [__('general.order_confirm_delivery_required')],
            ]);
        }

        $deliveryCount = $order->documents()
            ->where('kind', OrderDocument::KIND_DELIVERY)
            ->whereNull('purged_at')
            ->count();

        if ($deliveryCount < 1) {
            throw ValidationException::withMessages([
                'documents' => [__('general.order_delivery_documents_required')],
            ]);
        }

        $addOns = $order->addOns()->get();
        if ($addOns->isNotEmpty()) {
            $requiredIds = $addOns->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
            $submittedIds = collect($completedAddOnIds)->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();

            if ($requiredIds !== $submittedIds) {
                throw ValidationException::withMessages([
                    'completed_add_ons' => [__('general.order_addons_confirm_required')],
                ]);
            }
        }

        $sourceRetentionDays = max(1, (int) (siteSettings()?->order_source_retention_days ?: 90));
        $deliveryRetentionDays = max(1, (int) (siteSettings()?->order_delivery_retention_days ?: 1095));

        $completed = DB::transaction(function () use (
            $order,
            $addOns,
            $completedAddOnIds,
            $vendorUserId,
            $sourceRetentionDays,
            $deliveryRetentionDays,
            $deliveryCount
        ) {
            foreach ($addOns as $addOn) {
                if (! in_array((int) $addOn->id, array_map('intval', $completedAddOnIds), true)) {
                    continue;
                }

                $meta = is_array($addOn->meta) ? $addOn->meta : [];
                $meta['vendor_completed'] = true;
                $meta['vendor_completed_at'] = now()->toIso8601String();
                $addOn->forceFill(['meta' => $meta])->save();
            }

            $order->forceFill([
                'status' => Order::STATUS_COMPLETED,
                'completed_at' => now(),
            ])->save();

            $order->documents()
                ->whereNull('purged_at')
                ->get()
                ->each(function (OrderDocument $document) use ($sourceRetentionDays, $deliveryRetentionDays) {
                    $days = $document->kind === OrderDocument::KIND_DELIVERY
                        ? $deliveryRetentionDays
                        : $sourceRetentionDays;
                    $document->forceFill([
                        'retained_until' => now()->addDays($days),
                    ])->save();
                });

            OrderEvent::query()->create([
                'order_id' => $order->id,
                'type' => 'completed',
                'actor_type' => 'vendor_user',
                'actor_id' => $vendorUserId,
                'payload' => [
                    'delivery_documents_count' => $deliveryCount,
                    'completed_add_on_ids' => array_values(array_map('intval', $completedAddOnIds)),
                ],
            ]);

            return $order->fresh(['documents', 'addOns', 'customer', 'vendor']);
        });

        $this->notifier->orderCompleted($completed);

        return $completed;
    }
}
