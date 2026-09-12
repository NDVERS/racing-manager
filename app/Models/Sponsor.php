<?php

namespace App\Models;

use Database\Factories\SponsorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'description',
    'tier',
    'min_reputation',
    'signing_bonus',
    'target_objective',
    'bonus_per_race',
    'duration_races',
])]
class Sponsor extends Model
{
    /** @use HasFactory<SponsorFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_reputation' => 'integer',
            'signing_bonus' => 'integer',
            'bonus_per_race' => 'integer',
            'duration_races' => 'integer',
        ];
    }

    /**
     * Get the team sponsor pivot records.
     */
    public function teamSponsors(): HasMany
    {
        return $this->hasMany(TeamSponsor::class);
    }

    /**
     * Get all teams that have signed this sponsor.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_sponsors')
            ->withPivot(['id', 'races_remaining', 'is_active', 'signed_at'])
            ->withTimestamps();
    }

    /**
     * Get a human-readable label for the target objective.
     */
    public function objectiveLabel(): string
    {
        return match ($this->target_objective) {
            'finish_top_3' => 'Podium Finish (P1 - P3)',
            'finish_top_5' => 'Top 5 Finish (P1 - P5)',
            'score_fastest_lap' => 'Score Fastest Lap',
            'finish_race' => 'Complete Race (No DNF)',
            default => ucwords(str_replace('_', ' ', $this->target_objective)),
        };
    }

    /**
     * Get tier label in uppercase.
     */
    public function tierLabel(): string
    {
        return match (strtolower($this->tier)) {
            'primary' => 'PRIMARY TITLE PARTNER',
            'secondary' => 'OFFICIAL TECHNICAL SPONSOR',
            'tertiary' => 'ASSOCIATE SUPPLIER',
            default => strtoupper($this->tier),
        };
    }

    /**
     * Get tier badge styling classes.
     */
    public function tierBadgeClasses(): string
    {
        return match (strtolower($this->tier)) {
            'primary' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
            'secondary' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
            'tertiary' => 'bg-zinc-800 text-zinc-300 border-zinc-700',
            default => 'bg-zinc-800 text-zinc-300 border-zinc-700',
        };
    }
}
