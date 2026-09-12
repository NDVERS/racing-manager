<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Services\ChampionshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChampionshipController extends Controller
{
    /**
     * Display the official Season Championship Standings hub.
     */
    public function index(Request $request, ChampionshipService $championshipService): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $availableSeasons = $team->availableSeasons();
        $requestedSeason = $request->query('season');
        $selectedSeason = $requestedSeason ? (int) $requestedSeason : (int) $team->current_season;

        if (! in_array($selectedSeason, $availableSeasons, true)) {
            $selectedSeason = (int) $team->current_season;
        }

        $seasonOverview = $championshipService->getSeasonOverview($team, $selectedSeason);
        $constructorsStandings = $championshipService->getConstructorsStandings($team, $selectedSeason);
        $driversStandings = $championshipService->getDriversStandings($team, $selectedSeason);

        return view('standings.index', [
            'team' => $team,
            'season' => $seasonOverview,
            'constructorsStandings' => $constructorsStandings,
            'driversStandings' => $driversStandings,
            'availableSeasons' => $availableSeasons,
            'selectedSeason' => $selectedSeason,
        ]);
    }
}
