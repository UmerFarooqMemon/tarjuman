<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateDocument extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'estimate_id',
        'filename',
        'extension',
        'pages',
        'words',
        'method',
        'used_fallback',
        'warnings',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'pages' => 'integer',
        'words' => 'integer',
        'used_fallback' => 'boolean',
        'warnings' => 'array',
    ];

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }
}
