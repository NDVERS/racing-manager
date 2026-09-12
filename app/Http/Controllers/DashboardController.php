<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the team manager dashboard.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $team = $user->team()->with(['cars.upgrades', 'drivers'])->firstOrFail();

        $activeCar = $team->activeCar();
        $primaryDriver = $team->primaryDriver();
        $isRaceReady = ($activeCar !== null && $primaryDriver !== null);

        return view('dashboard.index', [
            'team' => $team,
            'activeCar' => $activeCar,
            'primaryDriver' => $primaryDriver,
            'isRaceReady' => $isRaceReady,
            'totalCars' => $team->cars->count(),
            'totalDrivers' => $team->drivers->count(),
        ]);
    }
}
