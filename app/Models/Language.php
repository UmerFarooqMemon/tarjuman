<?php

namespace App\Models;

use App\Support\CatalogCache;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Language extends Model implements TranslatableContract
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
        'native_name',
        'direction',
        'sort_order',
        'is_active',
        'is_crud_locale',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_crud_locale' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flush = fn () => CatalogCache::flushLanguages();

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

    public function scopeCrudLocales(Builder $query): Builder
    {
        return $query->whereIn('code', CatalogCache::crudLocaleCodes())->ordered();
    }

    /**
     * @return Collection<int, static>
     */
    public static function cachedActive(): Collection
    {
        return CatalogCache::activeLanguages();
    }

    /**
     * @return Collection<int, static>
     */
    public static function cachedAll(): Collection
    {
        return CatalogCache::allLanguages();
    }

    /**
     * Fixed backend CRUD locales (en/ar only — matches lang/ files).
     *
     * @return Collection<int, static>
     */
    public static function crudLocaleList(): Collection
    {
        return CatalogCache::crudLocales();
    }

    /**
     * @return list<string>
     */
    public static function crudLocaleCodes(): array
    {
        return CatalogCache::crudLocaleCodes();
    }

    /** @deprecated Use CatalogCache::flushLanguages() */
    public static function forgetCrudLocaleCache(): void
    {
        CatalogCache::flushLanguages();
    }

    public function displayName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $this->native_name
            ?: $this->translate($locale, false)?->name
            ?: $this->translate('en', false)?->name
            ?: strtoupper($this->code);
    }

    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }

    /**
     * System locales used for UI / translation `locale` columns (en, ar).
     * Their codes must not change or be deleted.
     */
    public function hasLockedCode(): bool
    {
        return in_array(strtolower((string) $this->code), CatalogCache::crudLocaleCodes(), true);
    }
}
