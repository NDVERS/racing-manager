@extends('layouts.app')

@section('title', 'Pre-Race Briefing - ' . $race->name)

@section('content')
<div class="space-y-6">
    <!-- Header / Breadcrumb Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-600 via-orange-500 to-amber-400"></div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1 text-xs font-mono text-zinc-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-red-400 transition-colors">PADDOCK</a>
                    <span>/</span>
                    <a href="{{ route('races.index') }}" class="hover:text-red-400 transition-colors uppercase">CALENDAR</a>
                    <span>/</span>
                    <span class="text-red-400 font-bold uppercase">{{ $race->name }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight">
                        {{ $race->name }}
                    </h1>
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded bg-zinc-800 text-zinc-300 border border-zinc-700">
                        {{ $race->location }}
                    </span>
                    <span class="text-xs font-mono font-black uppercase tracking-wider bg-zinc-950 border border-zinc-800 text-zinc-400 px-2.5 py-1 rounded">
                        {{ $race->laps }} LAPS
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('races.index') }}" class="px-4 py-2 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 text-xs font-mono font-bold uppercase transition flex items-center gap-1.5">
                    <span>&larr; Back to Calendar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Grid: Circuit Telemetry & Lineup Confirmation -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Circuit Profile & Assigned Lineup -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Track Environment Profile -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg">
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded bg-red-500"></span>
                        <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Circuit Profile & Environmental Telemetry</h2>
                    </div>
                    <span class="text-xs font-mono text-zinc-400">Round Specification</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center text-xs font-mono mb-4">
                    <div class="bg-zinc-950/80 p-3 rounded border border-zinc-800">
                        <div class="text-[9px] text-zinc-500 uppercase">Race Distance</div>
                        <div class="text-base font-bold text-white mt-0.5">{{ $race->laps }} Laps</div>
                    </div>

                    <div class="bg-zinc-950/80 p-3 rounded border border-zinc-800">
                        <div class="text-[9px] text-zinc-500 uppercase">Track Character</div>
                        <div class="text-base font-bold uppercase mt-0.5 {{ $race->track_type === 'technical' ? 'text-amber-400' : ($race->track_type === 'high_speed' ? 'text-blue-400' : 'text-emerald-400') }}">
                            {{ str_replace('_', ' ', $race->track_type) }}
                        </div>
                    </div>

                    <div class="bg-zinc-950/80 p-3 rounded border border-zinc-800">
                        <div class="text-[9px] text-zinc-500 uppercase">Weather Forecast</div>
                        <div class="text-base font-bold uppercase text-white mt-0.5">
                            {{ $race->weather === 'wet' ? '🌧️ Wet Rain' : '☀️ Dry Track' }}
                        </div>
                    </div>

                    <div class="bg-zinc-950/80 p-3 rounded border border-zinc-800">
                        <div class="text-[9px] text-zinc-500 uppercase">Championship Prize</div>
                        <div class="text-base font-bold text-amber-400 mt-0.5">{{ number_format($race->prize_pool) }} CR</div>
                    </div>
                </div>

                <div class="bg-zinc-950/60 border border-zinc-800/80 rounded p-4 text-xs font-mono text-zinc-400 leading-relaxed">
                    @if($race->track_type === 'high_speed')
                        <span class="text-blue-400 font-bold">CIRCUIT BRIEF:</span> High-speed sweeping straights require strong Top Speed and engine power. Aerodynamic drag setup will be critical for lap times.
                    @elseif($race->track_type === 'technical')
                        <span class="text-amber-400 font-bold">CIRCUIT BRIEF:</span> Complex corner sequences and chicanes heavily reward agility, braking stability, and driver cornering precision.
                    @else
                        <span class="text-emerald-400 font-bold">CIRCUIT BRIEF:</span> Balanced layout combining high-speed sectors and tight technical complexes. Balanced car setup and consistent pacing are vital.
                    @endif
                </div>
            </div>

            <!-- Assigned Lineup Review Deck -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg">
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded bg-orange-500"></span>
                        <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Assigned Constructor Lineup</h2>
                    </div>
                    <span class="text-xs font-mono text-zinc-400">Pre-Grid Scrutineering</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Active Chassis Box -->
                    <div class="bg-zinc-950/80 border border-zinc-800 rounded p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-mono text-orange-400 uppercase font-bold">Designated Chassis</span>
                            <a href="{{ route('garage.index') }}" class="text-[10px] font-mono text-zinc-500 hover:text-orange-400 underline">Change &rarr;</a>
                        </div>

                        @if($activeCar)
                            <div class="flex items-baseline justify-between mb-3">
                                <h3 class="text-base font-black text-white font-mono uppercase">{{ $activeCar->name }}</h3>
                                <span class="text-[10px] font-mono font-bold bg-zinc-800 text-zinc-300 px-2 py-0.5 rounded">LVL {{ $activeCar->level }}</span>
                            </div>

                            <div class="grid grid-cols-5 gap-1 text-center text-[10px] font-mono bg-zinc-900/80 p-2 rounded border border-zinc-800/80">
                                <div><span class="text-zinc-500 block">SPD</span><span class="font-bold text-white">{{ $activeCar->speed }}</span></div>
                                <div><span class="text-zinc-500 block">ACC</span><span class="font-bold text-white">{{ $activeCar->acceleration }}</span></div>
                                <div><span class="text-zinc-500 block">HND</span><span class="font-bold text-white">{{ $activeCar->handling }}</span></div>
                                <div><span class="text-zinc-500 block">BRK</span><span class="font-bold text-white">{{ $activeCar->braking }}</span></div>
                                <div><span class="text-zinc-500 block">REL</span><span class="font-bold text-emerald-400">{{ $activeCar->reliability }}%</span></div>
                            </div>
                        @else
                            <div class="py-4 text-center text-xs font-mono text-red-400">
                                No active car designated. <a href="{{ route('garage.index') }}" class="underline font-bold">Select in Garage</a>
                            </div>
                        @endif
                    </div>

                    <!-- Lead Driver Box -->
                    <div class="bg-zinc-950/80 border border-zinc-800 rounded p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-mono text-cyan-400 uppercase font-bold">Designated Driver</span>
                            <a href="{{ route('drivers.index') }}" class="text-[10px] font-mono text-zinc-500 hover:text-cyan-400 underline">Change &rarr;</a>
                        </div>

                        @if($primaryDriver)
                            <div class="flex items-baseline justify-between mb-3">
                                <h3 class="text-base font-black text-white font-mono uppercase">{{ $primaryDriver->name }}</h3>
                                <span class="text-[10px] font-mono font-black bg-zinc-800 text-cyan-300 px-2 py-0.5 rounded">{{ $primaryDriver->overallRating() }} OVR</span>
                            </div>

                            <div class="grid grid-cols-4 gap-1 text-center text-[10px] font-mono bg-zinc-900/80 p-2 rounded border border-zinc-800/80">
                                <div><span class="text-zinc-500 block">PACE</span><span class="font-bold text-white">{{ $primaryDriver->pace }}</span></div>
                                <div><span class="text-zinc-500 block">CORN</span><span class="font-bold text-white">{{ $primaryDriver->cornering }}</span></div>
                                <div><span class="text-zinc-500 block">CONS</span><span class="font-bold text-white">{{ $primaryDriver->consistency }}</span></div>
                                <div><span class="text-zinc-500 block">EXP</span><span class="font-bold text-white">{{ $primaryDriver->experience }}</span></div>
                            </div>
                        @else
                            <div class="py-4 text-center text-xs font-mono text-red-400">
                                No lead driver assigned. <a href="{{ route('drivers.index') }}" class="underline font-bold">Assign in Driver Lineup</a>
                            </div>
                        @endif
                    </div>
                </div>
            <!-- Pit-Wall Tactical Strategy Selector Deck -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg">
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded bg-cyan-500 animate-pulse"></span>
                        <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Pit-Wall Strategy Selector</h2>
                    </div>
                    <span class="text-xs font-mono text-zinc-400">Pre-Race Tactical Calibration</span>
                </div>

                <!-- Strategy Form (wraps inputs and triggers execution) -->
                <form id="race-sim-form" method="POST" action="{{ route('races.run', $race) }}" class="space-y-6">
                    @csrf

                    <!-- 1. Tire Compound Selection -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs font-mono font-bold uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                <span>1. Tire Compound Allocation</span>
                            </label>
                            @if($race->weather === 'wet')
                                <span class="text-[10px] font-mono font-black uppercase text-cyan-400 bg-cyan-950/80 border border-cyan-700/60 px-2 py-0.5 rounded animate-pulse">
                                    🌧️ Wet Weather: Wet compound strongly recommended
                                </span>
                            @else
                                <span class="text-[10px] font-mono text-zinc-400 bg-zinc-950 px-2 py-0.5 rounded border border-zinc-800">
                                    ☀️ Dry Surface: Slicks optimal
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <!-- Soft Compound -->
                            <label class="relative flex flex-col justify-between p-3.5 rounded bg-zinc-950/90 border-2 border-zinc-800 hover:border-red-500/80 cursor-pointer transition-all has-[:checked]:border-red-500 has-[:checked]:bg-red-950/20 group">
                                <input type="radio" name="tire_compound" value="soft" class="sr-only" {{ ($race->weather !== 'wet') ? '' : '' }}>
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-3 h-3 rounded-full bg-red-500 inline-block shrink-0 shadow-sm shadow-red-500/50"></span>
                                            <span class="font-mono font-black text-sm uppercase text-white group-hover:text-red-400">SOFT (C3)</span>
                                        </div>
                                        <span class="text-[9px] font-mono font-bold text-red-400 bg-red-950/80 px-1.5 py-0.5 rounded border border-red-800/60">-0.85s PACE</span>
                                    </div>
                                    <p class="text-[10px] font-mono text-zinc-400 leading-snug">
                                        Peak launch grip & early sector pace. Rapid tire wear cliff after 35% distance.
                                    </p>
                                </div>
                                <div class="mt-2.5 pt-2 border-t border-zinc-800/80 flex items-center justify-between text-[9px] font-mono text-zinc-500">
                                    <span>Grip: <strong class="text-red-400">Maximum</strong></span>
                                    <span>Deg: <strong class="text-orange-400">High</strong></span>
                                </div>
                            </label>

                            <!-- Medium Compound -->
                            <label class="relative flex flex-col justify-between p-3.5 rounded bg-zinc-950/90 border-2 border-zinc-800 hover:border-amber-500/80 cursor-pointer transition-all has-[:checked]:border-amber-500 has-[:checked]:bg-amber-950/20 group">
                                <input type="radio" name="tire_compound" value="medium" class="sr-only" {{ ($race->weather !== 'wet') ? 'checked' : '' }}>
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-3 h-3 rounded-full bg-amber-400 inline-block shrink-0 shadow-sm shadow-amber-400/50"></span>
                                            <span class="font-mono font-black text-sm uppercase text-white group-hover:text-amber-400">MEDIUM (C2)</span>
                                        </div>
                                        <span class="text-[9px] font-mono font-bold text-amber-400 bg-amber-950/80 px-1.5 py-0.5 rounded border border-amber-800/60">BALANCED</span>
                                    </div>
                                    <p class="text-[10px] font-mono text-zinc-400 leading-snug">
                                        Optimal baseline balance between raw speed and controlled degradation.
                                    </p>
                                </div>
                                <div class="mt-2.5 pt-2 border-t border-zinc-800/80 flex items-center justify-between text-[9px] font-mono text-zinc-500">
                                    <span>Grip: <strong class="text-amber-400">Optimal</strong></span>
                                    <span>Deg: <strong class="text-zinc-400">Moderate</strong></span>
                                </div>
                            </label>

                            <!-- Hard Compound -->
                            <label class="relative flex flex-col justify-between p-3.5 rounded bg-zinc-950/90 border-2 border-zinc-800 hover:border-zinc-400/80 cursor-pointer transition-all has-[:checked]:border-zinc-300 has-[:checked]:bg-zinc-800/30 group">
                                <input type="radio" name="tire_compound" value="hard" class="sr-only">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-3 h-3 rounded-full bg-zinc-200 inline-block shrink-0 shadow-sm shadow-zinc-200/50"></span>
                                            <span class="font-mono font-black text-sm uppercase text-white group-hover:text-zinc-200">HARD (C1)</span>
                                        </div>
                                        <span class="text-[9px] font-mono font-bold text-zinc-300 bg-zinc-800 px-1.5 py-0.5 rounded border border-zinc-700">+0.50s PACE</span>
                                    </div>
                                    <p class="text-[10px] font-mono text-zinc-400 leading-snug">
                                        Endurance compound with near-zero degradation. Superior late-race consistency.
                                    </p>
                                </div>
                                <div class="mt-2.5 pt-2 border-t border-zinc-800/80 flex items-center justify-between text-[9px] font-mono text-zinc-500">
                                    <span>Grip: <strong class="text-zinc-400">Durable</strong></span>
                                    <span>Deg: <strong class="text-emerald-400">Minimal</strong></span>
                                </div>
                            </label>

                            <!-- Wet Compound -->
                            <label class="relative flex flex-col justify-between p-3.5 rounded bg-zinc-950/90 border-2 border-zinc-800 hover:border-blue-500/80 cursor-pointer transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-950/20 group">
                                <input type="radio" name="tire_compound" value="wet" class="sr-only" {{ ($race->weather === 'wet') ? 'checked' : '' }}>
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-3 h-3 rounded-full bg-blue-500 inline-block shrink-0 shadow-sm shadow-blue-500/50"></span>
                                            <span class="font-mono font-black text-sm uppercase text-white group-hover:text-blue-400">WET RAIN</span>
                                        </div>
                                        <span class="text-[9px] font-mono font-bold {{ $race->weather === 'wet' ? 'text-cyan-400 bg-cyan-950/80 border border-cyan-800/60' : 'text-red-400 bg-red-950/80 border border-red-800/60' }}">
                                            {{ $race->weather === 'wet' ? '🌧️ RAIN GRIP' : '+4.5s ON DRY' }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] font-mono text-zinc-400 leading-snug">
                                        Deep water evacuation tread. Mandatory in rain; severe overheating penalty on dry tarmac.
                                    </p>
                                </div>
                                <div class="mt-2.5 pt-2 border-t border-zinc-800/80 flex items-center justify-between text-[9px] font-mono text-zinc-500">
                                    <span>Water: <strong class="text-blue-400">100% Grip</strong></span>
                                    <span>Dry: <strong class="text-red-400">Degrade</strong></span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Driving / Engine Mode Selection -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs font-mono font-bold uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                <span>2. Engine Telemetry & Driving Mode</span>
                            </label>
                            <span class="text-[10px] font-mono text-zinc-400 bg-zinc-950 px-2 py-0.5 rounded border border-zinc-800">
                                Engine ECU Mapping
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <!-- Push (Aggressive) -->
                            <label class="relative flex flex-col justify-between p-3.5 rounded bg-zinc-950/90 border-2 border-zinc-800 hover:border-red-500/80 cursor-pointer transition-all has-[:checked]:border-red-500 has-[:checked]:bg-red-950/20 group">
                                <input type="radio" name="driving_mode" value="push" class="sr-only">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs">⚡</span>
                                            <span class="font-mono font-black text-sm uppercase text-white group-hover:text-red-400">PUSH (AGGRESSIVE)</span>
                                        </div>
                                        <span class="text-[9px] font-mono font-bold text-red-400 bg-red-950/80 px-1.5 py-0.5 rounded border border-red-800/60">-0.45s PACE</span>
                                    </div>
                                    <p class="text-[10px] font-mono text-zinc-400 leading-snug">
                                        Maximum power & aggressive overtake line. Accelerates tire wear (+35%) and elevates lock-up risk.
                                    </p>
                                </div>
                                <div class="mt-2.5 pt-2 border-t border-zinc-800/80 flex items-center justify-between text-[9px] font-mono text-zinc-500">
                                    <span>Attack: <strong class="text-red-400">+Overtake</strong></span>
                                    <span>Reliability: <strong class="text-orange-400">-12 Buffer</strong></span>
                                </div>
                            </label>

                            <!-- Balanced (Standard) -->
                            <label class="relative flex flex-col justify-between p-3.5 rounded bg-zinc-950/90 border-2 border-zinc-800 hover:border-zinc-400/80 cursor-pointer transition-all has-[:checked]:border-zinc-300 has-[:checked]:bg-zinc-800/30 group">
                                <input type="radio" name="driving_mode" value="balanced" class="sr-only" checked>
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs">⚙️</span>
                                            <span class="font-mono font-black text-sm uppercase text-white group-hover:text-zinc-200">BALANCED</span>
                                        </div>
                                        <span class="text-[9px] font-mono font-bold text-zinc-300 bg-zinc-800 px-1.5 py-0.5 rounded border border-zinc-700">OPTIMAL</span>
                                    </div>
                                    <p class="text-[10px] font-mono text-zinc-400 leading-snug">
                                        Standard engine mapping. Balanced fuel consumption, nominal reliability, and controlled tire wear.
                                    </p>
                                </div>
                                <div class="mt-2.5 pt-2 border-t border-zinc-800/80 flex items-center justify-between text-[9px] font-mono text-zinc-500">
                                    <span>Attack: <strong class="text-zinc-300">Standard</strong></span>
                                    <span>Reliability: <strong class="text-zinc-300">Nominal</strong></span>
                                </div>
                            </label>

                            <!-- Conserve (Defensive) -->
                            <label class="relative flex flex-col justify-between p-3.5 rounded bg-zinc-950/90 border-2 border-zinc-800 hover:border-emerald-500/80 cursor-pointer transition-all has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-950/20 group">
                                <input type="radio" name="driving_mode" value="conserve" class="sr-only">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs">🛡️</span>
                                            <span class="font-mono font-black text-sm uppercase text-white group-hover:text-emerald-400">CONSERVE (DEFENSE)</span>
                                        </div>
                                        <span class="text-[9px] font-mono font-bold text-emerald-400 bg-emerald-950/80 px-1.5 py-0.5 rounded border border-emerald-800/60">+15 RELIABILITY</span>
                                    </div>
                                    <p class="text-[10px] font-mono text-zinc-400 leading-snug">
                                        Defensive positioning and engine care. Reduces tire wear (-30%) and minimizes mechanical faults.
                                    </p>
                                </div>
                                <div class="mt-2.5 pt-2 border-t border-zinc-800/80 flex items-center justify-between text-[9px] font-mono text-zinc-500">
                                    <span>Defense: <strong class="text-emerald-400">+Defense</strong></span>
                                    <span>Wear: <strong class="text-emerald-400">-30% Rate</strong></span>
                                </div>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right 1 Col: Pre-Race Checklist & Grid Entry Confirmation -->
        <div class="space-y-6">
            <!-- Scrutineering Checklist & Entry Card -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg">
                <div class="text-xs font-mono font-bold uppercase tracking-wider text-zinc-400 mb-4 pb-2 border-b border-zinc-800">
                    Pre-Race Scrutineering Checklist
                </div>

                <div class="space-y-3 text-xs font-mono">
                    <!-- Active Car Check -->
                    <div class="flex items-center justify-between py-1.5 border-b border-zinc-800/60">
                        <span class="text-zinc-400">Primary Race Chassis</span>
                        @if($hasActiveCar)
                            <span class="text-emerald-400 font-bold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>VERIFIED</span>
                            </span>
                        @else
                            <span class="text-red-400 font-bold">UNASSIGNED</span>
                        @endif
                    </div>

                    <!-- Lead Driver Check -->
                    <div class="flex items-center justify-between py-1.5 border-b border-zinc-800/60">
                        <span class="text-zinc-400">Lead Driver Contract</span>
                        @if($hasPrimaryDriver)
                            <span class="text-emerald-400 font-bold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>VERIFIED</span>
                            </span>
                        @else
                            <span class="text-red-400 font-bold">UNASSIGNED</span>
                        @endif
                    </div>

                    <!-- Entry Fee Solvency Check -->
                    <div class="flex items-center justify-between py-1.5 border-b border-zinc-800/60">
                        <span class="text-zinc-400">Entry Fee ({{ number_format($race->entry_fee) }} CR)</span>
                        @if($hasEnoughFunds)
                            <span class="text-emerald-400 font-bold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>SOLVENT</span>
                            </span>
                        @else
                            <span class="text-red-400 font-bold">SHORT FUNDS</span>
                        @endif
                    </div>

                    <!-- Treasury Balance -->
                    <div class="flex items-center justify-between py-1.5">
                        <span class="text-zinc-500">Available Treasury</span>
                        <span class="text-amber-400 font-bold">{{ number_format($team->money) }} CR</span>
                    </div>
                </div>

                <!-- Registration & Simulation Action Buttons -->
                <div class="mt-6 pt-4 border-t border-zinc-800 space-y-2">
                    @if($isReady)
                        <button type="submit" form="race-sim-form" class="w-full text-center py-3 px-4 rounded bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-500 hover:to-orange-500 text-white text-xs font-mono font-black tracking-wider uppercase transition shadow-lg cursor-pointer flex items-center justify-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                            <span>Start Live Race Simulation &rarr;</span>
                        </button>
                        <form method="POST" action="{{ route('races.enter', $race) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full text-center py-2 px-3 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-700/80 text-zinc-300 text-[11px] font-mono font-bold uppercase transition cursor-pointer">
                                Confirm Lineup Only
                            </button>
                        </form>
                    @else
                        <button type="button" disabled class="w-full text-center py-3 px-4 rounded bg-zinc-950 border border-zinc-800 text-zinc-500 text-xs font-mono font-bold uppercase cursor-not-allowed">
                            Scrutineering Incomplete
                        </button>
                    @endif
                </div>
            </div>

            <!-- Past Circuit Result (if competed before) -->
            @if($pastResult)
                <div class="bg-zinc-900 border border-indigo-500/40 rounded p-5 shadow-lg">
                    <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-indigo-400 mb-2">
                        Past Constructor Result
                    </div>
                    <div class="flex justify-between items-baseline font-mono text-xs">
                        <span class="text-zinc-300">Previous Finish:</span>
                        <span class="text-indigo-300 font-black text-sm">P{{ $pastResult->position }}</span>
                    </div>
                    <div class="flex justify-between items-baseline font-mono text-xs mt-1">
                        <span class="text-zinc-500">Earned Prize:</span>
                        <span class="text-amber-400 font-bold">+{{ number_format($pastResult->prize_money) }} CR</span>
                    </div>
                </div>
            @endif

            <!-- Quick Navigation Box -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-5 shadow-lg">
                <div class="text-xs font-mono font-bold uppercase tracking-wider text-zinc-400 mb-3">
                    Pit-Wall Quick Nav
                </div>
                <div class="space-y-2 text-xs font-mono">
                    <a href="{{ route('races.index') }}" class="block p-2.5 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white transition-colors">
                        &rarr; Grand Prix Schedule Calendar
                    </a>
                    <a href="{{ route('garage.index') }}" class="block p-2.5 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-orange-400 hover:text-orange-300 transition-colors">
                        &rarr; Garage Fleet Management
                    </a>
                    <a href="{{ route('drivers.index') }}" class="block p-2.5 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-cyan-400 hover:text-cyan-300 transition-colors">
                        &rarr; Driver Lineup & Roster
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
