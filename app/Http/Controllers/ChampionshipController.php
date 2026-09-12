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

        $seasonOverview = $championshipService->getSeasonOverview($team);
        $constructorsStandings = $championshipService->getConstructorsStandings($team);
        $driversStandings = $championshipService->getDriversStandings($team);

        return view('standings.index', [
            'team' => $team,
            'season' => $seasonOverview,
            'constructorsStandings' => $constructorsStandings,
            'driversStandings' => $driversStandings,
        ]);
    }
}
