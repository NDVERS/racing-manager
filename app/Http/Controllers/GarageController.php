<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarUpgrade;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GarageController extends Controller
{
    /**
     * Display the team's garage roster.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $team = $user->team()->with(['cars.upgrades'])->firstOrFail();

        return view('garage.index', [
            'team' => $team,
            'cars' => $team->cars,
            'activeCar' => $team->activeCar(),
        ]);
    }

    /**
     * Display the official constructor chassis showroom & dealership catalog.
     */
    public function dealership(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $templates = Car::whereNull('team_id')
            ->orderBy('purchase_price', 'asc')
            ->get();

        $activeCar = $team->activeCar();
        $ownedCarCounts = $team->cars()
            ->selectRaw('name, count(*) as total')
            ->groupBy('name')
            ->pluck('total', 'name')
            ->toArray();
        $ownedCarNames = array_keys($ownedCarCounts);

        return view('garage.dealership', [
            'team' => $team,
            'templates' => $templates,
            'activeCar' => $activeCar,
            'ownedCarNames' => $ownedCarNames,
            'ownedCarCounts' => $ownedCarCounts,
        ]);
    }

    /**
     * Display the technical inspection sheet of a specific car.
     */
    public function show(Request $request, Car $car): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        // Scoping / Authorization: Car must belong to the authenticated user's team
        if ($car->team_id !== $team->id) {
            abort(404, 'Car not found in team garage.');
        }

        $car->load('upgrades');

        $performanceRating = (int) round(
            ($car->speed + $car->acceleration + $car->handling + $car->braking + $car->reliability) / 5
        );

        $partsMatrix = [];
        foreach (CarUpgrade::PARTS as $key => $config) {
            $installed = $car->upgrades->firstWhere('part_type', $key);
            $currentLevel = $installed ? (int) $installed->level : 0;
            $isMax = $currentLevel >= CarUpgrade::MAX_LEVEL;
            $nextLevel = $isMax ? null : $currentLevel + 1;
            $nextCost = $isMax ? null : CarUpgrade::getCostForLevel($key, $nextLevel);
            $statKey = $config['stat'];
            $currentStat = (int) $car->{$statKey};
            $projectedStat = min(100, $currentStat + $config['delta']);
            $canAfford = ! $isMax && ($team->money >= $nextCost);

            $partsMatrix[$key] = [
                'key' => $key,
                'name' => $config['name'],
                'stat' => $statKey,
                'stat_name' => $config['stat_name'],
                'delta' => $config['delta'],
                'description' => $config['description'],
                'current_level' => $currentLevel,
                'max_level' => CarUpgrade::MAX_LEVEL,
                'is_max' => $isMax,
                'next_level' => $nextLevel,
                'next_cost' => $nextCost,
                'current_stat' => $currentStat,
                'projected_stat' => $projectedStat,
                'can_afford' => $canAfford,
            ];
        }

        return view('garage.show', [
            'team' => $team,
            'car' => $car,
            'performanceRating' => $performanceRating,
            'upgrades' => $car->upgrades,
            'partsMatrix' => $partsMatrix,
        ]);
    }

    /**
     * Purchase and install a component upgrade package.
     */
    public function purchaseUpgrade(Request $request, Car $car): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        // Scoping / Authorization: Car must belong to the authenticated user's team
        if ($car->team_id !== $team->id) {
            abort(404, 'Car not found in team garage.');
        }

        $allowedParts = implode(',', array_keys(CarUpgrade::PARTS));
        $validated = $request->validate([
            'part_type' => ['required', 'string', 'in:'.$allowedParts],
        ]);

        $partType = $validated['part_type'];
        $partConfig = CarUpgrade::PARTS[$partType];

        $existingUpgrade = $car->upgrades()->where('part_type', $partType)->first();
        $currentLevel = $existingUpgrade ? (int) $existingUpgrade->level : 0;

        if ($currentLevel >= CarUpgrade::MAX_LEVEL) {
            return redirect()->back()
                ->with('error', "{$partConfig['name']} is already at maximum engineering tier (Level ".CarUpgrade::MAX_LEVEL.').');
        }

        $targetLevel = $currentLevel + 1;
        $cost = CarUpgrade::getCostForLevel($partType, $targetLevel);

        if ($team->money < $cost) {
            return redirect()->back()
                ->with('error', 'Insufficient team treasury funds. Required: '.number_format($cost).' CR, Available: '.number_format($team->money).' CR.');
        }

        DB::transaction(function () use ($team, $car, $partType, $partConfig, $targetLevel, $cost, $existingUpgrade) {
            // 1. Deduct cost from team treasury
            $team->decrement('money', $cost);
            $team->refresh();

            // 2. Record expense transaction
            Transaction::create([
                'team_id' => $team->id,
                'type' => 'expense',
                'amount' => -$cost,
                'balance_after' => $team->money,
                'description' => "R&D Upgrade: {$partConfig['name']} (Tier {$targetLevel}) on {$car->name}",
                'reference_id' => $car->id,
                'reference_type' => Car::class,
            ]);

            // 3. Create or update car_upgrades record
            $statKey = $partConfig['stat'];
            $statDelta = $partConfig['delta'];
            $totalStatIncrease = $targetLevel * $statDelta;
            $cumulativeCost = ($existingUpgrade ? (int) $existingUpgrade->cost : 0) + $cost;

            CarUpgrade::updateOrCreate(
                [
                    'car_id' => $car->id,
                    'part_type' => $partType,
                ],
                [
                    'level' => $targetLevel,
                    'cost' => $cumulativeCost,
                    'stat_increases' => [$statKey => $totalStatIncrease],
                ]
            );

            // 4. Boost car attribute stat (capped at 100)
            $newStatVal = min(100, (int) $car->{$statKey} + $statDelta);
            $car->{$statKey} = $newStatVal;

            // 5. Update overall car level tier based on total upgrade levels
            $otherUpgradesTotal = (int) $car->upgrades()->where('part_type', '!=', $partType)->sum('level');
            $totalLevels = $otherUpgradesTotal + $targetLevel;
            $car->level = 1 + (int) floor($totalLevels / 3);

            $car->save();
        });

        return redirect()->route('garage.show', $car)
            ->with('success', "Upgraded {$partConfig['name']} to Level {$targetLevel}! +{$partConfig['delta']} ".strtoupper($partConfig['stat_name']));
    }

    /**
     * Purchase and commission a new constructor chassis from the showroom.
     */
    public function buy(Request $request, Car $car): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        // Validation 1: Sasis template valid (team_id MUST be null)
        if ($car->team_id !== null) {
            return redirect()->route('garage.dealership')
                ->with('error', "{$car->name} is a privately commissioned chassis and cannot be acquired from the dealership.");
        }

        $purchasePrice = (int) ($car->purchase_price ?? 30000);

        // Validation 2: Saldo mencukupi
        if ($team->money < $purchasePrice) {
            return redirect()->route('garage.dealership')
                ->with('error', 'Insufficient credits in team treasury to acquire '.$car->name.'. Required: '.number_format($purchasePrice).' CR, Available: '.number_format($team->money).' CR.');
        }

        // Validation 3: Cegah kepemilikan lebih dari 2 unit sasis untuk model yang sama (dukungan 2-Car Entry)
        $ownedCount = $team->cars()->where('name', $car->name)->count();
        if ($ownedCount >= 2) {
            return redirect()->route('garage.dealership')
                ->with('warning', "You already own the maximum allowed 2 units of {$car->name} in your constructor fleet.");
        }

        DB::transaction(function () use ($team, $car, $purchasePrice) {
            // 1. Potong saldo tim
            $team->decrement('money', $purchasePrice);
            $team->refresh();

            // 2. Catat transaksi di tabel transactions
            Transaction::create([
                'team_id' => $team->id,
                'type' => 'expense',
                'amount' => -$purchasePrice,
                'balance_after' => $team->money,
                'description' => "Chassis Acquisition: {$car->name}",
                'reference_id' => $car->id,
                'reference_type' => Car::class,
            ]);

            // 3. Replicate template car menjadi mobil baru milik tim
            $newCar = $car->replicate();
            $newCar->team_id = $team->id;

            // If team doesn't have car in slot 1, assign to slot 1. Else if slot 2 is empty, assign to slot 2.
            $hasSlot1 = $team->cars()->where('slot', 1)->exists() || $team->cars()->where('is_active', true)->exists();
            $hasSlot2 = $team->cars()->where('slot', 2)->exists();

            if (! $hasSlot1) {
                $newCar->slot = 1;
                $newCar->is_active = true;
            } elseif (! $hasSlot2) {
                $newCar->slot = 2;
                $newCar->is_active = false;
            } else {
                $newCar->slot = null;
                $newCar->is_active = false;
            }

            $newCar->level = 1;
            $newCar->save();
        });

        return redirect()->route('garage.index')
            ->with('success', "Congratulations! {$car->name} chassis has been commissioned to your constructor fleet for ".number_format($purchasePrice).' CR.');
    }

    /**
     * Set a car as the team's active race vehicle (legacy slot 1).
     */
    public function setActive(Request $request, Car $car): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        // Scoping / Authorization: Car must belong to the authenticated user's team
        if ($car->team_id !== $team->id) {
            abort(404, 'Car not found in team garage.');
        }

        DB::transaction(function () use ($team, $car) {
            $team->cars()->where('slot', 1)->update(['slot' => null, 'is_active' => false]);
            $team->cars()->update(['is_active' => false]);
            $car->update(['is_active' => true, 'slot' => 1]);
        });

        return redirect()->back()
            ->with('success', "{$car->name} is now designated as your active primary race car (Car #1).");
    }

    /**
     * Assign a car to a specific race slot (Slot 1, Slot 2, or unassigned).
     */
    public function assignSlot(Request $request, Car $car): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        // Scoping / Authorization: Car must belong to the authenticated user's team
        if ($car->team_id !== $team->id) {
            abort(404, 'Car not found in team garage.');
        }

        $validated = $request->validate([
            'slot' => ['nullable', 'integer', 'in:1,2,0'],
        ]);

        $targetSlot = isset($validated['slot']) && (int) $validated['slot'] > 0 ? (int) $validated['slot'] : null;

        DB::transaction(function () use ($team, $car, $targetSlot) {
            if ($targetSlot !== null) {
                // Remove this slot from any other car in the team
                $team->cars()->where('slot', $targetSlot)->update(['slot' => null]);

                if ($targetSlot === 1) {
                    $team->cars()->update(['is_active' => false]);
                    $car->update(['slot' => 1, 'is_active' => true]);
                } else {
                    $car->update(['slot' => $targetSlot, 'is_active' => false]);
                }
            } else {
                $car->update(['slot' => null, 'is_active' => false]);
            }
        });

        $message = $targetSlot !== null
            ? "{$car->name} has been assigned to Entry Slot #{$targetSlot}."
            : "{$car->name} has been unassigned to reserve fleet.";

        return redirect()->back()->with('success', $message);
    }
}
