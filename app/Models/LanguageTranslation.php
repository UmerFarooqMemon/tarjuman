<?php

namespace App\Models;

use App\Support\CatalogCache;
use Illuminate\Database\Eloquent\Model;

class LanguageTranslation extends Model
{
    public $timestamps = true;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    protected static function booted(): void
    {
        $flush = fn () => CatalogCache::flushLanguages();

        static::saved($flush);
        static::deleted($flush);
    }
}
