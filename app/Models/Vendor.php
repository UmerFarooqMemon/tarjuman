<?php

namespace App\Models;

use App\Support\CatalogCache;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vendor extends Model implements TranslatableContract
{
    use Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = [
        'legal_name',
        'business_name',
        'address',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'trn',
        'trade_license_no',
        'trade_license_expiry',
        'moj_registration_no',
        'email',
        'phone',
        'logo',
        'is_active',
        'is_approved',
        'approved_at',
        'approved_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'trade_license_expiry' => 'date',
        'is_active' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (Vendor $vendor) {
            CatalogCache::flushVendors($vendor->id);
        });

        static::deleted(function (Vendor $vendor) {
            CatalogCache::flushVendors($vendor->id);
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(VendorUser::class);
    }

    public function owner(): HasOne
    {
        return $this->hasOne(VendorUser::class)->where('is_owner', true);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function languagePairs(): HasMany
    {
        return $this->hasMany(VendorLanguagePair::class);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(VendorPricingRule::class);
    }

    /**
     * @return Collection<int, VendorLanguagePair>
     */
    public function cachedLanguagePairs(bool $activeOnly = false): Collection
    {
        return CatalogCache::vendorLanguagePairs($this->id, $activeOnly);
    }

    /**
     * @return Collection<int, VendorPricingRule>
     */
    public function cachedPricingRules(bool $activeOnly = false): Collection
    {
        return CatalogCache::vendorPricingRules($this->id, $activeOnly);
    }

    /**
     * @return Collection<int, static>
     */
    public static function cachedActive(): Collection
    {
        return CatalogCache::activeVendors();
    }

    public static function cachedFind(int $vendorId): ?self
    {
        return CatalogCache::vendor($vendorId);
    }

    public function displayName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $this->translate($locale, false)?->business_name
            ?: $this->translate($locale, false)?->legal_name
            ?: $this->translate('en', false)?->legal_name
            ?: $this->email;
    }
}
