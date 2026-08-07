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

if (! function_exists('notificationsDropdownConfig')) {
    /**
     * Config for the theme notifications dropdown (admin or vendor).
     *
     * @return array<string, string>|null
     */
    function notificationsDropdownConfig(string $guard): ?array
    {
        if ($guard === 'admin') {
            $user = auth('admin')->user();
            if (! $user) {
                return null;
            }

            return [
                'notificationsIndexUrl' => route('admin.notifications.index', [], false),
                'notificationsMarkAllUrl' => route('admin.notifications.read-all', [], false),
                'notificationsMarkReadUrlTemplate' => str_replace('PLACEHOLDER', '__ID__', route('admin.notifications.read', ['id' => 'PLACEHOLDER'], false)),
                'notificationsDestroyUrlTemplate' => str_replace('PLACEHOLDER', '__ID__', route('admin.notifications.destroy', ['id' => 'PLACEHOLDER'], false)),
                'broadcastAuthUrl' => route('admin.broadcasting.auth', [], false),
                'broadcastChannel' => 'App.Models.Admin.'.$user->id,
                'userId' => (string) $user->id,
                'guard' => 'admin',
            ];
        }

        if ($guard === 'vendor') {
            $user = auth('vendor')->user();
            if (! $user) {
                return null;
            }

            return [
                'notificationsIndexUrl' => route('vendor.notifications.index', [], false),
                'notificationsMarkAllUrl' => route('vendor.notifications.read-all', [], false),
                'notificationsMarkReadUrlTemplate' => str_replace('PLACEHOLDER', '__ID__', route('vendor.notifications.read', ['id' => 'PLACEHOLDER'], false)),
                'notificationsDestroyUrlTemplate' => str_replace('PLACEHOLDER', '__ID__', route('vendor.notifications.destroy', ['id' => 'PLACEHOLDER'], false)),
                'broadcastAuthUrl' => route('vendor.broadcasting.auth', [], false),
                'broadcastChannel' => 'App.Models.VendorUser.'.$user->id,
                'userId' => (string) $user->id,
                'guard' => 'vendor',
            ];
        }

        return null;
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

if (! function_exists('formatOrderStatus')) {
    /**
     * Human-readable order status (title case / translated).
     */
    function formatOrderStatus(?string $status): string
    {
        if (! filled($status)) {
            return '—';
        }

        $key = 'general.order_status_'.$status;
        $translated = __($key);

        if ($translated !== $key) {
            return $translated;
        }

        return \Illuminate\Support\Str::of($status)->replace('_', ' ')->title()->toString();
    }
}

if (! function_exists('orderStatusBadgeClass')) {
    /**
     * Vuexy badge background class for an order status.
     */
    function orderStatusBadgeClass(?string $status): string
    {
        return match ($status) {
            \App\Models\Order::STATUS_PENDING_PAYMENT => 'bg-label-warning',
            \App\Models\Order::STATUS_OPEN => 'bg-label-info',
            \App\Models\Order::STATUS_ASSIGNED => 'bg-label-primary',
            \App\Models\Order::STATUS_PENDING_VENDOR_CONFIRM => 'bg-label-warning',
            \App\Models\Order::STATUS_AWAITING_CUSTOMER_PAYMENT => 'bg-label-warning',
            \App\Models\Order::STATUS_IN_PROGRESS => 'bg-label-primary',
            \App\Models\Order::STATUS_COMPLETED => 'bg-label-success',
            \App\Models\Order::STATUS_CANCELLED => 'bg-label-danger',
            default => 'bg-label-secondary',
        };
    }
}

if (! function_exists('orderStatusBadge')) {
    /**
     * Colored badge HTML for an order status.
     */
    function orderStatusBadge(?string $status): string
    {
        $label = e(formatOrderStatus($status));

        return '<span class="badge '.orderStatusBadgeClass($status).'">'.$label.'</span>';
    }
}

if (! function_exists('formatOrderPaymentStatus')) {
    /**
     * Human-readable order payment status (title case / translated).
     */
    function formatOrderPaymentStatus(?string $status): string
    {
        if (! filled($status)) {
            return '—';
        }

        $key = 'general.order_payment_status_'.$status;
        $translated = __($key);

        if ($translated !== $key) {
            return $translated;
        }

        return \Illuminate\Support\Str::of($status)->replace('_', ' ')->title()->toString();
    }
}

if (! function_exists('orderPaymentStatusBadgeClass')) {
    /**
     * Vuexy badge background class for a payment status.
     */
    function orderPaymentStatusBadgeClass(?string $status): string
    {
        return match ($status) {
            \App\Models\Order::PAYMENT_UNPAID => 'bg-label-secondary',
            \App\Models\Order::PAYMENT_PENDING => 'bg-label-warning',
            \App\Models\Order::PAYMENT_PAID => 'bg-label-success',
            \App\Models\Order::PAYMENT_REFUNDED => 'bg-label-info',
            \App\Models\Order::PAYMENT_COVERED_BY_PLAN => 'bg-label-primary',
            default => 'bg-label-secondary',
        };
    }
}

if (! function_exists('orderPaymentStatusBadge')) {
    /**
     * Colored badge HTML for a payment status.
     */
    function orderPaymentStatusBadge(?string $status): string
    {
        $label = e(formatOrderPaymentStatus($status));

        return '<span class="badge '.orderPaymentStatusBadgeClass($status).'">'.$label.'</span>';
    }
}

if (! function_exists('normalizeAssignmentMode')) {
    /**
     * Canonical assignment mode: manual|open (legacy "uber" maps to open).
     */
    function normalizeAssignmentMode(?string $mode): string
    {
        $mode = is_string($mode) ? strtolower(trim($mode)) : '';

        return match ($mode) {
            'manual' => 'manual',
            'open', 'uber', 'marketplace' => 'open',
            default => 'open',
        };
    }
}

if (! function_exists('formatAssignmentMode')) {
    /**
     * Human label for assignment mode snapshots.
     */
    function formatAssignmentMode(?string $mode): string
    {
        return match (normalizeAssignmentMode($mode)) {
            'open' => __('general.platform_assignment_open'),
            'manual' => __('general.platform_assignment_manual'),
            default => filled($mode)
                ? \Illuminate\Support\Str::of($mode)->replace('_', ' ')->title()->toString()
                : '—',
        };
    }
}

if (! function_exists('orderAssignmentMode')) {
    /**
     * Effective assignment mode for an order (snapshot, else live platform setting).
     */
    function orderAssignmentMode(?\App\Models\Order $order): string
    {
        $snapshot = $order?->assignment_mode_snapshot;
        if (in_array($snapshot, ['manual', 'open', 'uber'], true)) {
            return normalizeAssignmentMode($snapshot);
        }

        return normalizeAssignmentMode(siteSettings()?->order_assignment_mode);
    }
}

if (! function_exists('platformAssignmentIsManual')) {
    function platformAssignmentIsManual(): bool
    {
        return normalizeAssignmentMode(siteSettings()?->order_assignment_mode) === 'manual';
    }
}

if (! function_exists('adminCanAssignOrder')) {
    /**
     * Whether an admin may assign this unassigned order to a vendor.
     * Allowed when the order snapshot is manual, or the live platform mode is manual.
     */
    function adminCanAssignOrder(?\App\Models\Order $order): bool
    {
        if (! $order || filled($order->vendor_id)) {
            return false;
        }

        if ($order->status !== \App\Models\Order::STATUS_OPEN) {
            return false;
        }

        return orderAssignmentMode($order) === 'manual' || platformAssignmentIsManual();
    }
}

if (! function_exists('formatPaymentTimingMode')) {
    /**
     * Human label for payment timing snapshots.
     */
    function formatPaymentTimingMode(?string $mode): string
    {
        return match ($mode) {
            'quick' => __('general.platform_quick_payment'),
            'later' => __('general.platform_pay_later'),
            default => filled($mode)
                ? \Illuminate\Support\Str::of($mode)->replace('_', ' ')->title()->toString()
                : '—',
        };
    }
}

if (! function_exists('formatOrderEventType')) {
    function formatOrderEventType(?string $type): string
    {
        if (! filled($type)) {
            return '—';
        }

        $key = 'general.order_event_'.$type;
        $translated = __($key);

        if ($translated !== $key) {
            return $translated;
        }

        return \Illuminate\Support\Str::of($type)->replace('_', ' ')->title()->toString();
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

if (! function_exists('vendorDocumentDownloadAllowed')) {
    /**
     * Whether vendors may download order source documents (vs preview-only).
     */
    function vendorDocumentDownloadAllowed(): bool
    {
        $settings = siteSettings();

        if ($settings === null || $settings->vendor_document_download_allowed === null) {
            return false;
        }

        return (bool) $settings->vendor_document_download_allowed;
    }
}

if (! function_exists('orderFeeBreakdown')) {
    /**
     * Split a customer-facing order total into platform fee + vendor net.
     *
     * @return array{
     *     total: float,
     *     platform_fee: float,
     *     vendor_amount: float,
     *     fee_percent: float,
     *     fee_fixed: float
     * }
     */
    function orderFeeBreakdown(float|int|string|null $total): array
    {
        $total = round(max(0, (float) $total), 2);
        $settings = siteSettings();
        $percent = round(max(0, (float) ($settings?->platform_fee_percent ?? 10)), 2);
        $fixed = round(max(0, (float) ($settings?->platform_fee_fixed ?? 0)), 2);
        $fee = round(($total * $percent / 100) + $fixed, 2);
        $fee = min($fee, $total);
        $vendorAmount = round(max(0, $total - $fee), 2);

        return [
            'total' => $total,
            'platform_fee' => $fee,
            'vendor_amount' => $vendorAmount,
            'fee_percent' => $percent,
            'fee_fixed' => $fixed,
        ];
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

if (! function_exists('siteFontFile')) {
    /**
     * Locale-aware uploaded font filename (EN / AR), or null when missing.
     */
    function siteFontFile(?string $locale = null): ?string
    {
        $settings = siteSettings();
        $locale = strtolower(substr((string) ($locale ?: app()->getLocale()), 0, 2));
        $dir = uploadsDir('front');

        $candidates = $locale === 'ar'
            ? [$settings?->font_ar, $settings?->font_en]
            : [$settings?->font_en, $settings?->font_ar];

        foreach ($candidates as $file) {
            if (! is_string($file) || $file === '') {
                continue;
            }

            if (is_file(public_path($dir.$file))) {
                return $file;
            }
        }

        return null;
    }
}

if (! function_exists('siteFontUrl')) {
    /**
     * Absolute URL for the locale-aware uploaded font, or null.
     */
    function siteFontUrl(?string $locale = null): ?string
    {
        $file = siteFontFile($locale);

        return $file ? asset(uploadsDir('front').$file) : null;
    }
}

if (! function_exists('siteFontFormat')) {
    /**
     * CSS @font-face format keyword for a font filename.
     */
    function siteFontFormat(?string $filename): ?string
    {
        if (! is_string($filename) || $filename === '') {
            return null;
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($ext) {
            'woff2' => 'woff2',
            'woff' => 'woff',
            'ttf' => 'truetype',
            'otf' => 'opentype',
            default => null,
        };
    }
}

if (! function_exists('siteFontFamilyName')) {
    /**
     * CSS font-family name for uploaded site fonts.
     */
    function siteFontFamilyName(?string $locale = null): string
    {
        $locale = strtolower(substr((string) ($locale ?: app()->getLocale()), 0, 2));

        return $locale === 'ar' ? 'Tarjuman AR' : 'Tarjuman EN';
    }
}

if (! function_exists('siteFontCssStack')) {
    /**
     * font-family stack for the current (or given) locale.
     */
    function siteFontCssStack(?string $locale = null): string
    {
        $locale = strtolower(substr((string) ($locale ?: app()->getLocale()), 0, 2));
        $fallback = '"Public Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';

        if (! siteFontFile($locale)) {
            return $fallback;
        }

        return '"'.siteFontFamilyName($locale).'", '.$fallback;
    }
}

if (! function_exists('cms_asset_url')) {
    /**
     * Absolute URL for a CMS content asset path.
     */
    function cms_asset_url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/images/')) {
            return rtrim((string) config('cms.frontend_url'), '/').$path;
        }

        if (str_starts_with($path, '/')) {
            return rtrim((string) config('app.url'), '/').$path;
        }

        return asset($path);
    }
}

if (! function_exists('cms_frontend_url')) {
    /**
     * Absolute frontend URL for a preview path.
     */
    function cms_frontend_url(string $path = '/'): string
    {
        $base = rtrim((string) config('cms.frontend_url'), '/');
        $path = '/'.ltrim($path, '/');

        if ($path === '/') {
            return $base.'/';
        }

        return $base.$path;
    }
}
