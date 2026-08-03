<?php

namespace App\Models;

use App\Support\CatalogCache;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AddOn extends Model implements TranslatableContract
{
    use Translatable;

    public const PRICING_MODE_FIXED = 'fixed';

    public const PRICING_MODE_PER_PAGE = 'per_page';

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
        'pricing_mode',
        'default_amount',
        'sort_order',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'default_amount' => 'decimal:4',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flush = fn () => CatalogCache::flushAddOns();

        static::saved($flush);
        static::deleted($flush);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return Collection<int, static>
     */
    public static function cachedActive(): Collection
    {
        return CatalogCache::activeAddOns();
    }

    /**
     * @return Collection<int, static>
     */
    public static function cachedAll(): Collection
    {
        return CatalogCache::allAddOns();
    }

    public function displayName(?string $locale = null): string
    {
        // Prefer Astrotomic locale attributes (current UI locale) over manual translate().
        if ($locale !== null && $locale !== '') {
            return (string) ($this->{"name:{$locale}"} ?: $this->{'name:en'} ?: '');
        }

        return (string) ($this->name ?: $this->{'name:en'} ?: '');
    }

    public function pricingModeLabel(): string
    {
        return match ($this->pricing_mode) {
            self::PRICING_MODE_PER_PAGE => __('general.pricing_mode_per_page'),
            default => __('general.pricing_mode_fixed'),
        };
    }

    /**
     * @return list<string>
     */
    public static function pricingModes(): array
    {
        return [
            self::PRICING_MODE_FIXED,
            self::PRICING_MODE_PER_PAGE,
        ];
    }
}
