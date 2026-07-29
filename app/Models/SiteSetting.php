<?php

namespace App\Models;

use App\Support\CatalogCache;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        $flush = fn () => CatalogCache::flushSiteSettings();

        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * Resolve a branding color key into CSS (solid or linear-gradient).
     *
     * Keys: primary_color, secondary_color, primary_button_color, secondary_button_color
     */
    public function brandingBackground(string $key, string $fallback = '#7367F0'): string
    {
        $start = $this->{$key} ?: $fallback;
        $end = $this->{$key.'_end'} ?? null;
        $angle = (int) ($this->{$key.'_angle'} ?? 135);

        if (is_string($end) && preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $end)) {
            $angle = max(0, min(360, $angle));

            return "linear-gradient({$angle}deg, {$start} 0%, {$end} 100%)";
        }

        return $start;
    }

    /**
     * Solid start color for text / accents that cannot use gradients.
     */
    public function brandingSolid(string $key, string $fallback = '#7367F0'): string
    {
        return $this->{$key} ?: $fallback;
    }

    /**
     * Active platform currency code (must exist in the currencies catalog).
     */
    public function currencyCode(): string
    {
        return platformCurrency();
    }

    /**
     * @return array{code: string, name_en: string, name_ar: string, symbol: string, symbol_native: string, decimals: int, country: string, name: string}
     */
    public function currencyMeta(): array
    {
        return currencyMeta($this->currencyCode()) ?? platformCurrencyMeta();
    }
}
