<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateAddOn extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'estimate_id',
        'add_on_id',
        'name',
        'pricing_mode',
        'unit_amount',
        'quantity',
        'amount',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'unit_amount' => 'decimal:4',
        'quantity' => 'integer',
        'amount' => 'decimal:4',
    ];

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function addOn(): BelongsTo
    {
        return $this->belongsTo(AddOn::class);
    }
}
