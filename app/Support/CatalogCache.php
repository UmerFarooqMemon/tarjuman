<?php

namespace App\Support;

use App\Models\Currency;
use App\Models\CurrencyTranslation;
use App\Models\Language;
use App\Models\LanguageTranslation;
use App\Models\SiteSetting;
use App\Models\Vendor;
use App\Models\VendorLanguagePair;
use App\Models\VendorPricingRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Remember-forever catalogs for hot paths.
 * Stores arrays / ids only (never Eloquent instances) and hydrates on read.
 * Call flush* methods from model saved/deleted hooks.
 */
class CatalogCache
{
    public const LANGUAGES_ACTIVE = 'catalog.languages.active';

    public const LANGUAGES_CRUD_CODES = 'catalog.languages.crud_codes';

    public const LANGUAGES_ALL = 'catalog.languages.all';

    public const CURRENCIES_ACTIVE = 'catalog.currencies.active';

    public const CURRENCIES_ALL = 'catalog.currencies.all';

    public const CURRENCIES_CATALOG = 'catalog.currencies.catalog';

    public const VENDORS_ACTIVE = 'catalog.vendors.active';

    public const SITE_SETTINGS = 'catalog.site_settings';

    public static function vendorKey(int $vendorId): string
    {
        return "catalog.vendor.{$vendorId}";
    }

    public static function vendorPairsKey(int $vendorId): string
    {
        return "catalog.pairs.vendor.{$vendorId}";
    }

    public static function vendorPricingKey(int $vendorId): string
    {
        return "catalog.pricing.vendor.{$vendorId}";
    }

    /**
     * @return Collection<int, Language>
     */
    public static function activeLanguages(): Collection
    {
        /** @var list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}> $rows */
        $rows = Cache::rememberForever(self::LANGUAGES_ACTIVE, function () {
            return Language::query()
                ->active()
                ->with('translations')
                ->ordered()
                ->get()
                ->map(fn (Language $language) => self::serializeTranslatable($language))
                ->all();
        });

        return self::hydrateLanguages($rows);
    }

    /**
     * @return Collection<int, Language>
     */
    public static function allLanguages(): Collection
    {
        /** @var list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}> $rows */
        $rows = Cache::rememberForever(self::LANGUAGES_ALL, function () {
            return Language::query()
                ->with('translations')
                ->ordered()
                ->get()
                ->map(fn (Language $language) => self::serializeTranslatable($language))
                ->all();
        });

        return self::hydrateLanguages($rows);
    }

    /**
     * Fixed backend CRUD locales (UI translation forms). Not user-configurable —
     * only locales that have lang/ files (en, ar).
     *
     * @return list<string>
     */
    public static function crudLocaleCodes(): array
    {
        return ['en', 'ar'];
    }

    /**
     * @return Collection<int, Language>
     */
    public static function crudLocales(): Collection
    {
        $codes = self::crudLocaleCodes();

        return self::allLanguages()
            ->filter(fn (Language $language) => in_array($language->code, $codes, true))
            ->sortBy(fn (Language $language) => array_search($language->code, $codes, true))
            ->values();
    }

    /**
     * @return Collection<int, Currency>
     */
    public static function activeCurrencies(): Collection
    {
        /** @var list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}> $rows */
        $rows = Cache::rememberForever(self::CURRENCIES_ACTIVE, function () {
            return Currency::query()
                ->active()
                ->with('translations')
                ->ordered()
                ->get()
                ->map(fn (Currency $currency) => self::serializeCurrency($currency))
                ->all();
        });

        return self::hydrateCurrencies($rows);
    }

    /**
     * @return Collection<int, Currency>
     */
    public static function allCurrencies(): Collection
    {
        /** @var list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}> $rows */
        $rows = Cache::rememberForever(self::CURRENCIES_ALL, function () {
            return Currency::query()
                ->with('translations')
                ->ordered()
                ->get()
                ->map(fn (Currency $currency) => self::serializeCurrency($currency))
                ->all();
        });

        return self::hydrateCurrencies($rows);
    }

    /**
     * Active currencies keyed by code (legacy helper shape).
     *
     * @return array<string, array{code: string, name_en: string, name_ar: string, symbol: string, symbol_native: string, icon?: string|null, decimals: int, country: string|null}>
     */
    public static function currenciesCatalog(): array
    {
        if (! Cache::has(self::CURRENCIES_CATALOG)) {
            $catalog = self::activeCurrencies()
                ->mapWithKeys(fn (Currency $currency) => [
                    strtoupper($currency->code) => $currency->toMetaArray(),
                ])
                ->all();

            if ($catalog !== []) {
                Cache::forever(self::CURRENCIES_CATALOG, $catalog);
            }

            return $catalog !== [] ? $catalog : config('currencies.gcc', []);
        }

        /** @var array<string, array{code: string, name_en: string, name_ar: string, symbol: string, symbol_native: string, icon?: string|null, decimals: int, country: string|null}> $catalog */
        $catalog = Cache::get(self::CURRENCIES_CATALOG, []);

        return $catalog !== [] ? $catalog : config('currencies.gcc', []);
    }

    /**
     * @return Collection<int, Vendor>
     */
    public static function activeVendors(): Collection
    {
        /** @var list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}> $rows */
        $rows = Cache::rememberForever(self::VENDORS_ACTIVE, function () {
            return Vendor::query()
                ->with('translations')
                ->where('is_active', true)
                ->where('is_approved', true)
                ->orderByDesc('id')
                ->get()
                ->map(fn (Vendor $vendor) => self::serializeTranslatable($vendor))
                ->all();
        });

        return self::hydrateVendors($rows);
    }

