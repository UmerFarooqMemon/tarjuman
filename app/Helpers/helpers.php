<?php

if (! function_exists('uploadsDir')) {
    /**
     * Relative public uploads path for a module (trailing slash).
     */
    function uploadsDir(string $module = 'front'): string
    {
        $path = public_path('uploads/'.$module);

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return 'uploads/'.$module.'/';
    }
}

if (! function_exists('permissionLabel')) {
    /**
     * Locale-aware permission label from DB.
     *
     * @param  \App\Models\Permission|string  $permission
     */
    function permissionLabel(\App\Models\Permission|string $permission): string
    {
        if (is_string($permission)) {
            $name = $permission;
            $permission = \App\Models\Permission::query()
                ->where('name', $name)
                ->where('guard_name', config('admin_permissions.guard', 'admin'))
                ->first();

            if (! $permission) {
                return $name;
            }
        }

        return $permission->label;
    }
}

if (! function_exists('moduleLabel')) {
    /**
     * Locale-aware module label from DB.
     */
    function moduleLabel(string $module): string
    {
        $permission = \App\Models\Permission::query()
            ->where('module', $module)
            ->where('guard_name', config('admin_permissions.guard', 'admin'))
            ->first();

        if ($permission) {
            return $permission->module_label;
        }

        return str_replace('_', ' ', ucfirst($module));
    }
}

if (! function_exists('adminLocaleSwitcher')) {
    /**
     * Data for the admin English/Arabic language switcher.
     *
     * @return array{
     *     currentLocale: string,
     *     currentLocaleNative: string,
     *     currentLocaleFlag: string,
     *     localeOptions: list<array{code: string, name: string, native: string, flag: string, url: string, active: bool}>
     * }
     */
    function adminLocaleSwitcher(): array
    {
        $localeFlagMap = [
            'en' => 'us',
            'ar' => 'ae',
        ];

        $currentLocale = \LaravelLocalization::getCurrentLocale();

        return [
            'currentLocale' => $currentLocale,
            'currentLocaleNative' => \LaravelLocalization::getCurrentLocaleNative(),
            'currentLocaleFlag' => $localeFlagMap[$currentLocale] ?? 'us',
            'localeOptions' => collect(\LaravelLocalization::getSupportedLocales())
                ->map(function (array $properties, string $locale) use ($localeFlagMap, $currentLocale) {
                    return [
                        'code' => $locale,
                        'name' => $properties['name'],
                        'native' => $properties['native'],
                        'flag' => $localeFlagMap[$locale] ?? 'us',
                        'url' => \LaravelLocalization::getLocalizedURL($locale, null, [], true),
                        'active' => $locale === $currentLocale,
                    ];
                })
                ->values()
                ->all(),
        ];
    }
}

if (! function_exists('crudLocales')) {
    /**
     * Fixed CRUD locales (en/ar) for bilingual admin translation forms.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Language>
     */
    function crudLocales(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Language::crudLocaleList();
    }
}

if (! function_exists('crudLocaleCodes')) {
    /**
     * Fixed CRUD locale codes: en, ar (must match resources/lang).
     *
     * @return list<string>
     */
    function crudLocaleCodes(): array
    {
        return \App\Models\Language::crudLocaleCodes();
    }
}

if (! function_exists('syncModelTranslations')) {
    /**
     * Persist Astrotomic translations without relying on model "saved" events.
     * Required when seeding under WithoutModelEvents (or any muted-event context).
     *
     * @param  \Illuminate\Database\Eloquent\Model&\Astrotomic\Translatable\Contracts\Translatable  $model
     * @param  array<string, array<string, mixed>>  $translations  locale => attributes
     */
    function syncModelTranslations($model, array $translations): void
    {
        foreach ($translations as $locale => $attributes) {
            $model->translations()->updateOrCreate(
                ['locale' => $locale],
                $attributes
            );
        }
    }
}

