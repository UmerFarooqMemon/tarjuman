<?php

namespace App\Models;

use App\Support\CatalogCache;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model implements TranslatableContract
{
    use Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = [
        'name',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'symbol',
        'symbol_native',
        'icon',
        'decimals',
        'country',
        'sort_order',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'decimals' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flush = fn () => CatalogCache::flushCurrencies();

        static::saved($flush);
        static::deleted($flush);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(VendorPricingRule::class, 'currency', 'code');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }

    /**
     * @return Collection<int, static>
     */
    public static function cachedActive(): Collection
    {
        return CatalogCache::activeCurrencies();
    }

    /**
     * @return Collection<int, static>
     */
    public static function cachedAll(): Collection
    {
        return CatalogCache::allCurrencies();
    }

    /**
     * @return array<string, array{code: string, name_en: string, name_ar: string, symbol: string, symbol_native: string, icon?: string|null, decimals: int, country: string|null}>
     */
    public static function cachedCatalog(): array
    {
        return CatalogCache::currenciesCatalog();
    }

    /**
     * @return list<string>
     */
    public static function cachedActiveCodes(): array
    {
        return array_keys(self::cachedCatalog());
    }

    public function displayName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $this->translate($locale, false)?->name
            ?: $this->translate('en', false)?->name
            ?: $this->code;
    }

    /**
     * Shape expected by legacy helpers / site-settings UI.
     *
     * @return array{code: string, name_en: string, name_ar: string, symbol: string, symbol_native: string, icon: string|null, decimals: int, country: string|null}
     */
    public function toMetaArray(): array
    {
        return [
            'code' => strtoupper($this->code),
            'name_en' => (string) ($this->translate('en', false)?->name ?: $this->code),
            'name_ar' => (string) ($this->translate('ar', false)?->name ?: $this->code),
            'symbol' => (string) ($this->symbol ?: ''),
            'symbol_native' => (string) ($this->symbol_native ?: ''),
            'icon' => $this->icon,
            'decimals' => (int) $this->decimals,
            'country' => $this->country,
        ];
    }

    public function isPlatformCurrency(): bool
    {
        return strtoupper((string) siteSettings()?->currency) === strtoupper($this->code);
    }

    public function isInUse(): bool
    {
        if ($this->isPlatformCurrency()) {
            return true;
        }

        return VendorPricingRule::query()
            ->where('currency', strtoupper($this->code))
            ->exists();
    }
}
