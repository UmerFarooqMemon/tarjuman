<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderDocument extends Model
{
    public const KIND_SOURCE = 'source';

    public const KIND_DELIVERY = 'delivery';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'order_id',
        'kind',
        'disk_path',
        'original_name',
        'mime',
        'checksum_sha256',
        'encryption',
        'size',
        'pages',
        'words',
        'amount',
        'retained_until',
        'purged_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'pages' => 'integer',
        'words' => 'integer',
        'amount' => 'decimal:2',
        'retained_until' => 'datetime',
        'purged_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (OrderDocument $document): void {
            if (empty($document->uuid)) {
                $document->uuid = (string) Str::uuid();
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isPurged(): bool
    {
        return $this->purged_at !== null;
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
