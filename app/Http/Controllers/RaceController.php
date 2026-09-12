<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
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
        $totalPoints = $allResults->sum(fn (RaceResult $r) => $r->points);
        $bestFinish = $allResults->min('position');

        $stats = [
            'total_races' => $totalRaces,
            'total_wins' => $totalWins,
            'total_podiums' => $totalPodiums,
            'total_earnings' => $totalEarnings,
            'total_reputation' => $totalReputation,
            'total_points' => $totalPoints,
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

    /**
     * Execute Grand Prix race simulation with atomic economic settlement.
     */
    public function run(Request $request, Race $race, RaceSimulationService $simulationService): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $activeCar = $team->activeCar();
        if (! $activeCar) {
            return redirect()->route('garage.index')
                ->with('warning', 'You must designate an active primary race car before running a race.');
        }

        $primaryDriver = $team->primaryDriver();
        if (! $primaryDriver) {
            return redirect()->route('drivers.index')
                ->with('warning', 'You must assign a lead race driver before running a race.');
        }

        if ($team->money < $race->entry_fee) {
            return redirect()->route('races.show', $race)
                ->with('warning', 'Insufficient credits in team treasury to pay entry fee of '.number_format($race->entry_fee).' CR.');
        }

        $simulationData = DB::transaction(function () use ($race, $team, $activeCar, $primaryDriver, $simulationService) {
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

            // 2. Run simulation engine
            $sim = $simulationService->simulate($race, $team, $activeCar, $primaryDriver);

            $playerPosition = $sim['player_result']['position'];
            $hasFastestLap = isset($sim['fastest_lap_overall']) && ($sim['fastest_lap_overall']['driver'] === $primaryDriver->name);

            // 3. Calculate prize money and reputation distribution
            $financials = $simulationService->calculatePrizeAndReputation($race, $playerPosition, $hasFastestLap);
            $prizeMoney = $financials['prize_money'];
            $reputationEarned = $financials['reputation_earned'];

            // 4. Credit prize money if earned
            if ($prizeMoney > 0) {
                $team->increment('money', $prizeMoney);
                $team->refresh();

                Transaction::create([
                    'team_id' => $team->id,
                    'type' => 'income',
                    'amount' => $prizeMoney,
                    'balance_after' => $team->money,
                    'description' => "Prize Money P{$playerPosition}: {$race->name}",
                    'reference_id' => $race->id,
                    'reference_type' => Race::class,
                ]);
            }

            // 5. Award reputation points
            if ($reputationEarned > 0) {
                $team->increment('reputation', $reputationEarned);
                $team->refresh();
            }

            // 6. Record official RaceResult
            $raceResult = RaceResult::create([
                'race_id' => $race->id,
                'team_id' => $team->id,
                'car_id' => $activeCar->id,
                'driver_id' => $primaryDriver->id,
                'position' => $playerPosition,
                'race_time' => $sim['player_result']['total_time'],
                'prize_money' => $prizeMoney,
                'reputation_earned' => $reputationEarned,
                'strategy' => 'balanced',
                'status' => 'finished',
                'simulation_log' => $sim,
            ]);

            $sim['result_id'] = $raceResult->id;
            $sim['financials'] = $financials;
            $sim['entry_fee'] = $race->entry_fee;
            $sim['net_profit'] = $prizeMoney - $race->entry_fee;
            $sim['balance_after'] = $team->money;
            $sim['reputation_after'] = $team->reputation;

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

        $result = RaceResult::where('race_id', $race->id)
            ->where('team_id', $team->id)
            ->with(['race', 'car', 'driver'])
            ->latest()
            ->first();

        if (! $result) {
            return redirect()->route('races.show', $race)
                ->with('warning', 'No race results recorded for this Grand Prix yet.');
        }

        $simulationLog = $result->simulation_log ?? session('race_simulation_'.$race->id);
        $netProfit = $result->prize_money - $race->entry_fee;

        return view('races.results', [
            'team' => $team,
            'race' => $race,
            'result' => $result,
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
        $netProfit = $raceResult->prize_money - $raceResult->race->entry_fee;

        return view('races.results', [
            'team' => $team,
            'race' => $raceResult->race,
            'result' => $raceResult,
            'simulation' => $raceResult->simulation_log,
            'netProfit' => $netProfit,
        ]);
    }
}
