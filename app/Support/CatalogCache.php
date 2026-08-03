<?php

namespace App\Support;

use App\Models\AddOn;
use App\Models\AddOnTranslation;
use App\Models\Authority;
use App\Models\AuthorityTranslation;
use App\Models\Currency;
use App\Models\CurrencyTranslation;
use App\Models\DeliverySpeed;
use App\Models\DeliverySpeedTranslation;
use App\Models\DocumentType;
use App\Models\DocumentTypeTranslation;
use App\Models\Language;
use App\Models\LanguageTranslation;
use App\Models\PricingRule;
use App\Models\SiteSetting;
use App\Models\Vendor;
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

    public const DOCUMENT_TYPES_ACTIVE = 'catalog.document_types.active';

    public const DOCUMENT_TYPES_ALL = 'catalog.document_types.all';

    public const AUTHORITIES_ACTIVE = 'catalog.authorities.active';

    public const AUTHORITIES_ALL = 'catalog.authorities.all';

    public const ADD_ONS_ACTIVE = 'catalog.add_ons.active';

    public const ADD_ONS_ALL = 'catalog.add_ons.all';

    public const DELIVERY_SPEEDS_ACTIVE = 'catalog.delivery_speeds.active';

    public const DELIVERY_SPEEDS_ALL = 'catalog.delivery_speeds.all';

    public const PRICING_RULES_ACTIVE = 'catalog.pricing_rules.active';

    public const PRICING_RULES_ALL = 'catalog.pricing_rules.all';

    public const VENDORS_ACTIVE = 'catalog.vendors.active';

    public const SITE_SETTINGS = 'catalog.site_settings';

    public static function vendorKey(int $vendorId): string
    {
        return "catalog.vendor.{$vendorId}";
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
     * @return Collection<int, DocumentType>
     */
    public static function activeDocumentTypes(): Collection
    {
        /** @var list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}> $rows */
        $rows = Cache::rememberForever(self::DOCUMENT_TYPES_ACTIVE, function () {
            return DocumentType::query()
                ->active()
                ->with('translations')
                ->ordered()
                ->get()
                ->map(fn (DocumentType $type) => self::serializeDocumentType($type))
                ->all();
        });

        return self::hydrateDocumentTypes($rows);
    }

    /**
     * @return Collection<int, DocumentType>
     */
    public static function allDocumentTypes(): Collection
    {
        /** @var list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}> $rows */
        $rows = Cache::rememberForever(self::DOCUMENT_TYPES_ALL, function () {
            return DocumentType::query()
                ->with('translations')
                ->ordered()
                ->get()
                ->map(fn (DocumentType $type) => self::serializeDocumentType($type))
                ->all();
        });

        return self::hydrateDocumentTypes($rows);
    }

    /**
     * @return Collection<int, Authority>
     */
    public static function activeAuthorities(): Collection
    {
        /** @var list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}> $rows */
        $rows = Cache::rememberForever(self::AUTHORITIES_ACTIVE, function () {
            return Authority::query()
                ->active()
                ->with('translations')
                ->ordered()
                ->get()
                ->map(fn (Authority $authority) => self::serializeAuthority($authority))
                ->all();
        });

        return self::hydrateAuthorities($rows);
    }

    /**
     * @return Collection<int, Authority>
     */
    public static function allAuthorities(): Collection
    {
        /** @var list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}> $rows */
        $rows = Cache::rememberForever(self::AUTHORITIES_ALL, function () {
            return Authority::query()
                ->with('translations')
                ->ordered()
                ->get()
                ->map(fn (Authority $authority) => self::serializeAuthority($authority))
                ->all();
        });

        return self::hydrateAuthorities($rows);
    }

    /**
     * @return Collection<int, AddOn>
     */
    public static function activeAddOns(): Collection
    {
        /** @var list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}> $rows */
        $rows = Cache::rememberForever(self::ADD_ONS_ACTIVE, function () {
            return AddOn::query()
                ->active()
                ->with('translations')
                ->ordered()
                ->get()
                ->map(fn (AddOn $addOn) => self::serializeAddOn($addOn))
                ->all();
        });

        return self::hydrateAddOns($rows);
    }

    /**
     * @return Collection<int, AddOn>
     */
    public static function allAddOns(): Collection
    {
        /** @var list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}> $rows */
        $rows = Cache::rememberForever(self::ADD_ONS_ALL, function () {
            return AddOn::query()
                ->with('translations')
                ->ordered()
                ->get()
                ->map(fn (AddOn $addOn) => self::serializeAddOn($addOn))
                ->all();
        });

        return self::hydrateAddOns($rows);
    }

    /**
     * @return Collection<int, DeliverySpeed>
     */
    public static function activeDeliverySpeeds(): Collection
    {
        /** @var list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}> $rows */
        $rows = Cache::rememberForever(self::DELIVERY_SPEEDS_ACTIVE, function () {
            return DeliverySpeed::query()
                ->active()
                ->with('translations')
                ->ordered()
                ->get()
                ->map(fn (DeliverySpeed $speed) => self::serializeDeliverySpeed($speed))
                ->all();
        });

        return self::hydrateDeliverySpeeds($rows);
    }

    /**
     * @return Collection<int, DeliverySpeed>
     */
    public static function allDeliverySpeeds(): Collection
    {
        /** @var list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}> $rows */
        $rows = Cache::rememberForever(self::DELIVERY_SPEEDS_ALL, function () {
            return DeliverySpeed::query()
                ->with('translations')
                ->ordered()
                ->get()
                ->map(fn (DeliverySpeed $speed) => self::serializeDeliverySpeed($speed))
                ->all();
        });

        return self::hydrateDeliverySpeeds($rows);
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
     * @return Collection<int, PricingRule>
     */
    public static function activePricingRules(): Collection
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = Cache::rememberForever(self::PRICING_RULES_ACTIVE, function () {
            return PricingRule::query()
                ->active()
                ->orderByDesc('priority')
                ->orderByDesc('id')
                ->get()
                ->map(fn (PricingRule $rule) => $rule->getAttributes())
                ->all();
        });

        return PricingRule::hydrate($rows);
    }

    /**
     * @return Collection<int, PricingRule>
     */
    public static function allPricingRules(): Collection
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = Cache::rememberForever(self::PRICING_RULES_ALL, function () {
            return PricingRule::query()
                ->orderByDesc('priority')
                ->orderByDesc('id')
                ->get()
                ->map(fn (PricingRule $rule) => $rule->getAttributes())
                ->all();
        });

        return PricingRule::hydrate($rows);
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

    public static function flushDocumentTypes(): void
    {
        Cache::forget(self::DOCUMENT_TYPES_ACTIVE);
        Cache::forget(self::DOCUMENT_TYPES_ALL);
    }

    public static function flushAuthorities(): void
    {
        Cache::forget(self::AUTHORITIES_ACTIVE);
        Cache::forget(self::AUTHORITIES_ALL);
    }

    public static function flushAddOns(): void
    {
        Cache::forget(self::ADD_ONS_ACTIVE);
        Cache::forget(self::ADD_ONS_ALL);
    }

    public static function flushDeliverySpeeds(): void
    {
        Cache::forget(self::DELIVERY_SPEEDS_ACTIVE);
        Cache::forget(self::DELIVERY_SPEEDS_ALL);
    }

    public static function flushPricingRules(): void
    {
        Cache::forget(self::PRICING_RULES_ACTIVE);
        Cache::forget(self::PRICING_RULES_ALL);
    }

    public static function flushVendors(?int $vendorId = null): void
    {
        Cache::forget(self::VENDORS_ACTIVE);

        if ($vendorId !== null) {
            Cache::forget(self::vendorKey($vendorId));
        }
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
     * @return array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}
     */
    protected static function serializeDocumentType(DocumentType $type): array
    {
        return [
            'attributes' => $type->getAttributes(),
            'translations' => $type->translations
                ->map(fn ($translation) => $translation->getAttributes())
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}
     */
    protected static function serializeAuthority(Authority $authority): array
    {
        return [
            'attributes' => $authority->getAttributes(),
            'translations' => $authority->translations
                ->map(fn ($translation) => $translation->getAttributes())
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}
     */
    protected static function serializeAddOn(AddOn $addOn): array
    {
        return [
            'attributes' => $addOn->getAttributes(),
            'translations' => $addOn->translations
                ->map(fn ($translation) => $translation->getAttributes())
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}
     */
    protected static function serializeDeliverySpeed(DeliverySpeed $speed): array
    {
        return [
            'attributes' => $speed->getAttributes(),
            'translations' => $speed->translations
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
     * @return Collection<int, DocumentType>
     */
    protected static function hydrateDocumentTypes(array $rows): Collection
    {
        $collection = new Collection;

        foreach ($rows as $row) {
            /** @var DocumentType $type */
            $type = (new DocumentType)->newFromBuilder($row['attributes']);
            $type->setRelation(
                'translations',
                DocumentTypeTranslation::hydrate($row['translations'])
            );
            $collection->push($type);
        }

        return $collection;
    }

    /**
     * @param  list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}>  $rows
     * @return Collection<int, Authority>
     */
    protected static function hydrateAuthorities(array $rows): Collection
    {
        $collection = new Collection;

        foreach ($rows as $row) {
            /** @var Authority $authority */
            $authority = (new Authority)->newFromBuilder($row['attributes']);
            $authority->setRelation(
                'translations',
                AuthorityTranslation::hydrate($row['translations'])
            );
            $collection->push($authority);
        }

        return $collection;
    }

    /**
     * @param  list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}>  $rows
     * @return Collection<int, AddOn>
     */
    protected static function hydrateAddOns(array $rows): Collection
    {
        $collection = new Collection;

        foreach ($rows as $row) {
            /** @var AddOn $addOn */
            $addOn = (new AddOn)->newFromBuilder($row['attributes']);
            $addOn->setRelation(
                'translations',
                AddOnTranslation::hydrate($row['translations'])
            );
            $collection->push($addOn);
        }

        return $collection;
    }

    /**
     * @param  list<array{attributes: array<string, mixed>, translations: list<array<string, mixed>>}>  $rows
     * @return Collection<int, DeliverySpeed>
     */
    protected static function hydrateDeliverySpeeds(array $rows): Collection
    {
        $collection = new Collection;

        foreach ($rows as $row) {
            /** @var DeliverySpeed $speed */
            $speed = (new DeliverySpeed)->newFromBuilder($row['attributes']);
            $speed->setRelation(
                'translations',
                DeliverySpeedTranslation::hydrate($row['translations'])
            );
            $collection->push($speed);
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
