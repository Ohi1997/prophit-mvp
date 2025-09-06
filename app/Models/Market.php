<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Market extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'market_id',
        'question',
    ];

    /**
     * Get the price history snapshots for the market.
     */
    public function snapshots(): HasMany
    {
        return $this->hasMany(MarketSnapshot::class);
    }
}