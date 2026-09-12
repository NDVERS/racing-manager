<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use App\Models\Team;
use App\Models\TeamSponsor;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SponsorController extends Controller
{
    /**
     * Display the Sponsor Commercial Hub, active contracts, and open market deals.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $activeContracts = $team->teamSponsors()
            ->where('is_active', true)
            ->with('sponsor')
            ->get();

        $expiredContracts = $team->teamSponsors()
            ->where('is_active', false)
            ->with('sponsor')
            ->latest()
            ->take(6)
            ->get();

        $allSponsors = Sponsor::orderBy('min_reputation')->get();

        $activePrimaryCount = $activeContracts->filter(fn ($c) => strtolower($c->sponsor->tier) === 'primary')->count();
        $activeSecondaryCount = $activeContracts->filter(fn ($c) => in_array(strtolower($c->sponsor->tier), ['secondary', 'tertiary']))->count();

        $totalSponsorEarnings = (int) $team->transactions()
            ->where('type', 'income')
            ->where('description', 'like', '%Sponsor%')
            ->sum('amount');

        return view('sponsors.index', [
            'team' => $team,
            'activeContracts' => $activeContracts,
            'expiredContracts' => $expiredContracts,
            'allSponsors' => $allSponsors,
            'activePrimaryCount' => $activePrimaryCount,
            'activeSecondaryCount' => $activeSecondaryCount,
            'totalSponsorEarnings' => $totalSponsorEarnings,
        ]);
    }

    /**
     * Negotiate and sign a corporate motorsport sponsorship deal.
     */
    public function sign(Request $request, Sponsor $sponsor): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        if ($team->hasActiveSponsor($sponsor->id)) {
            return redirect()->route('sponsors.index')
                ->with('warning', "Your team already has an active partnership contract with {$sponsor->name}.");
        }

        if ($team->reputation < $sponsor->min_reputation) {
            return redirect()->route('sponsors.index')
                ->with('error', "Insufficient team reputation to sign {$sponsor->name}. Required: {$sponsor->min_reputation} REP, Current: {$team->reputation} REP.");
        }

        if (! $team->canSignSponsor($sponsor)) {
            $slotType = strtolower($sponsor->tier) === 'primary' ? 'Primary Title Partner (Max 1)' : 'Technical/Secondary Sponsor (Max 2)';

            return redirect()->route('sponsors.index')
                ->with('error', "No available commercial contract slots for {$slotType}. Wait for current contracts to conclude.");
        }

        DB::transaction(function () use ($team, $sponsor) {
            TeamSponsor::create([
                'team_id' => $team->id,
                'sponsor_id' => $sponsor->id,
                'races_remaining' => $sponsor->duration_races,
                'is_active' => true,
                'signed_at' => now(),
            ]);

            $team->increment('money', $sponsor->signing_bonus);
            $team->refresh();

            Transaction::create([
                'team_id' => $team->id,
                'type' => 'income',
                'amount' => $sponsor->signing_bonus,
                'balance_after' => $team->money,
                'description' => "Sponsor Signing Bonus: {$sponsor->name}",
                'reference_id' => $sponsor->id,
                'reference_type' => Sponsor::class,
            ]);
        });

        return redirect()->route('sponsors.index')
            ->with('success', "Contract signed with {$sponsor->name}! Immediate signing grant of +".number_format($sponsor->signing_bonus).' CR credited to team treasury.');
    }
}
