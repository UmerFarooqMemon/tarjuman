<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionUsageEvent extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'enterprise_subscription_id',
        'order_id',
        'amount',
        'quota_unit',
        'type',
        'meta',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'integer',
        'meta' => 'array',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(EnterpriseSubscription::class, 'enterprise_subscription_id');
    }
}
