<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class VendorUser extends Authenticatable
{
    use Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vendor_id',
        'first_name',
        'last_name',
        'phone',
        'image',
        'email',
        'password',
        'is_active',
        'is_owner',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_owner' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.($this->last_name ?? ''));
    }
}
