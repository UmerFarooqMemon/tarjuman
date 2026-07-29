<?php

namespace App\Models;

use App\Support\CatalogCache;
use Illuminate\Database\Eloquent\Model;

class VendorTranslation extends Model
{
    public $timestamps = true;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'legal_name',
        'business_name',
        'address',
    ];

    protected static function booted(): void
    {
        $flush = function (VendorTranslation $translation) {
            CatalogCache::flushVendors(
                $translation->vendor_id ? (int) $translation->vendor_id : null
            );
        };

        static::saved($flush);
        static::deleted($flush);
    }
}
