<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model implements TranslatableContract
{
    use Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = [
        'name',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'price_amount',
        'currency',
        'billing_period',
        'delivery_speed_id',
        'page_quota',
        'word_quota',
        'is_active',
        'sort_order',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price_amount' => 'decimal:2',
        'page_quota' => 'integer',
        'word_quota' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(EnterpriseSubscription::class);
    }

    public function deliverySpeed(): BelongsTo
    {
        return $this->belongsTo(DeliverySpeed::class);
    }

    public function addOns(): BelongsToMany
    {
        return $this->belongsToMany(AddOn::class, 'plan_add_on')
            ->withTimestamps();
    }

    public function displayName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return (string) ($this->{'name:'.$locale} ?: $this->name ?: '');
    }

    public function quotaLabel(): string
    {
        return trim(sprintf(
            '%s %s | %s %s',
            number_format((int) $this->page_quota),
            __('general.platform_quota_page'),
            number_format((int) $this->word_quota),
            __('general.platform_quota_word')
        ));
    }
}
