<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ChampionshipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the team manager dashboard.
     */
    public function index(Request $request, ChampionshipService $championshipService): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team()->with(['cars.upgrades', 'drivers'])->firstOrFail();

        $activeCar = $team->activeCar();
        $primaryDriver = $team->primaryDriver();
        $isRaceReady = ($activeCar !== null && $primaryDriver !== null);

        // Onboarding Directives Tracking
        $hasActiveCar = ($activeCar !== null);
        $hasPrimaryDriver = ($primaryDriver !== null);
        $hasUpgrades = $team->cars()->whereHas('upgrades')->exists();
        $hasCompletedRace = $team->raceResults()->exists();

        $directivesCompletedCount = ($hasActiveCar ? 1 : 0)
            + ($hasPrimaryDriver ? 1 : 0)
            + ($hasUpgrades ? 1 : 0)
            + ($hasCompletedRace ? 1 : 0);

        $directivesTotal = 4;
        $isAllDirectivesComplete = ($directivesCompletedCount === $directivesTotal);

        $isBonusClaimed = $team->transactions()
            ->where('description', 'like', '%Onboarding Bonus%')
            ->exists();

        $directives = [
            'has_active_car' => $hasActiveCar,
            'has_primary_driver' => $hasPrimaryDriver,
            'has_upgrades' => $hasUpgrades,
            'has_completed_race' => $hasCompletedRace,
            'completed_count' => $directivesCompletedCount,
            'total' => $directivesTotal,
            'progress_percent' => (int) round(($directivesCompletedCount / $directivesTotal) * 100),
            'is_complete' => $isAllDirectivesComplete,
            'is_bonus_claimed' => $isBonusClaimed,
        ];

        $activeSponsors = $team->teamSponsors()->where('is_active', true)->with('sponsor')->get();
        $seasonOverview = $championshipService->getSeasonOverview($team);

        return view('dashboard.index', [
            'team' => $team,
            'activeCar' => $activeCar,
            'primaryDriver' => $primaryDriver,
            'isRaceReady' => $isRaceReady,
            'totalCars' => $team->cars->count(),
            'totalDrivers' => $team->drivers->count(),
            'activeSponsors' => $activeSponsors,
            'directives' => $directives,
            'season' => $seasonOverview,
        ]);
    }

    /**
     * Display the Team Principal Operations Handbook & Guide.
     */
    public function guide(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        return view('guide.index', [
            'team' => $team,
        ]);
    }

    /**
     * Claim the starter constructor onboarding grant bonus.
     */
    public function claimTutorialBonus(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        // Check directives completeness
        $hasActiveCar = ($team->activeCar() !== null);
        $hasPrimaryDriver = ($team->primaryDriver() !== null);
        $hasUpgrades = $team->cars()->whereHas('upgrades')->exists();
        $hasCompletedRace = $team->raceResults()->exists();

        if (! ($hasActiveCar && $hasPrimaryDriver && $hasUpgrades && $hasCompletedRace)) {
            return redirect()->back()
                ->with('error', 'All 4 Team Principal Directives must be completed before claiming the grant.');
        }

        $alreadyClaimed = $team->transactions()
            ->where('description', 'like', '%Onboarding Bonus%')
            ->exists();

        if ($alreadyClaimed) {
            return redirect()->back()
                ->with('warning', 'Onboarding Constructor Grant has already been claimed for your team.');
        }

        $grantCredits = 5000;
        $grantReputation = 25;

        DB::transaction(function () use ($team, $grantCredits, $grantReputation) {
            $team->increment('money', $grantCredits);
            $team->increment('reputation', $grantReputation);
            $team->refresh();

            Transaction::create([
                'team_id' => $team->id,
                'type' => 'income',
                'amount' => $grantCredits,
                'balance_after' => $team->money,
                'description' => 'Team Principal Onboarding Bonus Grant',
                'reference_id' => $team->id,
                'reference_type' => Team::class,
            ]);
        });

        return redirect()->route('dashboard')
            ->with('success', 'Constructor Grant claimed! +'.number_format($grantCredits)." CR and +{$grantReputation} Reputation added to your team treasury.");
    }
}
