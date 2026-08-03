<?php

namespace App\Models;

use App\Support\CatalogCache;
use Illuminate\Database\Eloquent\Model;

class DeliverySpeedTranslation extends Model
{
    public $timestamps = true;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'duration_label',
    ];

    protected static function booted(): void
    {
        $flush = fn () => CatalogCache::flushDeliverySpeeds();

        static::saved($flush);
        static::deleted($flush);
    }
}
