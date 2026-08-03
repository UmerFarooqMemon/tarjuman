<?php

namespace App\Models;

use App\Support\CatalogCache;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DeliverySpeed extends Model implements TranslatableContract
{
    use Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = [
        'name',
        'duration_label',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'price_amount',
        'min_hours',
        'max_hours',
        'sort_order',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price_amount' => 'decimal:4',
        'min_hours' => 'integer',
        'max_hours' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flush = fn () => CatalogCache::flushDeliverySpeeds();

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
        return CatalogCache::activeDeliverySpeeds();
    }

    /**
     * @return Collection<int, static>
     */
    public static function cachedAll(): Collection
    {
        return CatalogCache::allDeliverySpeeds();
    }

    public function displayName(?string $locale = null): string
    {
        if ($locale !== null && $locale !== '') {
            return (string) ($this->{"name:{$locale}"} ?: $this->{'name:en'} ?: '');
        }

        return (string) ($this->name ?: $this->{'name:en'} ?: '');
    }

    public function displayDuration(?string $locale = null): string
    {
        if ($locale !== null && $locale !== '') {
            return (string) ($this->{"duration_label:{$locale}"} ?: $this->{'duration_label:en'} ?: '');
        }

        return (string) ($this->duration_label ?: $this->{'duration_label:en'} ?: '');
    }
}
