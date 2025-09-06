<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketSnapshot extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'market_id',
        'probability',
        'volume',
        'recorded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    /**
     * Get the market that this snapshot belongs to.
     */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }
}