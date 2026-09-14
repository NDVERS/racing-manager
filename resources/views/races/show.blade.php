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
                        <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Assigned Constructor Lineup (20-Car Grid Entry)</h2>
                    </div>
                    @if($isTwoCarReady)
                        <span class="text-[10px] font-mono font-black uppercase text-emerald-400 bg-emerald-950/80 border border-emerald-500/50 px-2 py-0.5 rounded">
                            2-CAR CONSTRUCTOR ENTRY ACTIVE
                        </span>
                    @else
                        <span class="text-[10px] font-mono font-bold uppercase text-amber-400 bg-amber-950/80 border border-amber-500/50 px-2 py-0.5 rounded">
                            SINGLE CAR ENTRY (SLOT #2 AVAILABLE)
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Car 1 Entry Box -->
                    <div class="bg-zinc-950/80 border border-cyan-500/40 rounded p-4 relative overflow-hidden">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-mono text-cyan-400 uppercase font-black tracking-wider">ENTRY #1 (LEAD SEAT)</span>
                            <a href="{{ route('garage.index') }}" class="text-[10px] font-mono text-zinc-500 hover:text-cyan-400 underline">Garage &rarr;</a>
                        </div>

                        @if($car1 && $driver1)
                            <div class="space-y-2">
                                <div class="flex items-baseline justify-between">
                                    <h3 class="text-base font-black text-white font-mono uppercase">{{ $car1->name }}</h3>
                                    <span class="text-[10px] font-mono font-bold bg-zinc-800 text-zinc-300 px-2 py-0.5 rounded">LVL {{ $car1->level }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs font-mono text-cyan-300 bg-cyan-950/40 border border-cyan-800/40 rounded px-2.5 py-1.5">
                                    <span>Pilot: <strong>{{ $driver1->name }}</strong></span>
                                    <span class="font-black">{{ $driver1->overallRating() }} OVR</span>
                                </div>
                            </div>
                        @else
                            <div class="py-4 text-center text-xs font-mono text-red-400">
                                Car #1 or Driver #1 missing. <a href="{{ route('garage.index') }}" class="underline font-bold">Assign in Garage</a>
                            </div>
                        @endif
                    </div>

                    <!-- Car 2 Entry Box -->
                    <div class="bg-zinc-950/80 border {{ $isTwoCarReady ? 'border-blue-500/40' : 'border-zinc-800 border-dashed' }} rounded p-4 relative overflow-hidden">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-mono {{ $isTwoCarReady ? 'text-blue-400' : 'text-zinc-500' }} uppercase font-black tracking-wider">ENTRY #2 (SECONDARY SEAT)</span>
                            <a href="{{ route('drivers.index') }}" class="text-[10px] font-mono text-zinc-500 hover:text-blue-400 underline">Drivers &rarr;</a>
                        </div>

                        @if($car2 && $driver2)
                            <div class="space-y-2">
                                <div class="flex items-baseline justify-between">
                                    <h3 class="text-base font-black text-white font-mono uppercase">{{ $car2->name }}</h3>
                                    <span class="text-[10px] font-mono font-bold bg-zinc-800 text-zinc-300 px-2 py-0.5 rounded">LVL {{ $car2->level }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs font-mono text-blue-300 bg-blue-950/40 border border-blue-800/40 rounded px-2.5 py-1.5">
                                    <span>Pilot: <strong>{{ $driver2->name }}</strong></span>
                                    <span class="font-black">{{ $driver2->overallRating() }} OVR</span>
                                </div>
                            </div>
                        @else
                            <div class="py-4 text-center text-xs font-mono text-zinc-500">
                                Slot #2 Open. <a href="{{ route('drivers.market') }}" class="text-blue-400 underline font-bold">Hire Driver</a> or <a href="{{ route('garage.dealership') }}" class="text-orange-400 underline font-bold">Buy 2nd Chassis</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Pit-Wall Tactical Strategy Selector Deck -->
            <div x-data="{
                c1Compound: '{{ ($race->weather === 'wet') ? 'wet' : 'medium' }}',
                c1Mode: 'balanced',
                c2Compound: '{{ ($race->weather === 'wet') ? 'wet' : 'medium' }}',
                c2Mode: 'balanced',
                activePreset: null,

                applyPreset(name) {
                    this.activePreset = name;
                    const isWet = {{ $race->weather === 'wet' ? 'true' : 'false' }};
                    if (name === 'aggressive_split') {
                        this.c1Compound = 'soft';
                        this.c1Mode = 'push';
                        this.c2Compound = 'medium';
                        this.c2Mode = 'balanced';
                    } else if (name === 'safe_conserve') {
                        this.c1Compound = isWet ? 'wet' : 'hard';
                        this.c1Mode = 'conserve';
                        this.c2Compound = isWet ? 'wet' : 'hard';
                        this.c2Mode = 'conserve';
                    } else if (name === 'balanced_standard') {
                        this.c1Compound = isWet ? 'wet' : 'medium';
                        this.c1Mode = 'balanced';
                        this.c2Compound = isWet ? 'wet' : 'medium';
                        this.c2Mode = 'balanced';
                    } else if (name === 'wet_weather') {
                        this.c1Compound = 'wet';
                        this.c1Mode = 'balanced';
                        this.c2Compound = 'wet';
                        this.c2Mode = 'balanced';
                    }
                }
            }" class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg">
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded bg-cyan-500 animate-pulse"></span>
                        <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Pit-Wall Strategy Selector</h2>
                    </div>
                    <span class="text-xs font-mono text-zinc-400">Split-Strategy Calibration</span>
                </div>

                <!-- 1-Click Strategy Presets Bar -->
                <div class="mb-6 p-4 rounded bg-zinc-950/90 border border-zinc-800/90">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-black uppercase text-amber-400 tracking-wider">⚡ 1-Click Strategy Presets</span>
                            <span class="text-[9px] font-mono text-zinc-500 uppercase">Quick Calibration</span>
                        </div>
                        @if($race->weather === 'wet')
                            <span class="text-[10px] font-mono font-bold text-blue-400 bg-blue-950/80 border border-blue-500/40 px-2 py-0.5 rounded flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-ping"></span>
                                <span>Rain Recommended: Wet Protocol</span>
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 font-mono text-xs">
                        <!-- Preset 1: Aggressive Split -->
                        <button type="button"
                            @click="applyPreset('aggressive_split')"
                            :class="activePreset === 'aggressive_split' ? 'border-orange-500 bg-orange-950/50 ring-2 ring-orange-500/60 shadow-md shadow-orange-500/20' : 'border-zinc-800 bg-zinc-900 hover:border-orange-500/60 hover:bg-zinc-850 text-zinc-300'"
                            class="p-2.5 rounded border text-left transition cursor-pointer flex flex-col justify-between">
                            <div>
                                <div class="font-bold text-white flex items-center gap-1.5">
                                    <span>🚀 Aggressive Split</span>
                                </div>
                                <div class="text-[9px] text-zinc-400 mt-1 leading-tight">
                                    Car 1: Soft / Push<br>Car 2: Med / Balanced
                                </div>
                            </div>
                            <div class="mt-2 text-[8px] uppercase tracking-wider text-orange-400 font-bold">Max Attack</div>
                        </button>

                        <!-- Preset 2: Safe Conserve -->
                        <button type="button"
                            @click="applyPreset('safe_conserve')"
                            :class="activePreset === 'safe_conserve' ? 'border-emerald-500 bg-emerald-950/50 ring-2 ring-emerald-500/60 shadow-md shadow-emerald-500/20' : 'border-zinc-800 bg-zinc-900 hover:border-emerald-500/60 hover:bg-zinc-850 text-zinc-300'"
                            class="p-2.5 rounded border text-left transition cursor-pointer flex flex-col justify-between">
                            <div>
                                <div class="font-bold text-white flex items-center gap-1.5">
                                    <span>🛡️ Safe Conserve</span>
                                </div>
                                <div class="text-[9px] text-zinc-400 mt-1 leading-tight">
                                    {{ $race->weather === 'wet' ? 'Both: Wet / Conserve' : 'Both: Hard / Conserve' }}<br>+15 Rel / Low Wear
                                </div>
                            </div>
                            <div class="mt-2 text-[8px] uppercase tracking-wider text-emerald-400 font-bold">Max Reliability</div>
                        </button>

                        <!-- Preset 3: Balanced Standard -->
                        <button type="button"
                            @click="applyPreset('balanced_standard')"
                            :class="activePreset === 'balanced_standard' ? 'border-amber-400 bg-amber-950/50 ring-2 ring-amber-400/60 shadow-md shadow-amber-400/20' : 'border-zinc-800 bg-zinc-900 hover:border-amber-500/60 hover:bg-zinc-850 text-zinc-300'"
                            class="p-2.5 rounded border text-left transition cursor-pointer flex flex-col justify-between">
                            <div>
                                <div class="font-bold text-white flex items-center gap-1.5">
                                    <span>⚖️ Balanced Standard</span>
                                </div>
                                <div class="text-[9px] text-zinc-400 mt-1 leading-tight">
                                    Both: Medium / Balanced<br>Optimal Telemetry
                                </div>
                            </div>
                            <div class="mt-2 text-[8px] uppercase tracking-wider text-amber-400 font-bold">Equilibrium</div>
                        </button>

                        <!-- Preset 4: Wet Weather Protocol -->
                        <button type="button"
                            @click="applyPreset('wet_weather')"
                            :class="activePreset === 'wet_weather' ? 'border-blue-500 bg-blue-950/50 ring-2 ring-blue-500/60 shadow-md shadow-blue-500/20' : 'border-zinc-800 bg-zinc-900 hover:border-blue-500/60 hover:bg-zinc-850 text-zinc-300'"
                            class="p-2.5 rounded border text-left transition cursor-pointer flex flex-col justify-between relative overflow-hidden">
                            @if($race->weather === 'wet')
                                <div class="absolute top-0 right-0 w-2 h-2 rounded-full bg-blue-400 animate-ping m-1"></div>
                            @endif
                            <div>
                                <div class="font-bold text-white flex items-center gap-1.5">
                                    <span>🌧️ Wet Protocol</span>
                                </div>
                                <div class="text-[9px] text-zinc-400 mt-1 leading-tight">
                                    Both: Wet / Balanced<br>Aquaplaning Defense
                                </div>
                            </div>
                            <div class="mt-2 text-[8px] uppercase tracking-wider text-blue-400 font-bold">Wet Track Spec</div>
                        </button>
                    </div>
                </div>

                <!-- Strategy Form (wraps inputs and triggers execution) -->
                <form id="race-sim-form" method="POST" action="{{ route('races.run', $race) }}" class="space-y-8">
                    @csrf

                    <!-- Car #1 Tactical Section -->
                    <div class="bg-zinc-950/70 border border-cyan-500/30 rounded p-5 space-y-5">
                        <div class="flex items-center justify-between border-b border-zinc-800/80 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 shadow-sm shadow-cyan-400/50"></span>
                                <h3 class="text-xs font-mono font-black uppercase text-white tracking-wider">
                                    Car #1 Strategy &bull; {{ $driver1 ? $driver1->name : 'Driver 1' }} ({{ $car1 ? $car1->name : 'Car 1' }})
                                </h3>
                            </div>
                            <span class="text-[10px] font-mono text-cyan-400 bg-cyan-950 px-2 py-0.5 rounded border border-cyan-800/60 font-bold">PRIMARY TACTIC</span>
                        </div>

                        <!-- 1A. Car 1 Tire Compound Selection -->
                        <div>
                            <div class="text-[11px] font-mono text-zinc-400 uppercase font-bold mb-2">Tire Compound Allocation:</div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs font-mono">
                                <!-- Soft (C3) -->
                                <label :class="c1Compound === 'soft' ? 'border-red-500 bg-red-950/40 ring-2 ring-red-500/60 shadow-md shadow-red-500/20' : 'border-zinc-800 bg-zinc-900 hover:border-red-500/60 text-zinc-400'" class="p-2.5 rounded border cursor-pointer transition flex flex-col justify-between">
                                    <input type="radio" name="tire_compound" value="soft" x-model="c1Compound" @change="activePreset = null" class="sr-only">
                                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-sm shadow-red-500/50"></span><strong class="text-white">SOFT (C3)</strong></div>
                                    <span class="text-[9px] text-red-400 block mt-1 font-semibold">-0.85s Pace</span>
                                </label>

                                <!-- Medium (C2) -->
                                <label :class="c1Compound === 'medium' ? 'border-amber-400 bg-amber-950/40 ring-2 ring-amber-400/60 shadow-md shadow-amber-400/20' : 'border-zinc-800 bg-zinc-900 hover:border-amber-500/60 text-zinc-400'" class="p-2.5 rounded border cursor-pointer transition flex flex-col justify-between">
                                    <input type="radio" name="tire_compound" value="medium" x-model="c1Compound" @change="activePreset = null" class="sr-only" {{ ($race->weather !== 'wet') ? 'checked' : '' }}>
                                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-sm shadow-amber-400/50"></span><strong class="text-white">MEDIUM (C2)</strong></div>
                                    <span class="text-[9px] text-amber-400 block mt-1 font-semibold">Balanced</span>
                                </label>

                                <!-- Hard (C1) - Enhanced Outline -->
                                <label :class="c1Compound === 'hard' ? 'border-zinc-100 bg-zinc-800/60 ring-2 ring-zinc-300 shadow-md shadow-zinc-300/20' : 'border-zinc-800 bg-zinc-900 hover:border-zinc-400 text-zinc-400'" class="p-2.5 rounded border cursor-pointer transition flex flex-col justify-between">
                                    <input type="radio" name="tire_compound" value="hard" x-model="c1Compound" @change="activePreset = null" class="sr-only">
                                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-zinc-200 shadow-sm shadow-zinc-200/50"></span><strong class="text-white">HARD (C1)</strong></div>
                                    <span class="text-[9px] text-zinc-300 block mt-1 font-semibold">Durable</span>
                                </label>

                                <!-- Wet Rain -->
                                <label :class="c1Compound === 'wet' ? 'border-blue-500 bg-blue-950/40 ring-2 ring-blue-500/60 shadow-md shadow-blue-500/20' : 'border-zinc-800 bg-zinc-900 hover:border-blue-500/60 text-zinc-400'" class="p-2.5 rounded border cursor-pointer transition flex flex-col justify-between">
                                    <input type="radio" name="tire_compound" value="wet" x-model="c1Compound" @change="activePreset = null" class="sr-only" {{ ($race->weather === 'wet') ? 'checked' : '' }}>
                                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-sm shadow-blue-500/50"></span><strong class="text-white">WET RAIN</strong></div>
                                    <span class="text-[9px] text-blue-400 block mt-1 font-semibold">Rain Spec</span>
                                </label>
                            </div>
                        </div>

                        <!-- 1B. Car 1 Driving Mode -->
                        <div>
                            <div class="text-[11px] font-mono text-zinc-400 uppercase font-bold mb-2">Engine & Driving Mode:</div>
                            <div class="grid grid-cols-3 gap-2.5 text-xs font-mono">
                                <!-- Push -->
                                <label :class="c1Mode === 'push' ? 'border-red-500 bg-red-950/40 ring-2 ring-red-500/60 shadow-md shadow-red-500/20' : 'border-zinc-800 bg-zinc-900 hover:border-red-500/60 text-zinc-400'" class="p-2.5 rounded border cursor-pointer transition flex flex-col justify-between">
                                    <input type="radio" name="driving_mode" value="push" x-model="c1Mode" @change="activePreset = null" class="sr-only">
                                    <div class="font-bold text-white">⚡ PUSH (AGGRESSIVE)</div>
                                    <span class="text-[9px] text-red-400 block mt-0.5 font-semibold">-0.45s / +Wear</span>
                                </label>

                                <!-- Balanced - Enhanced Outline -->
                                <label :class="c1Mode === 'balanced' ? 'border-zinc-100 bg-zinc-800/60 ring-2 ring-zinc-300 shadow-md shadow-zinc-300/20' : 'border-zinc-800 bg-zinc-900 hover:border-zinc-400 text-zinc-400'" class="p-2.5 rounded border cursor-pointer transition flex flex-col justify-between">
                                    <input type="radio" name="driving_mode" value="balanced" x-model="c1Mode" @change="activePreset = null" class="sr-only" checked>
                                    <div class="font-bold text-white">⚙️ BALANCED</div>
                                    <span class="text-[9px] text-zinc-300 block mt-0.5 font-semibold">Optimal Balance</span>
                                </label>

                                <!-- Conserve -->
                                <label :class="c1Mode === 'conserve' ? 'border-emerald-500 bg-emerald-950/40 ring-2 ring-emerald-500/60 shadow-md shadow-emerald-500/20' : 'border-zinc-800 bg-zinc-900 hover:border-emerald-500/60 text-zinc-400'" class="p-2.5 rounded border cursor-pointer transition flex flex-col justify-between">
                                    <input type="radio" name="driving_mode" value="conserve" x-model="c1Mode" @change="activePreset = null" class="sr-only">
                                    <div class="font-bold text-white">🛡️ CONSERVE (DEFENSE)</div>
                                    <span class="text-[9px] text-emerald-400 block mt-0.5 font-semibold">+15 Rel / -30% Deg</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Car #2 Tactical Section (if 2-car ready) -->
                    @if($isTwoCarReady)
                        <div class="bg-zinc-950/70 border border-blue-500/30 rounded p-5 space-y-5">
                            <div class="flex items-center justify-between border-b border-zinc-800/80 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-400 shadow-sm shadow-blue-400/50"></span>
                                    <h3 class="text-xs font-mono font-black uppercase text-white tracking-wider">
                                        Car #2 Strategy &bull; {{ $driver2->name }} ({{ $car2->name }})
                                    </h3>
                                </div>
                                <span class="text-[10px] font-mono text-blue-400 bg-blue-950 px-2 py-0.5 rounded border border-blue-800/60 font-bold">SPLIT TACTIC</span>
                            </div>

                            <!-- 2A. Car 2 Tire Compound Selection -->
                            <div>
                                <div class="text-[11px] font-mono text-zinc-400 uppercase font-bold mb-2">Tire Compound Allocation:</div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs font-mono">
                                    <!-- Soft -->
                                    <label :class="c2Compound === 'soft' ? 'border-red-500 bg-red-950/40 ring-2 ring-red-500/60 shadow-md shadow-red-500/20' : 'border-zinc-800 bg-zinc-900 hover:border-red-500/60 text-zinc-400'" class="p-2.5 rounded border cursor-pointer transition flex flex-col justify-between">
                                        <input type="radio" name="tire_compound_2" value="soft" x-model="c2Compound" @change="activePreset = null" class="sr-only">
                                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-sm shadow-red-500/50"></span><strong class="text-white">SOFT</strong></div>
                                        <span class="text-[9px] text-red-400 block mt-1 font-semibold">-0.85s Pace</span>
                                    </label>

                                    <!-- Medium -->
                                    <label :class="c2Compound === 'medium' ? 'border-amber-400 bg-amber-950/40 ring-2 ring-amber-400/60 shadow-md shadow-amber-400/20' : 'border-zinc-800 bg-zinc-900 hover:border-amber-500/60 text-zinc-400'" class="p-2.5 rounded border cursor-pointer transition flex flex-col justify-between">
                                        <input type="radio" name="tire_compound_2" value="medium" x-model="c2Compound" @change="activePreset = null" class="sr-only" {{ ($race->weather !== 'wet') ? 'checked' : '' }}>
                                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-sm shadow-amber-400/50"></span><strong class="text-white">MEDIUM</strong></div>
                                        <span class="text-[9px] text-amber-400 block mt-1 font-semibold">Balanced</span>
                                    </label>

                                    <!-- Hard - Enhanced Outline -->
                                    <label :class="c2Compound === 'hard' ? 'border-zinc-100 bg-zinc-800/60 ring-2 ring-zinc-300 shadow-md shadow-zinc-300/20' : 'border-zinc-800 bg-zinc-900 hover:border-zinc-400 text-zinc-400'" class="p-2.5 rounded border cursor-pointer transition flex flex-col justify-between">
                                        <input type="radio" name="tire_compound_2" value="hard" x-model="c2Compound" @change="activePreset = null" class="sr-only">
                                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-zinc-200 shadow-sm shadow-zinc-200/50"></span><strong class="text-white">HARD</strong></div>
                                        <span class="text-[9px] text-zinc-300 block mt-1 font-semibold">Durable</span>
                                    </label>

                                    <!-- Wet Rain -->
                                    <label :class="c2Compound === 'wet' ? 'border-blue-500 bg-blue-950/40 ring-2 ring-blue-500/60 shadow-md shadow-blue-500/20' : 'border-zinc-800 bg-zinc-900 hover:border-blue-500/60 text-zinc-400'" class="p-2.5 rounded border cursor-pointer transition flex flex-col justify-between">
                                        <input type="radio" name="tire_compound_2" value="wet" x-model="c2Compound" @change="activePreset = null" class="sr-only" {{ ($race->weather === 'wet') ? 'checked' : '' }}>
                                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-sm shadow-blue-500/50"></span><strong class="text-white">WET RAIN</strong></div>
                                        <span class="text-[9px] text-blue-400 block mt-1 font-semibold">Rain Spec</span>
                                    </label>
                                </div>
                            </div>

                            <!-- 2B. Car 2 Driving Mode -->
                            <div>
                                <div class="text-[11px] font-mono text-zinc-400 uppercase font-bold mb-2">Engine & Driving Mode:</div>
                                <div class="grid grid-cols-3 gap-2.5 text-xs font-mono">
                                    <!-- Push -->
                                    <label :class="c2Mode === 'push' ? 'border-red-500 bg-red-950/40 ring-2 ring-red-500/60 shadow-md shadow-red-500/20' : 'border-zinc-800 bg-zinc-900 hover:border-red-500/60 text-zinc-400'" class="p-2.5 rounded border cursor-pointer transition flex flex-col justify-between">
                                        <input type="radio" name="driving_mode_2" value="push" x-model="c2Mode" @change="activePreset = null" class="sr-only">
                                        <div class="font-bold text-white">⚡ PUSH</div>
                                        <span class="text-[9px] text-red-400 block mt-0.5 font-semibold">-0.45s / +Wear</span>
                                    </label>

                                    <!-- Balanced - Enhanced Outline -->
                                    <label :class="c2Mode === 'balanced' ? 'border-zinc-100 bg-zinc-800/60 ring-2 ring-zinc-300 shadow-md shadow-zinc-300/20' : 'border-zinc-800 bg-zinc-900 hover:border-zinc-400 text-zinc-400'" class="p-2.5 rounded border cursor-pointer transition flex flex-col justify-between">
                                        <input type="radio" name="driving_mode_2" value="balanced" x-model="c2Mode" @change="activePreset = null" class="sr-only" checked>
                                        <div class="font-bold text-white">⚙️ BALANCED</div>
                                        <span class="text-[9px] text-zinc-300 block mt-0.5 font-semibold">Optimal Balance</span>
                                    </label>

                                    <!-- Conserve -->
                                    <label :class="c2Mode === 'conserve' ? 'border-emerald-500 bg-emerald-950/40 ring-2 ring-emerald-500/60 shadow-md shadow-emerald-500/20' : 'border-zinc-800 bg-zinc-900 hover:border-emerald-500/60 text-zinc-400'" class="p-2.5 rounded border cursor-pointer transition flex flex-col justify-between">
                                        <input type="radio" name="driving_mode_2" value="conserve" x-model="c2Mode" @change="activePreset = null" class="sr-only">
                                        <div class="font-bold text-white">🛡️ CONSERVE</div>
                                        <span class="text-[9px] text-emerald-400 block mt-0.5 font-semibold">+15 Rel / -30% Deg</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif
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
            @if($latestResult)
                <div class="bg-zinc-900 border border-indigo-500/40 rounded p-5 shadow-lg">
                    <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-indigo-400 mb-2">
                        Season {{ $currentSeason }} Grand Prix Result
                    </div>
                    <div class="flex justify-between items-baseline font-mono text-xs">
                        <span class="text-zinc-300">Finish Position:</span>
                        <span class="text-indigo-300 font-black text-sm">P{{ $latestResult->position }}</span>
                    </div>
                    <div class="flex justify-between items-baseline font-mono text-xs mt-1">
                        <span class="text-zinc-500">Earned Prize:</span>
                        <span class="text-amber-400 font-bold">+{{ number_format($latestResult->prize_money) }} CR</span>
                    </div>
                </div>
            @elseif($pastResult)
                <div class="bg-zinc-900 border border-zinc-700/60 rounded p-5 shadow-lg">
                    <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-zinc-400 mb-2">
                        Historic Record (Season {{ $pastResult->season }})
                    </div>
                    <div class="flex justify-between items-baseline font-mono text-xs">
                        <span class="text-zinc-300">Previous Finish:</span>
                        <span class="text-zinc-200 font-bold text-sm">P{{ $pastResult->position }}</span>
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
