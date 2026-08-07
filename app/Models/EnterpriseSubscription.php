<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnterpriseSubscription extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXHAUSTED = 'exhausted';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_PAST_DUE = 'past_due';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'current_period_start',
        'current_period_end',
        'pages_total',
        'pages_used',
        'words_total',
        'words_used',
        'payment_gateway',
        'payment_tran_ref',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'pages_total' => 'integer',
        'pages_used' => 'integer',
        'words_total' => 'integer',
        'words_used' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function usageEvents(): HasMany
    {
        return $this->hasMany(SubscriptionUsageEvent::class);
    }

    public function remainingPages(): int
    {
        return max(0, (int) $this->pages_total - (int) $this->pages_used);
    }

    public function remainingWords(): int
    {
        return max(0, (int) $this->words_total - (int) $this->words_used);
    }

    public function isExhausted(): bool
    {
        return $this->remainingPages() <= 0 || $this->remainingWords() <= 0;
    }

    public function canCover(int $pages, int $words): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $pages <= $this->remainingPages()
            && $words <= $this->remainingWords();
    }
}
