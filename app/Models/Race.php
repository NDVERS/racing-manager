<?php

namespace App\Models;

use Database\Factories\RaceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'location',
    'laps',
    'track_type',
    'weather',
    'entry_fee',
    'prize_pool',
])]
class Race extends Model
{
    /** @use HasFactory<RaceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'laps' => 'integer',
            'entry_fee' => 'integer',
            'prize_pool' => 'integer',
        ];
    }

    /**
     * Get all race results for this race.
     */
    public function results(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }
}
