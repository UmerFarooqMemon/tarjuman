<?php

namespace App\Models;

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
}
