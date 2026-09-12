<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeamOnboardingController extends Controller
{
    /**
     * Show the team creation / onboarding form.
     */
    public function create(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->team()->exists()) {
            return redirect()->route('dashboard')
                ->with('info', 'Your team is already active.');
        }

        return view('team.create', [
            'starterCar' => [
                'name' => 'Kuro GT',
                'speed' => 65,
                'acceleration' => 62,
                'handling' => 64,
                'braking' => 63,
                'reliability' => 75,
            ],
            'starterDriver' => [
                'name' => 'Alex Carter',
                'pace' => 65,
                'cornering' => 64,
                'consistency' => 70,
                'experience' => 40,
                'salary' => 1200,
            ],
            'startingCredits' => 50000,
        ]);
    }

    /**
     * Store the newly created team and allocate starter assets.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->team()->exists()) {
            return redirect()->route('dashboard')
                ->with('warning', 'You already have an active team.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:50'],
        ]);

        $team = DB::transaction(function () use ($user, $validated) {
            $newTeam = Team::create([
                'user_id' => $user->id,
                'name' => trim($validated['name']),
                'money' => 50000,
                'reputation' => 0,
            ]);

            // Assign starter car "Kuro GT"
            $kuroGt = Car::where('name', 'Kuro GT')
                ->whereNull('team_id')
                ->first();

            if ($kuroGt) {
                $kuroGt->update([
                    'team_id' => $newTeam->id,
                    'is_active' => true,
                ]);
            } else {
                Car::create([
                    'team_id' => $newTeam->id,
                    'name' => 'Kuro GT',
                    'speed' => 65,
                    'acceleration' => 62,
                    'handling' => 64,
                    'braking' => 63,
                    'reliability' => 75,
                    'level' => 1,
                    'purchase_price' => 25000,
                    'is_active' => true,
                ]);
            }

            // Assign starter driver "Alex Carter"
            $alexCarter = Driver::where('name', 'Alex Carter')
                ->whereNull('team_id')
                ->first();

            if ($alexCarter) {
                $alexCarter->update([
                    'team_id' => $newTeam->id,
                    'is_lead' => true,
                ]);
            } else {
                Driver::create([
                    'team_id' => $newTeam->id,
                    'name' => 'Alex Carter',
                    'pace' => 65,
                    'cornering' => 64,
                    'consistency' => 70,
                    'overtaking' => 62,
                    'defensive' => 63,
                    'racecraft' => 66,
                    'experience' => 40,
                    'salary' => 1200,
                    'is_lead' => true,
                ]);
            }

            return $newTeam;
        });

        return redirect()->route('dashboard')
            ->with('success', "Welcome, Team Manager! {$team->name} has been founded with 50,000 Credits, your starter Kuro GT, and driver Alex Carter.");
    }
}
