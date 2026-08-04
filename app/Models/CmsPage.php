<?php

namespace App\Models;

use App\Support\CmsCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsPage extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'preview_path',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn (self $page) => CmsCache::flushPage($page->slug));
        static::deleted(fn (self $page) => CmsCache::flushPage($page->slug));
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CmsSection::class)->orderBy('sort_order');
    }

    public function enabledSections(): HasMany
    {
        return $this->sections()->where('is_enabled', true);
    }
}
