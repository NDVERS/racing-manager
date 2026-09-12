<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Sponsor;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ChampionshipService;
use App\Services\RaceSimulationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RaceController extends Controller
{
    /**
     * Display the Grand Prix championship race calendar.
     */
    public function index(Request $request, ChampionshipService $championshipService): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $currentSeason = max(1, (int) $team->current_season);
        $races = Race::all();
        $teamResults = RaceResult::where('team_id', $team->id)
            ->where('season', $currentSeason)
            ->get()
            ->keyBy('race_id');

        $activeCar = $team->activeCar();
        $primaryDriver = $team->primaryDriver();
        $isRaceReady = ($activeCar !== null && $primaryDriver !== null);
        $isSeasonCompleted = $team->isCurrentSeasonCompleted();
        $seasonOverview = $championshipService->getSeasonOverview($team, $currentSeason);

        return view('races.index', [
            'team' => $team,
            'races' => $races,
            'teamResults' => $teamResults,
            'activeCar' => $activeCar,
            'primaryDriver' => $primaryDriver,
            'isRaceReady' => $isRaceReady,
            'currentSeason' => $currentSeason,
            'isSeasonCompleted' => $isSeasonCompleted,
            'seasonOverview' => $seasonOverview,
        ]);
    }

    /**
     * Display the team's historical race archives and cumulative performance metrics.
     */
    public function history(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $allResults = RaceResult::where('team_id', $team->id)->get();
        $results = RaceResult::where('team_id', $team->id)
            ->with(['race', 'car', 'driver'])
            ->orderByDesc('created_at')
            ->paginate(10);

        $totalRaces = $allResults->count();
        $totalWins = $allResults->where('position', 1)->count();
        $totalPodiums = $allResults->whereIn('position', [1, 2, 3])->count();
        $totalEarnings = (int) $allResults->sum('prize_money');
        $totalReputation = (int) $allResults->sum('reputation_earned');
        $bestFinish = $allResults->min('position');

        $driverPointsMap = [
            1 => 25, 2 => 18, 3 => 15, 4 => 12, 5 => 10,
            6 => 8, 7 => 6, 8 => 4, 9 => 2, 10 => 1,
        ];
        $totalChampionshipPoints = 0;
        foreach ($allResults as $res) {
            $totalChampionshipPoints += $driverPointsMap[$res->position] ?? 0;
        }

        $stats = [
            'total_races' => $totalRaces,
            'total_wins' => $totalWins,
            'total_podiums' => $totalPodiums,
            'total_earnings' => $totalEarnings,
            'total_reputation' => $totalReputation,
            'total_points' => $totalChampionshipPoints,
            'championship_points' => $totalChampionshipPoints,
            'best_finish' => $bestFinish,
            'win_rate' => $totalRaces > 0 ? round(($totalWins / $totalRaces) * 100, 1) : 0,
            'podium_rate' => $totalRaces > 0 ? round(($totalPodiums / $totalRaces) * 100, 1) : 0,
        ];

        return view('races.history', [
            'team' => $team,
            'results' => $results,
            'stats' => $stats,
        ]);
    }

    /**
     * Display pre-race preparation and configuration hub.
     */
    public function show(Request $request, Race $race): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $car1 = $team->car1();
        $car2 = $team->car2();
        $driver1 = $team->driver1();
        $driver2 = $team->driver2();

        $isTwoCarReady = $team->isTwoCarReady();
        $isRaceReady = ($car1 !== null && $driver1 !== null);
        $hasEnoughFunds = ($team->money >= $race->entry_fee);

        $currentSeason = max(1, (int) $team->current_season);

        $latestResults = RaceResult::where('race_id', $race->id)
            ->where('team_id', $team->id)
            ->where('season', $currentSeason)
            ->with(['car', 'driver'])
            ->latest()
            ->get();

        $latestResult = $latestResults->first();

        $pastResult = RaceResult::where('race_id', $race->id)
            ->where('team_id', $team->id)
            ->with(['car', 'driver'])
            ->latest()
            ->first();

        $hasActiveCar = ($car1 !== null);
        $hasPrimaryDriver = ($driver1 !== null);
        $isReady = $hasActiveCar && $hasPrimaryDriver && $hasEnoughFunds;

        return view('races.show', [
            'team' => $team,
            'race' => $race,
            'activeCar' => $car1,
            'primaryDriver' => $driver1,
            'car1' => $car1,
            'car2' => $car2,
            'driver1' => $driver1,
            'driver2' => $driver2,
            'isTwoCarReady' => $isTwoCarReady,
            'hasActiveCar' => $hasActiveCar,
            'hasPrimaryDriver' => $hasPrimaryDriver,
            'isRaceReady' => $isRaceReady,
            'hasEnoughFunds' => $hasEnoughFunds,
            'isReady' => $isReady,
            'latestResult' => $latestResult,
            'latestResults' => $latestResults,
            'pastResult' => $pastResult,
            'currentSeason' => $currentSeason,
        ]);
    }

    /**
     * Confirm lineup and pre-race readiness.
     */
    public function enter(Request $request, Race $race): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $car1 = $team->car1();
        if (! $car1) {
            return redirect()->route('garage.index')
                ->with('warning', 'Please configure and assign an active primary race car in your garage first.');
        }

        $driver1 = $team->driver1();
        if (! $driver1) {
            return redirect()->route('drivers.index')
                ->with('warning', 'Please designate a lead driver in your team lineup first.');
        }

        if ($team->money < $race->entry_fee) {
            return redirect()->route('races.show', $race)
                ->with('warning', 'Insufficient credits in team treasury to pay entry fee of '.number_format($race->entry_fee).' CR.');
        }

        $car2 = $team->car2();
        $driver2 = $team->driver2();
        $entryDetails = ($car2 && $driver2)
            ? "Car #1: {$car1->name} ({$driver1->name}) | Car #2: {$car2->name} ({$driver2->name})"
            : "Car #1: {$car1->name} ({$driver1->name})";

        return redirect()->route('races.show', $race)
            ->with('success', "Race entry confirmed for {$race->name}! Lineup: {$entryDetails}. Ready for Grand Prix simulation.");
    }

    /**
     * Execute Grand Prix race simulation with atomic economic settlement.
     */
    public function run(Request $request, Race $race, RaceSimulationService $simulationService): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $car1 = $team->car1();
        if (! $car1) {
            return redirect()->route('garage.index')
                ->with('warning', 'You must designate an active primary race car before running a race.');
        }

        $driver1 = $team->driver1();
        if (! $driver1) {
            return redirect()->route('drivers.index')
                ->with('warning', 'You must assign a lead race driver before running a race.');
        }

        if ($team->money < $race->entry_fee) {
            return redirect()->route('races.show', $race)
                ->with('warning', 'Insufficient credits in team treasury to pay entry fee of '.number_format($race->entry_fee).' CR.');
        }

        $car2 = $team->car2();
        $driver2 = $team->driver2();
        $isTwoCar = ($car2 !== null && $driver2 !== null);

        $validCompounds = ['soft', 'medium', 'hard', 'wet'];
        $validModes = ['push', 'balanced', 'conserve'];

        $rawCompound1 = strtolower((string) $request->input('tire_compound'));
        $rawMode1 = strtolower((string) $request->input('driving_mode'));
        $tireCompound1 = in_array($rawCompound1, $validCompounds, true) ? $rawCompound1 : 'medium';
        $drivingMode1 = in_array($rawMode1, $validModes, true) ? $rawMode1 : 'balanced';

        $rawCompound2 = strtolower((string) $request->input('tire_compound_2'));
        $rawMode2 = strtolower((string) $request->input('driving_mode_2'));
        $tireCompound2 = in_array($rawCompound2, $validCompounds, true) ? $rawCompound2 : 'medium';
        $drivingMode2 = in_array($rawMode2, $validModes, true) ? $rawMode2 : 'balanced';

        $simulationData = DB::transaction(function () use (
            $race,
            $team,
            $car1,
            $driver1,
            $car2,
            $driver2,
            $isTwoCar,
            $simulationService,
            $tireCompound1,
            $drivingMode1,
            $tireCompound2,
            $drivingMode2
        ) {
            // 1. Deduct entry fee
            $team->decrement('money', $race->entry_fee);
            $team->refresh();

            Transaction::create([
                'team_id' => $team->id,
                'type' => 'expense',
                'amount' => -$race->entry_fee,
                'balance_after' => $team->money,
                'description' => "Grand Prix Entry Fee: {$race->name}",
                'reference_id' => $race->id,
                'reference_type' => Race::class,
            ]);

            // 2. Run simulation engine with selected tactics for 1 or 2 cars
            $sim = $simulationService->simulate(
                $race,
                $team,
                $car1,
                $driver1,
                $tireCompound1,
                $drivingMode1,
                $isTwoCar ? $car2 : null,
                $isTwoCar ? $driver2 : null,
                $tireCompound2,
                $drivingMode2
            );

            $currentSeason = max(1, (int) $team->current_season);

            // 3. Calculate prize money and reputation distribution
            $pos1 = $sim['player_result_1']['position'] ?? ($sim['player_result']['position'] ?? 10);
            $hasFastestLap1 = isset($sim['fastest_lap_overall']) && ($sim['fastest_lap_overall']['driver'] === $driver1->name);
            $financials1 = $simulationService->calculatePrizeAndReputation($race, $pos1, $hasFastestLap1);

            $financials2 = ['prize_money' => 0, 'reputation_earned' => 0];
            $pos2 = null;
            $hasFastestLap2 = false;

            if ($isTwoCar && isset($sim['player_result_2'])) {
                $pos2 = $sim['player_result_2']['position'];
                $hasFastestLap2 = isset($sim['fastest_lap_overall']) && ($sim['fastest_lap_overall']['driver'] === $driver2->name);
                $financials2 = $simulationService->calculatePrizeAndReputation($race, $pos2, $hasFastestLap2);
            }

            $totalPrizeMoney = $financials1['prize_money'] + $financials2['prize_money'];
            $totalRepEarned = $financials1['reputation_earned'] + $financials2['reputation_earned'];

            // 4. Credit prize money if earned
            if ($totalPrizeMoney > 0) {
                $team->increment('money', $totalPrizeMoney);
                $team->refresh();

                $prizeDesc = $isTwoCar
                    ? "Prize Money Car #1 (P{$pos1}) + Car #2 (P{$pos2}): {$race->name}"
                    : "Prize Money P{$pos1}: {$race->name}";

                Transaction::create([
                    'team_id' => $team->id,
                    'type' => 'income',
                    'amount' => $totalPrizeMoney,
                    'balance_after' => $team->money,
                    'description' => $prizeDesc,
                    'reference_id' => $race->id,
                    'reference_type' => Race::class,
                ]);
            }

            // 5. Award reputation points
            if ($totalRepEarned > 0) {
                $team->increment('reputation', $totalRepEarned);
                $team->refresh();
            }

            // 6. Sponsor Contracts Evaluation & Payout
            $activeContracts = $team->teamSponsors()->where('is_active', true)->with('sponsor')->get();
            $sponsorSettlements = [];
            $totalSponsorBonus = 0;

            $bestPlayerPos = $isTwoCar ? min($pos1, $pos2) : $pos1;
            $hasAnyFastestLap = ($hasFastestLap1 || $hasFastestLap2);

            foreach ($activeContracts as $contract) {
                $sponsor = $contract->sponsor;
                $achieved = false;

                switch ($sponsor->target_objective) {
                    case 'finish_top_3':
                        $achieved = ($bestPlayerPos <= 3);
                        break;
                    case 'finish_top_5':
                        $achieved = ($bestPlayerPos <= 5);
                        break;
                    case 'score_fastest_lap':
                        $achieved = $hasAnyFastestLap;
                        break;
                    case 'finish_race':
                    default:
                        $achieved = true;
                        break;
                }

                $bonusEarned = 0;
                if ($achieved && $sponsor->bonus_per_race > 0) {
                    $bonusEarned = $sponsor->bonus_per_race;
                    $totalSponsorBonus += $bonusEarned;
                    $team->increment('money', $bonusEarned);
                    $team->refresh();

                    Transaction::create([
                        'team_id' => $team->id,
                        'type' => 'income',
                        'amount' => $bonusEarned,
                        'balance_after' => $team->money,
                        'description' => "Sponsor Objective Bonus ({$sponsor->name}): {$sponsor->objectiveLabel()}",
                        'reference_id' => $sponsor->id,
                        'reference_type' => Sponsor::class,
                    ]);
                }

                // Decrement races remaining
                $contract->decrement('races_remaining', 1);
                $contract->refresh();

                if ($contract->races_remaining <= 0) {
                    $contract->update(['is_active' => false]);
                }

                $sponsorSettlements[] = [
                    'sponsor_id' => $sponsor->id,
                    'sponsor_name' => $sponsor->name,
                    'tier' => $sponsor->tier,
                    'target_objective' => $sponsor->target_objective,
                    'objective_label' => $sponsor->objectiveLabel(),
                    'achieved' => $achieved,
                    'bonus_earned' => $bonusEarned,
                    'races_remaining' => $contract->races_remaining,
                    'expired' => ($contract->races_remaining <= 0),
                ];
            }

            // 7. Record official RaceResult records (Car 1 and optional Car 2)
            $raceResult1 = RaceResult::create([
                'race_id' => $race->id,
                'team_id' => $team->id,
                'car_id' => $car1->id,
                'driver_id' => $driver1->id,
                'car_slot' => 1,
                'season' => $currentSeason,
                'position' => $pos1,
                'race_time' => $sim['player_result_1']['total_time'] ?? ($sim['player_result']['total_time'] ?? '1:20.000'),
                'prize_money' => $financials1['prize_money'],
                'reputation_earned' => $financials1['reputation_earned'],
                'strategy' => "{$drivingMode1}_{$tireCompound1}",
                'status' => 'finished',
                'simulation_log' => $sim,
            ]);

            $raceResult2 = null;
            if ($isTwoCar && $car2 && $driver2 && isset($sim['player_result_2'])) {
                $raceResult2 = RaceResult::create([
                    'race_id' => $race->id,
                    'team_id' => $team->id,
                    'car_id' => $car2->id,
                    'driver_id' => $driver2->id,
                    'car_slot' => 2,
                    'season' => $currentSeason,
                    'position' => $pos2,
                    'race_time' => $sim['player_result_2']['total_time'],
                    'prize_money' => $financials2['prize_money'],
                    'reputation_earned' => $financials2['reputation_earned'],
                    'strategy' => "{$drivingMode2}_{$tireCompound2}",
                    'status' => 'finished',
                    'simulation_log' => $sim,
                ]);
            }

            $sim['result_id'] = $raceResult1->id;
            $sim['result_id_1'] = $raceResult1->id;
            $sim['result_id_2'] = $raceResult2 ? $raceResult2->id : null;
            $sim['financials'] = [
                'car1' => $financials1,
                'car2' => $financials2,
                'total_prize' => $totalPrizeMoney,
                'total_reputation' => $totalRepEarned,
            ];
            $sim['entry_fee'] = $race->entry_fee;
            $sim['sponsor_settlements'] = $sponsorSettlements;
            $sim['sponsor_bonus_total'] = $totalSponsorBonus;
            $sim['net_profit'] = ($totalPrizeMoney + $totalSponsorBonus) - $race->entry_fee;
            $sim['balance_after'] = $team->money;
            $sim['reputation_after'] = $team->reputation;

            // Update stored simulation log with complete sponsor settlements & tactics
            $raceResult1->update(['simulation_log' => $sim]);
            if ($raceResult2) {
                $raceResult2->update(['simulation_log' => $sim]);
            }

            return $sim;
        });

        // Store settlement payload in session for Live and Results views
        session(['race_simulation_'.$race->id => $simulationData]);

        return redirect()->route('races.live', $race);
    }

    /**
     * Display the Live Pit-Wall race telemetry simulation.
     */
    public function live(Request $request, Race $race): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $simulationData = session('race_simulation_'.$race->id);

        if (! $simulationData) {
            // Check if there is already a settled result
            $latestResult = RaceResult::where('race_id', $race->id)
                ->where('team_id', $team->id)
                ->latest()
                ->first();

            if ($latestResult && $latestResult->simulation_log) {
                $simulationData = $latestResult->simulation_log;
                $simulationData['result_id'] = $latestResult->id;
                session(['race_simulation_'.$race->id => $simulationData]);
            } else {
                return redirect()->route('races.show', $race)
                    ->with('warning', 'Please initiate race simulation from the preparation hub.');
            }
        }

        return view('races.live', [
            'team' => $team,
            'race' => $race,
            'simulation' => $simulationData,
        ]);
    }

    /**
     * Display the Official Post-Race Debrief and Financial Ledger.
     */
    public function results(Request $request, Race $race): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $currentSeason = max(1, (int) $team->current_season);

        $results = RaceResult::where('race_id', $race->id)
            ->where('team_id', $team->id)
            ->where('season', $currentSeason)
            ->with(['race', 'car', 'driver'])
            ->orderBy('car_slot')
            ->get();

        if ($results->isEmpty()) {
            $results = RaceResult::where('race_id', $race->id)
                ->where('team_id', $team->id)
                ->with(['race', 'car', 'driver'])
                ->orderBy('car_slot')
                ->get();
        }

        if ($results->isEmpty()) {
            return redirect()->route('races.show', $race)
                ->with('warning', 'No race results recorded for this Grand Prix yet.');
        }

        $result1 = $results->firstWhere('car_slot', 1) ?? $results->first();
        $result2 = $results->firstWhere('car_slot', 2);
        $result = $result1;

        $simulationLog = $result->simulation_log ?? session('race_simulation_'.$race->id);

        $totalPrize = $results->sum('prize_money');
        $totalBonus = $simulationLog['sponsor_bonus_total'] ?? 0;
        $netProfit = $simulationLog['net_profit'] ?? (($totalPrize + $totalBonus) - $race->entry_fee);

        return view('races.results', [
            'team' => $team,
            'race' => $race,
            'result' => $result,
            'result1' => $result1,
            'result2' => $result2,
            'results' => $results,
            'simulation' => $simulationLog,
            'netProfit' => $netProfit,
        ]);
    }

    /**
     * Display a specific RaceResult by its ID.
     */
    public function showResult(Request $request, RaceResult $raceResult): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        // Scoping / Authorization: Result must belong to the user's team
        if ($raceResult->team_id !== $team->id) {
            abort(404, 'Race result not found for your team.');
        }

        $raceResult->load(['race', 'car', 'driver']);
        $otherResult = RaceResult::where('race_id', $raceResult->race_id)
            ->where('team_id', $team->id)
            ->where('season', $raceResult->season)
            ->where('id', '!=', $raceResult->id)
            ->with(['race', 'car', 'driver'])
            ->first();

        $result1 = $raceResult->car_slot === 2 ? ($otherResult ?? $raceResult) : $raceResult;
        $result2 = $raceResult->car_slot === 2 ? $raceResult : $otherResult;
        $results = collect([$result1, $result2])->filter()->values();

        $netProfit = $raceResult->prize_money - $raceResult->race->entry_fee;

        return view('races.results', [
            'team' => $team,
            'race' => $raceResult->race,
            'result' => $raceResult,
            'result1' => $result1,
            'result2' => $result2,
            'results' => $results,
            'simulation' => $raceResult->simulation_log,
            'netProfit' => $netProfit,
        ]);
    }

    /**
     * Advance the team to the next championship season after completing all Grand Prix rounds.
     */
    public function advanceSeason(Request $request, ChampionshipService $championshipService): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $currentSeason = max(1, (int) $team->current_season);

        if (! $team->isCurrentSeasonCompleted()) {
            return redirect()->route('races.index')
                ->with('warning', "You must complete all scheduled Grand Prix races in Season {$currentSeason} before advancing to the next season.");
        }

        $settlement = DB::transaction(function () use ($team, $currentSeason, $championshipService) {
            $overview = $championshipService->getSeasonOverview($team, $currentSeason);
            $playerRank = $overview['player_constructor_rank'] ?? 10;

            // Determine Season Finale Prize based on Constructors Championship final rank
            if ($playerRank === 1) {
                $seasonPrizeMoney = 30000;
                $seasonPrizeReputation = 1200;
            } elseif ($playerRank <= 3) {
                $seasonPrizeMoney = 20000;
                $seasonPrizeReputation = 800;
            } elseif ($playerRank <= 6) {
                $seasonPrizeMoney = 12000;
                $seasonPrizeReputation = 500;
            } else {
                $seasonPrizeMoney = 8000;
                $seasonPrizeReputation = 300;
            }

            // Award financial & reputation payouts
            $team->increment('money', $seasonPrizeMoney);
            $team->increment('reputation', $seasonPrizeReputation);
            $team->increment('current_season', 1);
            $team->refresh();

            // Record transaction
            Transaction::create([
                'team_id' => $team->id,
                'type' => 'income',
                'amount' => $seasonPrizeMoney,
                'balance_after' => $team->money,
                'description' => "Season {$currentSeason} World Championship Finale Bonus (Constructor P{$playerRank})",
                'reference_id' => $currentSeason,
                'reference_type' => Team::class,
            ]);

            return [
                'old_season' => $currentSeason,
                'new_season' => $team->current_season,
                'player_rank' => $playerRank,
                'prize_money' => $seasonPrizeMoney,
                'prize_rep' => $seasonPrizeReputation,
            ];
        });

        return redirect()->route('races.index')
            ->with('success', "🏁 Season {$settlement['old_season']} concluded! Finished P{$settlement['player_rank']} in Constructors' Championship. Awarded +".number_format($settlement['prize_money'])." CR & +{$settlement['prize_rep']} REP. Welcome to Season {$settlement['new_season']}!");
    }
}