    public static function vendor(int $vendorId): ?Vendor
    {
        /** @var array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}|null $row */
        $row = Cache::rememberForever(self::vendorKey($vendorId), function () use ($vendorId) {
            $vendor = Vendor::query()
                ->with('translations')
                ->find($vendorId);

            return $vendor ? self::serializeTranslatable($vendor) : null;
        });

        if ($row === null) {
            return null;
        }

        return self::hydrateVendors([$row])->first();
    }

    /**
     * @return Collection<int, VendorLanguagePair>
     */
    public static function vendorLanguagePairs(int $vendorId, bool $activeOnly = false): Collection
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = Cache::rememberForever(self::vendorPairsKey($vendorId), function () use ($vendorId) {
            return VendorLanguagePair::query()
                ->where('vendor_id', $vendorId)
                ->orderByDesc('id')
                ->get()
                ->map(fn (VendorLanguagePair $pair) => $pair->getAttributes())
                ->all();
        });

        $pairs = VendorLanguagePair::hydrate($rows);

        if ($activeOnly) {
            $pairs = $pairs->where('is_active', true)->values();
        }

        return $pairs;
    }

    /**
     * @return Collection<int, VendorPricingRule>
     */
    public static function vendorPricingRules(int $vendorId, bool $activeOnly = false): Collection
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = Cache::rememberForever(self::vendorPricingKey($vendorId), function () use ($vendorId) {
            return VendorPricingRule::query()
                ->where('vendor_id', $vendorId)
                ->orderByDesc('priority')
                ->orderByDesc('id')
                ->get()
                ->map(fn (VendorPricingRule $rule) => $rule->getAttributes())
                ->all();
        });

        $rules = VendorPricingRule::hydrate($rows);

        if ($activeOnly) {
            $rules = $rules->where('is_active', true)->values();
        }

        return $rules;
    }

    public static function siteSettings(): ?SiteSetting
    {
        if (! Cache::has(self::SITE_SETTINGS)) {
            $settings = SiteSetting::query()->find(1);

            if ($settings === null) {
                return null;
            }

            Cache::forever(self::SITE_SETTINGS, $settings->getAttributes());
        }

        /** @var array<string, mixed>|null $attributes */
        $attributes = Cache::get(self::SITE_SETTINGS);

        if ($attributes === null) {
            return null;
        }

        return (new SiteSetting)->newFromBuilder($attributes);
    }

    public static function flushLanguages(): void
    {
        Cache::forget(self::LANGUAGES_ACTIVE);
        Cache::forget(self::LANGUAGES_ALL);
        Cache::forget(self::LANGUAGES_CRUD_CODES); // legacy key
        Cache::forget('languages.crud_locale_codes'); // legacy
        Cache::forget('languages.crud_locales'); // legacy
    }

    public static function flushCurrencies(): void
    {
        Cache::forget(self::CURRENCIES_ACTIVE);
        Cache::forget(self::CURRENCIES_ALL);
        Cache::forget(self::CURRENCIES_CATALOG);
    }

    public static function flushVendors(?int $vendorId = null): void
    {
        Cache::forget(self::VENDORS_ACTIVE);

        if ($vendorId !== null) {
            Cache::forget(self::vendorKey($vendorId));
            Cache::forget(self::vendorPairsKey($vendorId));
            Cache::forget(self::vendorPricingKey($vendorId));
        }
    }

    public static function flushVendorPairs(int $vendorId): void
    {
        Cache::forget(self::vendorPairsKey($vendorId));
    }

    public static function flushVendorPricing(int $vendorId): void
    {
        Cache::forget(self::vendorPricingKey($vendorId));
    }

    public static function flushSiteSettings(): void
    {
        Cache::forget(self::SITE_SETTINGS);
    }

    /**
     * @return array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}
     */
    protected static function serializeCurrency(Currency $currency): array
    {
        return [
            'attributes' => $currency->getAttributes(),
            'translations' => $currency->translations
                ->map(fn ($translation) => $translation->getAttributes())
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  Language|Vendor  $model
     * @return array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}
     */
    protected static function serializeTranslatable(Language|Vendor $model): array
    {
        return [
            'attributes' => $model->getAttributes(),
            'translations' => $model->translations
                ->map(fn ($translation) => $translation->getAttributes())
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}>  $rows
     * @return Collection<int, Language>
     */
    protected static function hydrateLanguages(array $rows): Collection
    {
        $collection = new Collection;

        foreach ($rows as $row) {
            /** @var Language $language */
            $language = (new Language)->newFromBuilder($row['attributes']);
            $language->setRelation(
                'translations',
                LanguageTranslation::hydrate($row['translations'])
            );
            $collection->push($language);
        }

        return $collection;
    }

    /**
     * @param  list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}>  $rows
     * @return Collection<int, Currency>
     */
    protected static function hydrateCurrencies(array $rows): Collection
    {
        $collection = new Collection;

        foreach ($rows as $row) {
            /** @var Currency $currency */
            $currency = (new Currency)->newFromBuilder($row['attributes']);
            $currency->setRelation(
                'translations',
                CurrencyTranslation::hydrate($row['translations'])
            );
            $collection->push($currency);
        }

        return $collection;
    }

    /**
     * @param  list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}>  $rows
     * @return Collection<int, Vendor>
     */
    protected static function hydrateVendors(array $rows): Collection
    {
        $collection = new Collection;

        foreach ($rows as $row) {
            /** @var Vendor $vendor */
            $vendor = (new Vendor)->newFromBuilder($row['attributes']);
            $vendor->setRelation(
                'translations',
                \App\Models\VendorTranslation::hydrate($row['translations'])
            );
            $collection->push($vendor);
        }

        return $collection;
    }
}
