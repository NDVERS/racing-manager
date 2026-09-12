<?php

namespace App\Models;

use Database\Factories\CarFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'team_id',
    'name',
    'speed',
    'acceleration',
    'handling',
    'braking',
    'reliability',
    'level',
    'purchase_price',
    'is_active',
    'slot',
])]
class Car extends Model
{
    /** @use HasFactory<CarFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'speed' => 'integer',
            'acceleration' => 'integer',
            'handling' => 'integer',
            'braking' => 'integer',
            'reliability' => 'integer',
            'level' => 'integer',
            'purchase_price' => 'integer',
            'is_active' => 'boolean',
            'slot' => 'integer',
        ];
    }

    /**
     * Get the team that owns the car.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get all upgrades applied to the car.
     */
    public function upgrades(): HasMany
    {
        return $this->hasMany(CarUpgrade::class);
    }

    /**
     * Get all race results featuring this car.
     */
    public function raceResults(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }
}
