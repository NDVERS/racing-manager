<?php

namespace App\Models;

use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'money', 'reputation'])]
class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'money' => 'integer',
            'reputation' => 'integer',
        ];
    }

    /**
     * Get the user that owns the team.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all cars owned by the team.
     */
    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }

    /**
     * Get all drivers employed by the team.
     */
    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    /**
     * Get all race results for the team.
     */
    public function raceResults(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }

    /**
     * Get all transactions for the team.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the team's designated active race car.
     */
    public function activeCar(): ?Car
    {
        return $this->cars()->where('is_active', true)->first();
    }

    /**
     * Get the team's lead/primary driver.
     */
    public function primaryDriver(): ?Driver
    {
        return $this->drivers()->first();
    }
}
