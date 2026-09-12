<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\GarageController;
use App\Http\Controllers\RaceController;
use App\Http\Controllers\TeamOnboardingController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root / Home redirect
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    /** @var User $user */
    $user = Auth::user();

    return $user->team()->exists()
        ? redirect()->route('dashboard')
        : redirect()->route('team.create');
})->name('home');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Onboarding / Team Creation (only accessible if user does NOT have a team)
    Route::middleware('no.team')->group(function () {
        Route::get('/onboarding/team', [TeamOnboardingController::class, 'create'])->name('team.create');
        Route::post('/onboarding/team', [TeamOnboardingController::class, 'store'])->name('team.store');
    });

    // Game Protected Routes (accessible only if user HAS a team)
    Route::middleware('has.team')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Garage & Car Management
        Route::get('/garage', [GarageController::class, 'index'])->name('garage.index');
        Route::get('/garage/{car}', [GarageController::class, 'show'])->name('garage.show');
        Route::post('/garage/{car}/set-active', [GarageController::class, 'setActive'])->name('garage.set-active');

        // Driver System & Market
        Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
        Route::get('/drivers/market', [DriverController::class, 'market'])->name('drivers.market');
        Route::post('/drivers/market/{driver}/hire', [DriverController::class, 'hire'])->name('drivers.hire');
        Route::get('/drivers/{driver}', [DriverController::class, 'show'])->name('drivers.show');
        Route::post('/drivers/{driver}/set-lead', [DriverController::class, 'setLead'])->name('drivers.set-lead');

        // Grand Prix Race System & Simulation
        Route::get('/races', [RaceController::class, 'index'])->name('races.index');
        Route::get('/races/{race}', [RaceController::class, 'show'])->name('races.show');
        Route::post('/races/{race}/enter', [RaceController::class, 'enter'])->name('races.enter');
        Route::post('/races/{race}/run', [RaceController::class, 'run'])->name('races.run');
        Route::get('/races/{race}/live', [RaceController::class, 'live'])->name('races.live');
        Route::get('/races/{race}/results', [RaceController::class, 'results'])->name('races.results');
        Route::get('/race-results/{raceResult}', [RaceController::class, 'showResult'])->name('race-results.show');
    });
});
