<?php

namespace App\Models;

use Database\Factories\DriverFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'team_id',
    'name',
    'pace',
    'cornering',
    'consistency',
    'overtaking',
    'defensive',
    'racecraft',
    'experience',
    'salary',
    'is_lead',
    'slot',
])]
class Driver extends Model
{
    /** @use HasFactory<DriverFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pace' => 'integer',
            'cornering' => 'integer',
            'consistency' => 'integer',
            'overtaking' => 'integer',
            'defensive' => 'integer',
            'racecraft' => 'integer',
            'experience' => 'integer',
            'salary' => 'integer',
            'is_lead' => 'boolean',
            'slot' => 'integer',
        ];
    }

    /**
     * Get the team that employs the driver.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get all race results featuring this driver.
     */
    public function raceResults(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }

    /**
     * Calculate composite overall rating (OVR) across all 7 core driver attributes.
     */
    public function overallRating(): int
    {
        return (int) round(
            ($this->pace + $this->cornering + $this->consistency + $this->overtaking + $this->defensive + $this->racecraft + $this->experience) / 7
        );
    }

    /**
     * Calculate signing bonus / hiring fee for recruiting this driver from the market.
     */
    public function hiringCost(): int
    {
        return $this->salary * 3;
    }
}
