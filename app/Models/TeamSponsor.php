<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'team_id',
    'sponsor_id',
    'races_remaining',
    'is_active',
    'signed_at',
])]
class TeamSponsor extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'races_remaining' => 'integer',
            'is_active' => 'boolean',
            'signed_at' => 'datetime',
        ];
    }

    /**
     * Get the team that owns the contract.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the sponsor entity for this contract.
     */
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }
}
