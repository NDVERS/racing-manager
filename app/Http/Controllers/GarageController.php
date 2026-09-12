<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return view('garage.show', [
            'team' => $team,
            'car' => $car,
            'performanceRating' => $performanceRating,
            'upgrades' => $car->upgrades,
        ]);
    }

    /**
     * Set a car as the team's active race vehicle.
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

        // Deactivate all other cars and activate this one
        $team->cars()->update(['is_active' => false]);
        $car->update(['is_active' => true]);

        return redirect()->back()
            ->with('success', "{$car->name} is now designated as your active primary race car.");
    }
}
