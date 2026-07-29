<?php

namespace App\Models;

use App\Support\CatalogCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorLanguagePair extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'vendor_id',
        'source_language_id',
        'target_language_id',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flush = function (VendorLanguagePair $pair) {
            CatalogCache::flushVendorPairs((int) $pair->vendor_id);
            CatalogCache::flushVendorPricing((int) $pair->vendor_id);
        };

        static::saved($flush);
        static::deleted($flush);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function sourceLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'source_language_id');
    }

    public function targetLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'target_language_id');
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(VendorPricingRule::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function label(?string $locale = null): string
    {
        $source = $this->sourceLanguage?->displayName($locale) ?? '?';
        $target = $this->targetLanguage?->displayName($locale) ?? '?';

        return "{$source} → {$target}";
    }
}
