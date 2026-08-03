<?php

namespace App\Models;

use App\Support\CatalogCache;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model implements TranslatableContract
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
        'sort_order',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flush = fn () => CatalogCache::flushDocumentTypes();

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
        return CatalogCache::activeDocumentTypes();
    }

    /**
     * @return Collection<int, static>
     */
    public static function cachedAll(): Collection
    {
        return CatalogCache::allDocumentTypes();
    }

    public function displayName(?string $locale = null): string
    {
        // Prefer Astrotomic locale attributes (current UI locale) over manual translate().
        if ($locale !== null && $locale !== '') {
            return (string) ($this->{"name:{$locale}"} ?: $this->{'name:en'} ?: '');
        }

        return (string) ($this->name ?: $this->{'name:en'} ?: '');
    }
}
