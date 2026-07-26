<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vendor extends Model implements TranslatableContract
{
    use Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = [
        'legal_name',
        'business_name',
        'address',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'trn',
        'trade_license_no',
        'trade_license_expiry',
        'moj_registration_no',
        'email',
        'phone',
        'logo',
        'is_active',
        'is_approved',
        'approved_at',
        'approved_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'trade_license_expiry' => 'date',
        'is_active' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(VendorUser::class);
    }

    public function owner(): HasOne
    {
        return $this->hasOne(VendorUser::class)->where('is_owner', true);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function displayName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $this->translate($locale, false)?->business_name
            ?: $this->translate($locale, false)?->legal_name
            ?: $this->translate('en', false)?->legal_name
            ?: $this->email;
    }
}
