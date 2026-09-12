<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RaceController extends Controller
{
    /**
     * Display the Grand Prix championship race calendar.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $races = Race::all();
        $teamResults = RaceResult::where('team_id', $team->id)->get()->keyBy('race_id');

        $activeCar = $team->activeCar();
        $primaryDriver = $team->primaryDriver();
        $isRaceReady = ($activeCar !== null && $primaryDriver !== null);

        return view('races.index', [
            'team' => $team,
            'races' => $races,
            'teamResults' => $teamResults,
            'activeCar' => $activeCar,
            'primaryDriver' => $primaryDriver,
            'isRaceReady' => $isRaceReady,
        ]);
    }

    /**
     * Display the pre-race preparation and briefing hub for a specific race.
     */
    public function show(Request $request, Race $race): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $activeCar = $team->activeCar();
        $primaryDriver = $team->primaryDriver();

        $hasActiveCar = ($activeCar !== null);
        $hasPrimaryDriver = ($primaryDriver !== null);
        $hasEnoughFunds = ($team->money >= $race->entry_fee);

        $isReady = $hasActiveCar && $hasPrimaryDriver && $hasEnoughFunds;

        $pastResult = RaceResult::where('race_id', $race->id)
            ->where('team_id', $team->id)
            ->latest()
            ->first();

        return view('races.show', [
            'team' => $team,
            'race' => $race,
            'activeCar' => $activeCar,
            'primaryDriver' => $primaryDriver,
            'hasActiveCar' => $hasActiveCar,
            'hasPrimaryDriver' => $hasPrimaryDriver,
            'hasEnoughFunds' => $hasEnoughFunds,
            'isReady' => $isReady,
            'pastResult' => $pastResult,
        ]);
    }

    /**
     * Confirm entry setup / register lineup for the race.
     */
    public function enter(Request $request, Race $race): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $activeCar = $team->activeCar();
        if (! $activeCar) {
            return redirect()->route('garage.index')
                ->with('warning', 'You must designate an active primary race car before entering a race.');
        }

        $primaryDriver = $team->primaryDriver();
        if (! $primaryDriver) {
            return redirect()->route('drivers.index')
                ->with('warning', 'You must assign a lead race driver before entering a race.');
        }

        if ($team->money < $race->entry_fee) {
            return redirect()->route('races.show', $race)
                ->with('warning', 'Insufficient credits in team treasury to pay entry fee of '.number_format($race->entry_fee).' CR.');
        }

        return redirect()->route('races.show', $race)
            ->with('success', "Race setup confirmed for {$race->name}! Vehicle: {$activeCar->name} | Driver: {$primaryDriver->name}. Ready for Grand Prix simulation.");
    }
}
