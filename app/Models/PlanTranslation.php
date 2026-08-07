<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanTranslation extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'plan_id',
        'locale',
        'name',
    ];
}