if (! function_exists('gccCurrencies')) {
    /**
     * Active currency catalog (DB-backed, config fallback when empty).
     *
     * @return array<string, array{code: string, name_en: string, name_ar: string, symbol: string, symbol_native: string, icon?: string|null, decimals: int, country: string|null}>
     */
    function gccCurrencies(): array
    {
        return \App\Models\Currency::cachedCatalog();
    }
}

if (! function_exists('gccCurrencyCodes')) {
    /**
     * @return list<string>
     */
    function gccCurrencyCodes(): array
    {
        return array_keys(gccCurrencies());
    }
}

if (! function_exists('platformCurrency')) {
    /**
     * Active platform currency code from site settings.
     */
    function platformCurrency(): string
    {
        $codes = gccCurrencyCodes();
        $default = (string) config('currencies.default', 'AED');
        $code = strtoupper((string) (
            siteSettings()?->currency
            ?: $default
        ));

        if (in_array($code, $codes, true)) {
            return $code;
        }

        return in_array($default, $codes, true)
            ? $default
            : ($codes[0] ?? $default);
    }
}

if (! function_exists('platformCurrencyMeta')) {
    /**
     * Metadata for the active platform currency.
     *
     * @return array{code: string, name_en: string, name_ar: string, symbol: string, symbol_native: string, decimals: int, country: string, name: string}
     */
    function platformCurrencyMeta(): array
    {
        $code = platformCurrency();
        $meta = gccCurrencies()[$code] ?? gccCurrencies()[config('currencies.default', 'AED')];
        $locale = app()->getLocale();

        $meta['name'] = $locale === 'ar'
            ? ($meta['name_ar'] ?? $meta['name_en'])
            : ($meta['name_en'] ?? $code);

        return $meta;
    }
}

if (! function_exists('currencyMeta')) {
    /**
     * Metadata for a GCC currency code.
     *
     * @return array{code: string, name_en: string, name_ar: string, symbol: string, symbol_native: string, icon?: string, decimals: int, country: string, name: string}|null
     */
    function currencyMeta(?string $code = null): ?array
    {
        $code = strtoupper((string) ($code ?: platformCurrency()));
        $meta = gccCurrencies()[$code] ?? null;

        if (! $meta) {
            return null;
        }

        $locale = app()->getLocale();
        $meta['name'] = $locale === 'ar'
            ? ($meta['name_ar'] ?? $meta['name_en'])
            : ($meta['name_en'] ?? $code);

        return $meta;
    }
}

if (! function_exists('currencySymbol')) {
    /**
     * Text/Unicode symbol for a currency (may not render on all devices yet).
     */
    function currencySymbol(?string $code = null): string
    {
        $meta = currencyMeta($code) ?? platformCurrencyMeta();

        return (string) ($meta['symbol'] ?: $meta['symbol_native'] ?: $meta['code']);
    }
}

if (! function_exists('currencyIconUrl')) {
    /**
     * Public URL for a currency SVG icon, if configured.
     */
    function currencyIconUrl(?string $code = null): ?string
    {
        $meta = currencyMeta($code) ?? platformCurrencyMeta();
        $file = $meta['icon'] ?? null;

        if (! $file) {
            return null;
        }

        $path = public_path('assets/img/currencies/'.$file);

        return is_file($path) ? asset('assets/img/currencies/'.$file) : null;
    }
}

