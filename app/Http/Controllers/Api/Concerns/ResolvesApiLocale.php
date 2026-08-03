<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\Request;

trait ResolvesApiLocale
{
    /**
     * Resolve UI locale for catalog labels (en/ar).
     * Accepts ?locale= or Accept-Language; falls back to en.
     */
    protected function apiLocale(Request $request): string
    {
        $raw = (string) ($request->query('locale') ?: $request->header('Accept-Language', ''));
        $locale = strtolower(substr(trim(explode(',', $raw)[0] ?? ''), 0, 2));

        $allowed = crudLocaleCodes();

        if ($locale !== '' && in_array($locale, $allowed, true)) {
            return $locale;
        }

        return $allowed[0] ?? 'en';
    }

    /**
     * @return array{code: string, icon_url: string|null}
     */
    protected function currencyPayload(): array
    {
        return [
            'code' => platformCurrency(),
            'icon_url' => currencyIconUrl(),
        ];
    }
}
