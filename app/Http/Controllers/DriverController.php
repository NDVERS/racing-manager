<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DriverController extends Controller
{
    /**
     * Display the team's contracted driver roster.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $team = $user->team()->with('drivers')->firstOrFail();

        return view('drivers.index', [
            'team' => $team,
            'drivers' => $team->drivers,
            'leadDriver' => $team->primaryDriver(),
        ]);
    }

    /**
     * Display the Free Agent Driver Market.
     */
    public function market(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        $freeAgents = Driver::whereNull('team_id')
            ->orderBy('salary', 'asc')
            ->get();

        return view('drivers.market', [
            'team' => $team,
            'freeAgents' => $freeAgents,
        ]);
    }

    /**
     * Display the complete dossier/technical inspection of a driver.
     */
    public function show(Request $request, Driver $driver): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        // Scoping / Authorization: Driver must belong to the authenticated user's team
        if ($driver->team_id !== $team->id) {
            abort(404, 'Driver not found in your team roster.');
        }

        $driver->load('raceResults.race');

        return view('drivers.show', [
            'team' => $team,
            'driver' => $driver,
            'overallRating' => $driver->overallRating(),
            'raceResults' => $driver->raceResults,
        ]);
    }

    /**
     * Set a driver as the team's Lead/Primary Race Driver.
     */
    public function setLead(Request $request, Driver $driver): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        // Scoping / Authorization: Driver must belong to the authenticated user's team
        if ($driver->team_id !== $team->id) {
            abort(404, 'Driver not found in your team roster.');
        }

        DB::transaction(function () use ($team, $driver) {
            $team->drivers()->update(['is_lead' => false]);
            $driver->update(['is_lead' => true]);
        });

        return redirect()->back()
            ->with('success', "{$driver->name} is now designated as your Lead Race Driver.");
    }

    /**
     * Recruit / hire a free agent driver from the market.
     */
    public function hire(Request $request, Driver $driver): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Team $team */
        $team = $user->team;

        // Ensure driver is truly a free agent
        if ($driver->team_id !== null) {
            return redirect()->route('drivers.market')
                ->with('warning', "{$driver->name} is already under contract with a constructor.");
        }

        $hiringCost = $driver->hiringCost();

        // Check funds
        if ($team->money < $hiringCost) {
            return redirect()->route('drivers.market')
                ->with('warning', "Insufficient funds in team treasury to recruit {$driver->name}. Required: ".number_format($hiringCost).' CR.');
        }

        DB::transaction(function () use ($team, $driver, $hiringCost) {
            $team->decrement('money', $hiringCost);
            $team->refresh();

            $isFirstDriver = $team->drivers()->count() === 0;

            $driver->update([
                'team_id' => $team->id,
                'is_lead' => $isFirstDriver,
            ]);

            Transaction::create([
                'team_id' => $team->id,
                'type' => 'expense',
                'amount' => -$hiringCost,
                'balance_after' => $team->money,
                'description' => "Driver Contract Signing: {$driver->name}",
                'reference_id' => $driver->id,
                'reference_type' => Driver::class,
            ]);
        });

        return redirect()->route('drivers.index')
            ->with('success', "{$driver->name} has been signed to your driver lineup! Signing fee: ".number_format($hiringCost).' CR.');
    }
}
