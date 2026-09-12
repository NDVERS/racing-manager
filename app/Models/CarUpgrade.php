<?php

namespace App\Models;

use Database\Factories\CarUpgradeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'car_id',
    'part_type',
    'level',
    'cost',
    'stat_increases',
])]
class CarUpgrade extends Model
{
    /** @use HasFactory<CarUpgradeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'cost' => 'integer',
            'stat_increases' => 'array',
        ];
    }

    /**
     * Get the car that received this upgrade.
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
