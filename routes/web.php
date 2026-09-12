<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GarageController;
use App\Http\Controllers\TeamOnboardingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root / Home redirect
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return Auth::user()->team()->exists()
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
    });
});
