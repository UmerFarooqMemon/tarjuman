<?php

namespace App\Notifications;

use App\Models\Admin;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorUser;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Delivered synchronously so the bell updates without a queue worker.
 * Mail still goes out on the same request (volume is low for order alerts).
 */
class OrderAlertNotification extends Notification
{
    public const EVENT_CREATED = 'created';
    public const EVENT_ACCEPTED = 'accepted';
    public const EVENT_ASSIGNED = 'assigned';
    public const EVENT_PAYMENT_LINK = 'payment_link';
    public const EVENT_PAID = 'paid';

    public const EVENT_COMPLETED = 'completed';

    public function __construct(
        public Order $order,
        public string $event,
    ) {
        $this->order->loadMissing(['customer', 'vendor']);
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if (filled($notifiable->email ?? null)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $copy = $this->copy($notifiable);
        $title = $copy['title'];
        $body = $copy['body'];
        $action = $this->event === self::EVENT_PAYMENT_LINK && $notifiable instanceof User
            ? __('general.pay_now')
            : __('general.view_order');
        $url = $copy['url'] ?? $this->urlFor($notifiable);

        $mail = (new MailMessage)
            ->subject($title)
            ->greeting(__('general.hello').' '.($notifiable->fullName() ?? $notifiable->name ?? ''))
            ->line($body);

        if (! empty($url)) {
            $mail->action($action, url($url));
        }

        return $mail;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $copy = $this->copy($notifiable);

        return [
            'event' => $this->event,
            'order_id' => $this->order->order_id,
            'title' => $copy['title'],
            'body' => $copy['body'],
            'url' => $copy['url'],
            'icon' => $copy['icon'],
            'actions' => $copy['actions'],
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable) + [
            'created_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * @return array{title: string, body: string, action: string, url: ?string, icon: string, actions: list<array<string, string>>}
     */
    protected function copy(object $notifiable): array
    {
        $orderId = $this->order->order_id;
        $isAdmin = $notifiable instanceof Admin;

        return match ($this->event) {
            self::EVENT_CREATED => [
                'title' => $isAdmin
                    ? __('general.notification_order_created_admin_title')
                    : __('general.notification_order_created_title'),
                'body' => $isAdmin
                    ? __('general.notification_order_created_admin_body', ['order_id' => $orderId])
                    : __('general.notification_order_created_body'),
                'action' => __('general.view_order'),
                'url' => $this->urlFor($notifiable),
                'icon' => 'ti ti-shopping-cart-plus',
                'actions' => $this->actionsFor($notifiable),
            ],
            self::EVENT_ACCEPTED => [
                'title' => __('general.notification_order_accepted_title'),
                'body' => __('general.notification_order_accepted_body', [
                    'vendor' => $this->vendorName(),
                ]),
                'action' => __('general.view_order'),
                'url' => $this->urlFor($notifiable),
                'icon' => 'ti ti-check',
                'actions' => [],
            ],
            self::EVENT_ASSIGNED => [
                'title' => __('general.notification_order_assigned_title'),
                'body' => $isAdmin
                    ? __('general.notification_order_assigned_admin_body', [
                        'order_id' => $orderId,
                        'vendor' => $this->vendorName(),
                    ])
                    : __('general.notification_order_assigned_body'),
                'action' => __('general.view_order'),
                'url' => $this->urlFor($notifiable),
                'icon' => 'ti ti-user-check',
                'actions' => [],
            ],
            self::EVENT_PAYMENT_LINK => [
                'title' => __('general.notification_payment_link_title'),
                'body' => __('general.notification_payment_link_body', ['order_id' => $orderId]),
                'action' => $notifiable instanceof User ? __('general.pay_now') : __('general.view_order'),
                'url' => $this->urlFor($notifiable),
                'icon' => 'ti ti-link',
                'actions' => [],
            ],
            self::EVENT_PAID => [
                'title' => __('general.notification_order_paid_title'),
                'body' => __('general.notification_order_paid_body', ['order_id' => $orderId]),
                'action' => __('general.view_order'),
                'url' => $this->urlFor($notifiable),
                'icon' => 'ti ti-currency-dollar',
                'actions' => [],
            ],
            self::EVENT_COMPLETED => [
                'title' => __('general.notification_order_completed_title'),
                'body' => __('general.notification_order_completed_body', ['order_id' => $orderId]),
                'action' => __('general.view_order'),
                'url' => $this->urlFor($notifiable),
                'icon' => 'ti ti-circle-check',
                'actions' => [],
            ],
            default => [
                'title' => __('general.notifications'),
                'body' => __('general.notification_order_updated_body', ['order_id' => $orderId]),
                'action' => __('general.view_order'),
                'url' => $this->urlFor($notifiable),
                'icon' => 'ti ti-bell',
                'actions' => [],
            ],
        };
    }

    /**
     * Relative path (no host) so links work regardless of APP_URL.
     */
    protected function urlFor(object $notifiable): ?string
    {
        if ($notifiable instanceof Admin) {
            return route('admin.orders.index', ['order_id' => $this->order->order_id], false);
        }

        if ($notifiable instanceof VendorUser) {
            if ($this->event === self::EVENT_CREATED && ! $this->order->vendor_id) {
                return route('vendor.orders.discover', [], false);
            }

            return route('vendor.orders.show', $this->order, false);
        }

        if ($notifiable instanceof User) {
            return $this->order->payment_link_url;
        }

        return null;
    }

    /**
     * @return list<array{type: string, label: string, url: string, method: string}>
     */
    protected function actionsFor(object $notifiable): array
    {
        if (
            $notifiable instanceof VendorUser
            && $this->event === self::EVENT_CREATED
            && ! $this->order->vendor_id
            && $this->order->status === Order::STATUS_OPEN
        ) {
            return [
                [
                    'type' => 'view',
                    'label' => __('general.view'),
                    'url' => route('vendor.orders.details', $this->order, false),
                    'method' => 'GET',
                ],
                [
                    'type' => 'accept',
                    'label' => __('general.accept'),
                    'url' => route('vendor.orders.accept', $this->order, false),
                    'method' => 'POST',
                ],
            ];
        }

        return [];
    }

    protected function vendorName(): string
    {
        $this->order->loadMissing('vendor');

        return $this->order->vendor?->displayName()
            ?: __('general.vendor');
    }
}
