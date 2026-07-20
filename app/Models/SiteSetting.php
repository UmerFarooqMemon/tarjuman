<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $guarded = [];

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
}