if (! function_exists('currencyIconHtml')) {
    /**
     * Inline SVG icon HTML (inherits text color via currentColor).
     * Falls back to Unicode/native symbol text when no SVG exists.
     */
    function currencyIconHtml(?string $code = null, string $class = 'currency-icon'): string
    {
        $meta = currencyMeta($code) ?? platformCurrencyMeta();
        $file = $meta['icon'] ?? null;
        $path = $file ? public_path('assets/img/currencies/'.$file) : null;

        if ($path && is_file($path)) {
            $svg = (string) file_get_contents($path);
            $safeClass = e($class);
            $label = e($meta['name'] ?? $meta['code']);

            if (preg_match('/<svg\b[^>]*>/i', $svg, $match)) {
                $opening = $match[0];
                if (str_contains($opening, 'class=')) {
                    $opening = preg_replace('/class=(["\'])(.*?)\1/', 'class=$1'.$safeClass.' $2$1', $opening, 1);
                } else {
                    $opening = rtrim(substr($opening, 0, -1)).' class="'.$safeClass.'" role="img" aria-label="'.$label.'">';
                }
                $svg = substr_replace($svg, $opening, strpos($svg, $match[0]), strlen($match[0]));
            }

            return $svg;
        }

        return '<span class="'.e($class).' currency-icon--text" aria-label="'.e($meta['name'] ?? $meta['code']).'">'
            .e(currencySymbol($code))
            .'</span>';
    }
}

if (! function_exists('formatMoney')) {
    /**
     * Format an amount using a GCC currency (defaults to platform currency).
     *
     * @param  string  $suffix  icon|symbol|code|none
     */
    function formatMoney(float|int|string $amount, ?string $currency = null, string $suffix = 'icon'): string
    {
        $meta = currencyMeta($currency) ?? platformCurrencyMeta();
        $decimals = (int) ($meta['decimals'] ?? 2);
        $formatted = number_format((float) $amount, $decimals, '.', ',');

        return match ($suffix) {
            'symbol' => $formatted.' '.currencySymbol($currency),
            'code' => $formatted.' '.($meta['code'] ?? platformCurrency()),
            'none' => $formatted,
            default => '<span class="d-inline-flex align-items-center gap-1">'
                .currencyIconHtml($currency)
                .'<span>'.$formatted.'</span>'
                .'</span>',
        };
    }
}

if (! function_exists('siteSettings')) {
    /**
     * Cached singleton site settings row (invalidated on create/update/delete).
     */
    function siteSettings(): ?\App\Models\SiteSetting
    {
        return once(fn () => \App\Support\CatalogCache::siteSettings());
    }
}

if (! function_exists('siteLogoUrl')) {
    /**
     * Locale-aware site logo URL (falls back to EN logo, then placeholder).
     * Uses Arabic logo when locale is AR and logo_ar file exists.
     */
    function siteLogoUrl(?string $locale = null): string
    {
        $settings = siteSettings();
        $locale = strtolower(substr((string) ($locale ?: app()->getLocale()), 0, 2));
        $dir = uploadsDir('front');

        $candidates = $locale === 'ar'
            ? [$settings?->logo_ar, $settings?->logo]
            : [$settings?->logo, $settings?->logo_ar];

        foreach ($candidates as $file) {
            if (! is_string($file) || $file === '') {
                continue;
            }

            $relative = $dir.$file;
            if (is_file(public_path($relative))) {
                return asset($relative);
            }
        }

        return asset('assets/img/logo-placeholder.png');
    }
}

if (! function_exists('siteFooterLogoUrl')) {
    /**
     * Locale-aware footer logo URL (falls back to EN footer logo, then site logo, then placeholder).
     */
    function siteFooterLogoUrl(?string $locale = null): string
    {
        $settings = siteSettings();
        $locale = strtolower(substr((string) ($locale ?: app()->getLocale()), 0, 2));
        $dir = uploadsDir('front');

        $candidates = $locale === 'ar'
            ? [$settings?->footer_logo_ar, $settings?->footer_logo, $settings?->logo_ar, $settings?->logo]
            : [$settings?->footer_logo, $settings?->footer_logo_ar, $settings?->logo, $settings?->logo_ar];

        foreach ($candidates as $file) {
            if (! is_string($file) || $file === '') {
                continue;
            }

            $relative = $dir.$file;
            if (is_file(public_path($relative))) {
                return asset($relative);
            }
        }

        return asset('assets/img/logo-placeholder.png');
    }
}
