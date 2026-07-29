<?php

namespace App\Models;

use App\Support\CatalogCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPricingRule extends Model
{
    public const BILLING_UNIT_WORD = 'word';

    public const BILLING_UNIT_PAGE = 'page';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vendor_id',
        'vendor_language_pair_id',
        'document_type_id',
        'name',
        'min_pages',
        'max_pages',
        'billing_unit',
        'rate_amount',
        'currency',
        'priority',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'min_pages' => 'integer',
        'max_pages' => 'integer',
        'rate_amount' => 'decimal:4',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flush = function (VendorPricingRule $rule) {
            CatalogCache::flushVendorPricing((int) $rule->vendor_id);
        };

        static::saved($flush);
        static::deleted($flush);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function languagePair(): BelongsTo
    {
        return $this->belongsTo(VendorLanguagePair::class, 'vendor_language_pair_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function matchesPageCount(int $pageCount): bool
    {
        if ($this->min_pages !== null && $pageCount < $this->min_pages) {
            return false;
        }

        if ($this->max_pages !== null && $pageCount > $this->max_pages) {
            return false;
        }

        return true;
    }

    public function pageRangeLabel(): string
    {
        if ($this->min_pages === null && $this->max_pages === null) {
            return __('general.any_page_count');
        }

        if ($this->min_pages !== null && $this->max_pages !== null) {
            if ($this->min_pages === $this->max_pages) {
                return (string) $this->min_pages;
            }

            return "{$this->min_pages} – {$this->max_pages}";
        }

        if ($this->min_pages !== null) {
            return "≥ {$this->min_pages}";
        }

        return "≤ {$this->max_pages}";
    }

    public static function billingUnits(): array
    {
        return [
            self::BILLING_UNIT_WORD,
            self::BILLING_UNIT_PAGE,
        ];
    }
}
