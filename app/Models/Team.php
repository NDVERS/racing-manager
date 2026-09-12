<?php

namespace App\Models;

use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'money', 'reputation', 'current_season'])]
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
            'current_season' => 'integer',
        ];
    }

    /**
     * Get the team's available credits balance (alias of money).
     */
    public function getCreditsAttribute(): int
    {
        return (int) $this->money;
    }

    /**
     * Set the team's credits balance.
     */
    public function setCreditsAttribute(int $value): void
    {
        $this->attributes['money'] = $value;
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
     * Get all sponsor contract pivot records for the team.
     */
    public function teamSponsors(): HasMany
    {
        return $this->hasMany(TeamSponsor::class);
    }

    /**
     * Get all sponsors signed by the team.
     */
    public function sponsors()
    {
        return $this->belongsToMany(Sponsor::class, 'team_sponsors')
            ->withPivot(['id', 'races_remaining', 'is_active', 'signed_at'])
            ->withTimestamps();
    }

    /**
     * Get all currently active sponsor contracts with sponsor details.
     */
    public function activeSponsors(): HasMany
    {
        return $this->teamSponsors()->where('is_active', true)->with('sponsor');
    }

    /**
     * Check if team has an active contract with a specific sponsor.
     */
    public function hasActiveSponsor(int $sponsorId): bool
    {
        return $this->teamSponsors()
            ->where('sponsor_id', $sponsorId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Determine if team has available slot to sign a given sponsor.
     * Limit: Max 1 Primary Sponsor, Max 2 Secondary/Tertiary Sponsors.
     */
    public function canSignSponsor(Sponsor $sponsor): bool
    {
        if ($this->reputation < $sponsor->min_reputation) {
            return false;
        }

        if ($this->hasActiveSponsor($sponsor->id)) {
            return false;
        }

        $activePrimaryCount = $this->teamSponsors()
            ->where('is_active', true)
            ->whereHas('sponsor', fn ($q) => $q->where('tier', 'primary'))
            ->count();

        $activeSecondaryCount = $this->teamSponsors()
            ->where('is_active', true)
            ->whereHas('sponsor', fn ($q) => $q->whereIn('tier', ['secondary', 'tertiary']))
            ->count();

        if (strtolower($sponsor->tier) === 'primary') {
            return $activePrimaryCount < 1;
        }

        return $activeSecondaryCount < 2;
    }

    /**
     * Get the team's designated active race car (Slot 1 or fallback to active).
     */
    public function car1(): ?Car
    {
        return $this->cars()->where('slot', 1)->first() ?? $this->cars()->where('is_active', true)->first();
    }

    /**
     * Get the team's secondary race car (Slot 2).
     */
    public function car2(): ?Car
    {
        return $this->cars()->where('slot', 2)->first();
    }

    /**
     * Get all currently assigned active race cars for the team.
     */
    public function activeCars()
    {
        $cars = $this->cars()->whereIn('slot', [1, 2])->get();
        if ($cars->isEmpty()) {
            return $this->cars()->where('is_active', true)->get();
        }

        return $cars;
    }

    /**
     * Get the team's lead/primary driver (Slot 1 or fallback to lead).
     */
    public function driver1(): ?Driver
    {
        return $this->drivers()->where('slot', 1)->first() ?? $this->drivers()->where('is_lead', true)->first();
    }

    /**
     * Get the team's secondary driver (Slot 2).
     */
    public function driver2(): ?Driver
    {
        return $this->drivers()->where('slot', 2)->first();
    }

    /**
     * Get all currently assigned active race drivers for the team.
     */
    public function activeDrivers()
    {
        $drivers = $this->drivers()->whereIn('slot', [1, 2])->get();
        if ($drivers->isEmpty()) {
            return $this->drivers()->where('is_lead', true)->get();
        }

        return $drivers;
    }

    /**
     * Determine if team has both Car 1 and Car 2, and Driver 1 and Driver 2 assigned for full 2-car entry.
     */
    public function isTwoCarReady(): bool
    {
        return ! is_null($this->car1()) && ! is_null($this->car2()) && ! is_null($this->driver1()) && ! is_null($this->driver2());
    }

    /**
     * Get the team's designated active race car (backward compatibility).
     */
    public function activeCar(): ?Car
    {
        return $this->car1();
    }

    /**
     * Get the team's lead/primary driver (backward compatibility).
     */
    public function primaryDriver(): ?Driver
    {
        return $this->driver1();
    }

    /**
     * Check if all scheduled championship Grand Prix races for the current season have been completed.
     */
    public function isCurrentSeasonCompleted(): bool
    {
        $totalRaces = Race::count();
        if ($totalRaces === 0) {
            return false;
        }

        $completedRacesCount = $this->raceResults()
            ->where('season', $this->current_season)
            ->distinct('race_id')
            ->count('race_id');

        return $completedRacesCount >= $totalRaces;
    }

    /**
     * Get list of all available seasons for this team (from 1 up to current_season).
     *
     * @return array<int, int>
     */
    public function availableSeasons(): array
    {
        $maxSeason = max(1, (int) $this->current_season);

        return range(1, $maxSeason);
    }
}
