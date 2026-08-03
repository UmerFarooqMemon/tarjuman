<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Estimate extends Model
{
    public const STATUS_QUOTED = 'quoted';

    public const STATUS_CONVERTED = 'converted';

    public const STATUS_SUPERSEDED = 'superseded';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'session_uuid',
        'previous_estimate_id',
        'status',
        'document_type_id',
        'document_type_name',
        'source_language_id',
        'source_language_code',
        'source_language_name',
        'target_language_id',
        'target_language_code',
        'target_language_name',
        'pricing_rule_id',
        'pricing_rule_name',
        'billing_unit',
        'billing_quantity',
        'unit_rate',
        'page_count',
        'word_count',
        'translation_amount',
        'add_ons_total',
        'delivery_speed_id',
        'delivery_speed_name',
        'delivery_speed_amount',
        'total_amount',
        'currency',
        'order_id',
        'converted_at',
        'meta',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'billing_quantity' => 'integer',
        'unit_rate' => 'decimal:4',
        'page_count' => 'integer',
        'word_count' => 'integer',
        'translation_amount' => 'decimal:4',
        'add_ons_total' => 'decimal:4',
        'delivery_speed_amount' => 'decimal:4',
        'total_amount' => 'decimal:4',
        'converted_at' => 'datetime',
        'meta' => 'array',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(EstimateDocument::class);
    }

    public function addOns(): HasMany
    {
        return $this->hasMany(EstimateAddOn::class);
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

    public function pricingRule(): BelongsTo
    {
        return $this->belongsTo(PricingRule::class);
    }

    public function deliverySpeed(): BelongsTo
    {
        return $this->belongsTo(DeliverySpeed::class);
    }

    public function previousEstimate(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_estimate_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(self::class, 'previous_estimate_id');
    }

    public function scopeQuoted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_QUOTED);
    }

    public function scopeConverted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CONVERTED);
    }

    public function scopeSuperseded(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUPERSEDED);
    }

    /**
     * Current funnel rows only (ignore replaced recalculations).
     * Use for conversion rate: converted / (quoted + converted).
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_QUOTED, self::STATUS_CONVERTED]);
    }

    public function scopeForSession(Builder $query, string $sessionUuid): Builder
    {
        return $query->where('session_uuid', $sessionUuid);
    }

    public function isConverted(): bool
    {
        return $this->status === self::STATUS_CONVERTED;
    }

    public function isSuperseded(): bool
    {
        return $this->status === self::STATUS_SUPERSEDED;
    }

    /**
     * Mark this estimate as converted into a sale/order (orders module will call this).
     */
    public function markConverted(int $orderId, ?Carbon $at = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_CONVERTED,
            'order_id' => $orderId,
            'converted_at' => $at ?? now(),
        ])->save();
    }

    public function markSuperseded(): void
    {
        if ($this->status !== self::STATUS_QUOTED) {
            return;
        }

        $this->forceFill([
            'status' => self::STATUS_SUPERSEDED,
        ])->save();
    }
}
