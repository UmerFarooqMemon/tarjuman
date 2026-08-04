<?php

namespace App\Models;

use App\Cms\SchemaRegistry;
use App\Support\CmsCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsSection extends Model
{
    protected $fillable = [
        'cms_page_id',
        'type',
        'sort_order',
        'is_enabled',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'content' => 'array',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $section): void {
            $slug = $section->page?->slug ?? CmsPage::query()->whereKey($section->cms_page_id)->value('slug');
            if ($slug) {
                CmsCache::flushPage($slug);
            }
        });

        static::deleted(function (self $section): void {
            $slug = $section->page?->slug ?? CmsPage::query()->whereKey($section->cms_page_id)->value('slug');
            if ($slug) {
                CmsCache::flushPage($slug);
            }
        });
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(CmsPage::class, 'cms_page_id');
    }

    public function schemaLabel(): string
    {
        if (! SchemaRegistry::has($this->type)) {
            return $this->type;
        }

        return SchemaRegistry::get($this->type)->label();
    }
}
