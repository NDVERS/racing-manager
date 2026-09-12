<?php

namespace App\Http\Controllers;

use App\Models\RaceResult;
use App\Models\Team;
use App\Models\User;
use App\Services\ChampionshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HallOfFameController extends Controller
{
    /**
     * Display the official Team Trophy Cabinet & Hall of Fame showcase.
     */
    public function index(Request $request, ChampionshipService $championshipService): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $allResults = RaceResult::where('team_id', $team->id)
            ->with(['race', 'car', 'driver'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRaces = $allResults->count();
        $totalWins = $allResults->where('position', 1)->count();
        $totalPodiums = $allResults->whereIn('position', [1, 2, 3])->count();
        $totalPrizeMoney = (int) $allResults->sum('prize_money');
        $totalReputationEarned = (int) $allResults->sum('reputation_earned');

        $driverPointsMap = [
            1 => 25, 2 => 18, 3 => 15, 4 => 12, 5 => 10,
            6 => 8, 7 => 6, 8 => 4, 9 => 2, 10 => 1,
        ];
        $totalChampionshipPoints = 0;
        foreach ($allResults as $res) {
            $totalChampionshipPoints += $driverPointsMap[$res->position] ?? 0;
        }

        $totalCareerEarnings = (int) $team->transactions()
            ->where('type', 'income')
            ->sum('amount');

        $winRate = $totalRaces > 0 ? round(($totalWins / $totalRaces) * 100, 1) : 0;
        $podiumRate = $totalRaces > 0 ? round(($totalPodiums / $totalRaces) * 100, 1) : 0;

        // 1. Grand Prix Victory Trophies (Individual P1 race wins)
        $gpVictories = $allResults->where('position', 1)->map(function ($result) {
            return [
                'race_name' => $result->race->name ?? 'Grand Prix',
                'location' => $result->race->location ?? 'Unknown',
                'season' => $result->season,
                'driver_name' => $result->driver->name ?? 'Lead Driver',
                'car_name' => $result->car->name ?? 'Race Chassis',
                'car_slot' => $result->car_slot ?? 1,
                'race_time' => $result->race_time,
                'prize_money' => $result->prize_money,
                'date' => $result->created_at->format('M Y'),
            ];
        })->values();

        // 2. Multi-Season Championship Titles Evaluation (WCC & WDC)
        $wccTitles = [];
        $wdcTitles = [];
        $availableSeasons = $team->availableSeasons();

        foreach ($availableSeasons as $season) {
            $constructorsStandings = $championshipService->getConstructorsStandings($team, $season);
            if (! empty($constructorsStandings) && $constructorsStandings[0]['is_player']) {
                $wccTitles[] = [
                    'season' => $season,
                    'team_name' => $team->name,
                    'points' => $constructorsStandings[0]['points'],
                    'wins' => $constructorsStandings[0]['wins'],
                ];
            }

            $driversStandings = $championshipService->getDriversStandings($team, $season);
            if (! empty($driversStandings) && $driversStandings[0]['is_player']) {
                $wdcTitles[] = [
                    'season' => $season,
                    'driver_name' => $driversStandings[0]['driver_name'],
                    'points' => $driversStandings[0]['points'],
                    'wins' => $driversStandings[0]['wins'],
                ];
            }
        }

        // 3. Dual Podium Check (both cars in top 3 in same race)
        $hasDualPodium = false;
        $raceGroups = $allResults->groupBy(fn ($r) => $r->race_id.'-'.$r->season);
        foreach ($raceGroups as $group) {
            if ($group->whereIn('position', [1, 2, 3])->count() >= 2) {
                $hasDualPodium = true;
                break;
            }
        }

        // 4. Milestone Badges Catalog
        $milestones = [
            [
                'id' => 'first_win',
                'name' => 'First Blood',
                'description' => 'Win your inaugural Grand Prix victory in Formula racing.',
                'icon' => '🏁',
                'unlocked' => $totalWins >= 1,
                'progress' => min(1, $totalWins).' / 1',
            ],
            [
                'id' => 'podium_master',
                'name' => 'Podium Regular',
                'description' => 'Finish on the podium (P1-P3) in 5 separate Grand Prix events.',
                'icon' => '🥉',
                'unlocked' => $totalPodiums >= 5,
                'progress' => min(5, $totalPodiums).' / 5',
            ],
            [
                'id' => 'grand_slam',
                'name' => 'Grand Prix Maestro',
                'description' => 'Accumulate 10 Grand Prix victories across all championship seasons.',
                'icon' => '🏆',
                'unlocked' => $totalWins >= 10,
                'progress' => min(10, $totalWins).' / 10',
            ],
            [
                'id' => 'dual_podium',
                'name' => 'Dual-Car Lockout',
                'description' => 'Achieve a double-podium finish (both Car 1 and Car 2 in top 3) in a single Grand Prix.',
                'icon' => '⚡',
                'unlocked' => $hasDualPodium,
                'progress' => $hasDualPodium ? '1 / 1' : '0 / 1',
            ],
            [
                'id' => 'hundred_races',
                'name' => 'Centurion Constructor',
                'description' => 'Enter 20 total Grand Prix races across your management career.',
                'icon' => '🏎️',
                'unlocked' => $totalRaces >= 20,
                'progress' => min(20, $totalRaces).' / 20',
            ],
            [
                'id' => 'millionaire',
                'name' => 'High-Roller Paddock',
                'description' => 'Accumulate over 250,000 Credits in total career income.',
                'icon' => '💰',
                'unlocked' => $totalCareerEarnings >= 250000,
                'progress' => number_format(min(250000, $totalCareerEarnings)).' / 250,000 CR',
            ],
            [
                'id' => 'reputation_royalty',
                'name' => 'Motorsport Elite',
                'description' => 'Achieve 500+ Team Reputation points in international motorsport.',
                'icon' => '⭐',
                'unlocked' => $team->reputation >= 500,
                'progress' => min(500, $team->reputation).' / 500 REP',
            ],
            [
                'id' => 'wcc_champion',
                'name' => 'World Constructors Champion',
                'description' => 'Win the FIA World Constructors Championship title.',
                'icon' => '👑',
                'unlocked' => count($wccTitles) >= 1,
                'progress' => count($wccTitles).' Title(s)',
            ],
        ];

        $stats = [
            'total_races' => $totalRaces,
            'total_wins' => $totalWins,
            'total_podiums' => $totalPodiums,
            'total_prize_money' => $totalPrizeMoney,
            'total_career_earnings' => $totalCareerEarnings,
            'total_reputation' => $totalReputationEarned,
            'total_points' => $totalChampionshipPoints,
            'win_rate' => $winRate,
            'podium_rate' => $podiumRate,
            'current_reputation' => $team->reputation,
            'unlocked_milestones' => collect($milestones)->where('unlocked', true)->count(),
            'total_milestones' => count($milestones),
        ];

        return view('hall-of-fame.index', [
            'team' => $team,
            'stats' => $stats,
            'gpVictories' => $gpVictories,
            'wccTitles' => $wccTitles,
            'wdcTitles' => $wdcTitles,
            'milestones' => $milestones,
        ]);
    }
}
