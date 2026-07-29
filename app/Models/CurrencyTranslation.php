<?php

namespace App\Models;

use App\Support\CatalogCache;
use Illuminate\Database\Eloquent\Model;

class CurrencyTranslation extends Model
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
        $flush = fn () => CatalogCache::flushCurrencies();

        static::saved($flush);
        static::deleted($flush);
    }
}
