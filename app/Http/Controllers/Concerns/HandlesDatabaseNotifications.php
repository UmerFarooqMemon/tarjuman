<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Admin;
use App\Models\Order;
use App\Models\VendorUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

trait HandlesDatabaseNotifications
{
    protected function notificationsIndex(Request $request): JsonResponse
    {
        $user = $this->notificationUser();
        $limit = min(50, max(1, (int) $request->integer('limit', 20)));
        $status = strtolower((string) $request->query('status', 'unread'));

        if (! in_array($status, ['unread', 'read', 'all'], true)) {
            $status = 'unread';
        }

        $query = $user->notifications()->latest();

        if ($status === 'unread') {
            $query->whereNull('read_at');
        } elseif ($status === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query
            ->limit($limit)
            ->get()
            ->map(fn (DatabaseNotification $notification) => $this->transformNotification($notification, $user));

        return response()->json([
            'data' => [
                'unread_count' => $user->unreadNotifications()->count(),
                'status' => $status,
                'notifications' => $notifications,
            ],
        ]);
    }

    protected function notificationsMarkRead(string $id): JsonResponse
    {
        $notification = $this->notificationUser()
            ->notifications()
            ->whereKey($id)
            ->firstOrFail();

        $notification->markAsRead();

        $user = $this->notificationUser();
        $user->unsetRelation('notifications');
        $user->unsetRelation('unreadNotifications');

        return response()->json([
            'data' => [
                'id' => $notification->id,
                'read_at' => optional($notification->fresh()->read_at)?->toIso8601String(),
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    protected function notificationsMarkAllRead(): JsonResponse
    {
        $user = $this->notificationUser();
        $user->unreadNotifications->markAsRead();
        $user->unsetRelation('notifications');
        $user->unsetRelation('unreadNotifications');

        return response()->json([
            'data' => [
                'unread_count' => 0,
            ],
        ]);
    }

    protected function notificationsDestroy(string $id): JsonResponse
    {
        $notification = $this->notificationUser()
            ->notifications()
            ->whereKey($id)
            ->firstOrFail();

        $notification->delete();

        $user = $this->notificationUser();
        $user->unsetRelation('notifications');
        $user->unsetRelation('unreadNotifications');

        return response()->json([
            'data' => [
                'id' => $id,
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformNotification(DatabaseNotification $notification, mixed $user): array
    {
        $data = $notification->data ?? [];
        $orderId = $data['order_id'] ?? null;
        $event = $data['event'] ?? null;
        $order = filled($orderId)
            ? Order::query()->with('vendor')->where('order_id', $orderId)->first()
            : null;

        $orderState = $this->resolveOrderState($user, $order);
        $actions = $this->resolveNotificationActions($user, $event, $order, $data['actions'] ?? []);

        return [
            'id' => $notification->id,
            'title' => $this->resolveNotificationTitle($event, $data['title'] ?? null, $user),
            'body' => $this->resolveNotificationBody($event, $order, $data['body'] ?? '', $user),
            'url' => $this->resolveNotificationUrl($user, $event, $orderId, $data['url'] ?? null),
            'details_url' => $this->resolveNotificationDetailsUrl($user, $order, $actions),
            'icon' => $data['icon'] ?? 'ti ti-bell',
            'event' => $event,
            'order_id' => $orderId,
            'order_state' => $orderState,
            'order_state_label' => $this->resolveOrderStateLabel($orderState),
            'actions' => $actions,
            'read_at' => optional($notification->read_at)?->toIso8601String(),
            'created_at' => optional($notification->created_at)?->toIso8601String(),
            'created_at_human' => optional($notification->created_at)?->diffForHumans(),
        ];
    }

    protected function resolveNotificationTitle(?string $event, ?string $fallback, mixed $user = null): string
    {
        $isAdmin = $user instanceof Admin;

        $key = match ($event) {
            'created' => $isAdmin
                ? 'general.notification_order_created_admin_title'
                : 'general.notification_order_created_title',
            'accepted' => 'general.notification_order_accepted_title',
            'assigned' => 'general.notification_order_assigned_title',
            'payment_link' => 'general.notification_payment_link_title',
            'paid' => 'general.notification_order_paid_title',
            'completed' => 'general.notification_order_completed_title',
            default => null,
        };

        if ($key) {
            return __($key);
        }

        return $fallback ?: __('general.notifications');
    }

    protected function resolveNotificationBody(?string $event, ?Order $order, string $storedBody, mixed $user = null): string
    {
        $orderId = $order?->order_id;
        $isAdmin = $user instanceof Admin;

        return match ($event) {
            'created' => $isAdmin
                ? __('general.notification_order_created_admin_body', [
                    'order_id' => $orderId ?? '',
                ])
                : __('general.notification_order_created_body'),
            'accepted' => __('general.notification_order_accepted_body', [
                'vendor' => $order?->vendor?->displayName() ?: __('general.vendor'),
            ]),
            'assigned' => $isAdmin
                ? __('general.notification_order_assigned_admin_body', [
                    'order_id' => $orderId ?? '',
                    'vendor' => $order?->vendor?->displayName() ?: __('general.vendor'),
                ])
                : __('general.notification_order_assigned_body'),
            'payment_link' => __('general.notification_payment_link_body', [
                'order_id' => $orderId ?? '',
            ]),
            'paid' => __('general.notification_order_paid_body', [
                'order_id' => $orderId ?? '',
            ]),
            'completed' => __('general.notification_order_completed_body', [
                'order_id' => $orderId ?? '',
            ]),
            default => filled($storedBody)
                ? $storedBody
                : __('general.notification_order_updated_body', [
                    'order_id' => $orderId ?? '',
                ]),
        };
    }

    /**
     * @return 'open'|'mine'|'taken'|null
     */
    protected function resolveOrderState(mixed $user, ?Order $order): ?string
    {
        if (! $user instanceof VendorUser || ! $order) {
            return null;
        }

        if ($order->status === Order::STATUS_OPEN && ! $order->vendor_id) {
            return 'open';
        }

        $vendorId = (int) ($user->vendor_id ?? 0);
        if ($vendorId > 0 && (int) $order->vendor_id === $vendorId) {
            return 'mine';
        }

        if (filled($order->vendor_id)) {
            return 'taken';
        }

        return null;
    }

    protected function resolveOrderStateLabel(?string $orderState): ?string
    {
        return match ($orderState) {
            'mine' => __('general.notification_badge_accepted'),
            'taken' => __('general.notification_badge_taken'),
            default => null,
        };
    }

    /**
     * @param  list<array<string, mixed>>  $actions
     */
    protected function resolveNotificationDetailsUrl(mixed $user, ?Order $order, array $actions): ?string
    {
        if (! $user instanceof VendorUser || ! $order) {
            return null;
        }

        foreach ($actions as $action) {
            if (($action['type'] ?? null) === 'view' && filled($action['url'] ?? null)) {
                return (string) $action['url'];
            }
        }

        return route('vendor.orders.details', $order, false);
    }

    protected function resolveNotificationUrl(mixed $user, ?string $event, ?string $orderId, ?string $storedUrl): ?string
    {
        if ($user instanceof Admin && filled($orderId)) {
            return route('admin.orders.index', ['order_id' => $orderId], false);
        }

        if ($user instanceof VendorUser) {
            if ($event === 'created') {
                return route('vendor.orders.discover', [], false);
            }

            if (filled($orderId)) {
                return route('vendor.orders.show', ['order' => $orderId], false);
            }

            return route('vendor.orders.index', [], false);
        }

        if (is_string($storedUrl) && str_starts_with($storedUrl, 'http')) {
            $path = parse_url($storedUrl, PHP_URL_PATH);
            $query = parse_url($storedUrl, PHP_URL_QUERY);

            return $path.($query ? '?'.$query : '');
        }

        return $storedUrl;
    }

    /**
     * @param  list<array<string, mixed>>  $storedActions
     * @return list<array<string, string>>
     */
    protected function resolveNotificationActions(mixed $user, ?string $event, ?Order $order, array $storedActions): array
    {
        if (
            $user instanceof VendorUser
            && $event === 'created'
            && $order
            && $order->status === Order::STATUS_OPEN
            && ! $order->vendor_id
        ) {
            return [
                [
                    'type' => 'view',
                    'label' => __('general.view'),
                    'url' => route('vendor.orders.details', $order, false),
                    'method' => 'GET',
                ],
                [
                    'type' => 'accept',
                    'label' => __('general.accept'),
                    'url' => route('vendor.orders.accept', $order, false),
                    'method' => 'POST',
                ],
            ];
        }

        if ($user instanceof VendorUser) {
            return [];
        }

        return array_values(array_filter($storedActions, fn ($action) => is_array($action) && ! empty($action['url'])));
    }

    abstract protected function notificationUser(): mixed;
}
