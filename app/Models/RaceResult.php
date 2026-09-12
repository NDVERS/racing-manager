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
     * Get championship points awarded for this finish position.
     */
    public function getPointsAttribute(): int
    {
        $pointsMap = [
            1 => 25,
            2 => 18,
            3 => 15,
            4 => 12,
            5 => 10,
            6 => 8,
            7 => 6,
            8 => 4,
            9 => 2,
            10 => 1,
        ];

        return $pointsMap[$this->position] ?? 0;
    }

    /**
     * Determine if this result was a race victory (P1).
     */
    public function getIsWinAttribute(): bool
    {
        return $this->position === 1;
    }

    /**
     * Determine if this result was a podium finish (P1 - P3).
     */
    public function getIsPodiumAttribute(): bool
    {
        return $this->position >= 1 && $this->position <= 3;
    }

    /**
     * Get the driver who raced in this result.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
