<?php

namespace App\Models;

use Database\Factories\RaceResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'race_id',
    'team_id',
    'car_id',
    'driver_id',
    'position',
    'race_time',
    'prize_money',
    'reputation_earned',
    'strategy',
    'status',
    'simulation_log',
])]
class RaceResult extends Model
{
    /** @use HasFactory<RaceResultFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'prize_money' => 'integer',
            'reputation_earned' => 'integer',
            'simulation_log' => 'array',
        ];
    }

    /**
     * Get the race event for this result.
     */
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    /**
     * Get the team for this result.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the car used in this race result.
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the driver who raced in this result.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
