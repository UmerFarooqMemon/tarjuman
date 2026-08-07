<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Order extends Model
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_OPEN = 'open';

    public const STATUS_ASSIGNED = 'assigned';

    public const STATUS_PENDING_VENDOR_CONFIRM = 'pending_vendor_confirm';

    public const STATUS_AWAITING_CUSTOMER_PAYMENT = 'awaiting_customer_payment';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_REFUNDED = 'refunded';

    public const PAYMENT_COVERED_BY_PLAN = 'covered_by_plan';

    public const ORDER_ID_PREFIX = 'TRJ-';

    public const ORDER_ID_PAD = 5;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'uuid',
        'customer_id',
        'estimate_id',
        'session_uuid',
        'vendor_id',
        'accepted_by_vendor_user_id',
        'status',
        'payment_status',
        'payment_method',
        'payment_timing_snapshot',
        'assignment_mode_snapshot',
        'payment_gateway_snapshot',
        'payment_tran_ref',
        'payment_checkout_id',
        'payment_link_url',
        'source_language_id',
        'target_language_id',
        'document_type_id',
        'delivery_speed_id',
        'word_count',
        'page_count',
        'estimate_amount',
        'confirmed_amount',
        'currency',
        'customer_note',
        'vendor_note',
        'cancel_reason',
        'assigned_at',
        'confirmed_at',
        'paid_at',
        'completed_at',
        'cancelled_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'estimate_amount' => 'decimal:2',
        'confirmed_amount' => 'decimal:2',
        'word_count' => 'integer',
        'page_count' => 'integer',
        'assigned_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (empty($order->order_id)) {
                $order->order_id = static::generateNextOrderId();
            }

            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'order_id';
    }

    public static function generateNextOrderId(): string
    {
        return DB::transaction(function (): string {
            $latest = static::query()
                ->whereNotNull('order_id')
                ->where('order_id', 'like', self::ORDER_ID_PREFIX.'%')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('order_id');

            $sequence = 1;
            if (is_string($latest) && preg_match('/^'.preg_quote(self::ORDER_ID_PREFIX, '/').'(\d+)$/', $latest, $matches)) {
                $sequence = ((int) $matches[1]) + 1;
            }

            return self::ORDER_ID_PREFIX.str_pad((string) $sequence, self::ORDER_ID_PAD, '0', STR_PAD_LEFT);
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /** @deprecated Use customer() */
    public function user(): BelongsTo
    {
        return $this->customer();
    }

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function sourceLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'source_language_id');
    }

    public function targetLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'target_language_id');
    }

    public function deliverySpeed(): BelongsTo
    {
        return $this->belongsTo(DeliverySpeed::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(OrderDocument::class);
    }

    public function addOns(): HasMany
    {
        return $this->hasMany(OrderAddOn::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class);
    }

    public function payableAmount(): float
    {
        return (float) ($this->confirmed_amount ?? $this->estimate_amount ?? 0);
    }
}
