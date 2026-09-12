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

    public const MAX_LEVEL = 5;

    public const PARTS = [
        'engine' => [
            'name' => 'Powertrain & Turbo',
            'stat' => 'speed',
            'stat_name' => 'Top Speed',
            'delta' => 3,
            'description' => 'Upgrades engine displacement, forced induction turbo pressure, and peak RPM ceiling.',
            'costs' => [1 => 3000, 2 => 6000, 3 => 10000, 4 => 15000, 5 => 22000],
        ],
        'transmission' => [
            'name' => 'Drivetrain & Gearbox',
            'stat' => 'acceleration',
            'stat_name' => 'Acceleration',
            'delta' => 3,
            'description' => 'Lightweight flywheel, quick-shift sequential gears, and optimized launch torque delivery.',
            'costs' => [1 => 3000, 2 => 6000, 3 => 10000, 4 => 15000, 5 => 22000],
        ],
        'aerodynamics' => [
            'name' => 'Aero Package & Downforce',
            'stat' => 'handling',
            'stat_name' => 'Handling',
            'delta' => 3,
            'description' => 'Front carbon splitter, rear high-downforce wing, and underbody Venturi ground effect tunnels.',
            'costs' => [1 => 3000, 2 => 6000, 3 => 10000, 4 => 15000, 5 => 22000],
        ],
        'brakes' => [
            'name' => 'Carbon-Ceramic Braking System',
            'stat' => 'braking',
            'stat_name' => 'Braking',
            'delta' => 3,
            'description' => 'Multi-piston monobloc calipers, ventilated carbon discs, and high-temp cooling ducts.',
            'costs' => [1 => 3000, 2 => 6000, 3 => 10000, 4 => 15000, 5 => 22000],
        ],
        'reliability' => [
            'name' => 'Cooling & Structural Durability',
            'stat' => 'reliability',
            'stat_name' => 'Reliability',
            'delta' => 4,
            'description' => 'Reinforced engine block, titanium hardware, high-flow radiators, and heat shielding.',
            'costs' => [1 => 2500, 2 => 5000, 3 => 8500, 4 => 13000, 5 => 19000],
        ],
    ];

    /**
     * Get the upgrade cost for a specific part type and target level.
     */
    public static function getCostForLevel(string $partType, int $level): int
    {
        return self::PARTS[$partType]['costs'][$level] ?? (3000 * $level);
    }

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
