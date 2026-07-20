<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * Locale-aware display label.
     */
    public function getLabelAttribute(): string
    {
        return $this->translatedName() ?: $this->name;
    }

    /**
     * Locale-aware module label.
     */
    public function getModuleLabelAttribute(): string
    {
        $label = $this->translatedModuleName();

        if ($label) {
            return $label;
        }

        return $this->module
            ? str_replace('_', ' ', ucfirst($this->module))
            : '';
    }

    public function translatedName(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'ar' => $this->name_ar ?: $this->name_en,
            default => $this->name_en ?: $this->name_ar,
        };
    }

    public function translatedModuleName(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'ar' => $this->module_ar ?: $this->module_en,
            default => $this->module_en ?: $this->module_ar,
        };
    }
}
