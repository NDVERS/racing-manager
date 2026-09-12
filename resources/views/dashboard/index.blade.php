@extends('layouts.app')

@section('title', $team->name . ' - Paddock Command Hub')

@section('content')
<div class="space-y-6">
    <!-- Top Team Header / Constructor Banner -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 sm:p-8 shadow-xl relative overflow-hidden">
        <!-- Racing Livery Top Accent Stripe -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 via-amber-400 to-red-600"></div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-2.5 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[11px] font-mono uppercase tracking-widest text-zinc-400">OFFICIAL CONSTRUCTOR PADDOCK</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight uppercase font-mono">
                    {{ $team->name }}
                </h1>
                <p class="text-xs text-zinc-400 mt-1.5 font-mono">
                    CHIEF MANAGER: <span class="text-zinc-200 font-bold">{{ auth()->user()->name }}</span> &bull; STATUS: <span class="text-emerald-400 font-semibold">ACTIVE IN CHAMPIONSHIP</span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Money Card -->
                <div class="bg-zinc-950/90 border border-zinc-800 rounded px-4 py-3 min-w-[150px]">
                    <div class="text-[10px] font-mono font-bold text-zinc-400 uppercase tracking-wider">TEAM TREASURY</div>
                    <div class="text-xl sm:text-2xl font-mono font-black text-amber-400 mt-0.5">
                        {{ number_format($team->money) }} <span class="text-xs font-sans text-zinc-500 font-normal">CR</span>
                    </div>
                </div>

                <!-- Reputation Card -->
                <div class="bg-zinc-950/90 border border-zinc-800 rounded px-4 py-3 min-w-[130px]">
                    <div class="text-[10px] font-mono font-bold text-zinc-400 uppercase tracking-wider">REPUTATION</div>
                    <div class="text-xl sm:text-2xl font-mono font-black text-cyan-400 mt-0.5">
                        {{ number_format($team->reputation) }} <span class="text-xs font-sans text-zinc-500 font-normal">PTS</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Race Readiness & Telemetry Status Panel -->
    <div class="bg-zinc-900/90 border border-zinc-800 rounded p-6 shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-5 border-b border-zinc-800/80 gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full {{ $isRaceReady ? 'bg-emerald-500' : 'bg-amber-500' }} animate-ping"></span>
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-white">RACE READINESS TELEMETRY</span>
            </div>
            <div class="flex items-center gap-2">
                @if($isRaceReady)
                    <span class="text-[11px] font-mono font-bold bg-emerald-950 border border-emerald-500/40 text-emerald-300 px-2.5 py-1 rounded">
                        STATUS: GREEN FLAG // READY TO COMPETE
                    </span>
                @else
                    <span class="text-[11px] font-mono font-bold bg-amber-950 border border-amber-500/40 text-amber-300 px-2.5 py-1 rounded">
                        STATUS: INCOMPLETE SETUP // CONFIGURE ASSETS
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Active Vehicle Card -->
            <div class="bg-zinc-950/80 border border-zinc-800 rounded p-4 relative">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-orange-400 font-bold">PRIMARY RACE CHASSIS</span>
                    <a href="{{ route('garage.index') }}" class="text-[10px] font-mono text-zinc-400 hover:text-orange-400 underline underline-offset-2">
                        GARAGE FLEET &rarr;
                    </a>
                </div>

                @if($activeCar)
                    <div class="flex items-baseline justify-between mb-3">
                        <span class="text-lg font-black text-white uppercase font-mono">{{ $activeCar->name }}</span>
                        <span class="text-xs font-mono font-bold bg-orange-950 border border-orange-500/40 text-orange-300 px-2 py-0.5 rounded">
                            LEVEL {{ $activeCar->level }}
                        </span>
                    </div>

                    <!-- Quick Telemetry Gauges -->
                    <div class="grid grid-cols-5 gap-1.5 text-center text-xs font-mono">
                        <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800/80">
                            <div class="text-[9px] text-zinc-500 uppercase">SPD</div>
                            <div class="font-bold text-white">{{ $activeCar->speed }}</div>
                        </div>
                        <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800/80">
                            <div class="text-[9px] text-zinc-500 uppercase">ACC</div>
                            <div class="font-bold text-white">{{ $activeCar->acceleration }}</div>
                        </div>
                        <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800/80">
                            <div class="text-[9px] text-zinc-500 uppercase">HND</div>
                            <div class="font-bold text-white">{{ $activeCar->handling }}</div>
                        </div>
                        <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800/80">
                            <div class="text-[9px] text-zinc-500 uppercase">BRK</div>
                            <div class="font-bold text-white">{{ $activeCar->braking }}</div>
                        </div>
                        <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800/80">
                            <div class="text-[9px] text-zinc-500 uppercase">REL</div>
                            <div class="font-bold text-emerald-400">{{ $activeCar->reliability }}%</div>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-zinc-900 flex justify-between items-center text-xs">
                        <span class="text-[11px] text-zinc-400 font-mono">Total Fleet: {{ $totalCars }} car(s)</span>
                        <a href="{{ route('garage.show', $activeCar) }}" class="text-xs font-mono text-orange-400 hover:text-orange-300 font-semibold">
                            Technical Sheet &rarr;
                        </a>
                    </div>
                @else
                    <div class="py-6 text-center text-xs font-mono text-zinc-500">
                        No active car selected. <a href="{{ route('garage.index') }}" class="text-orange-400 underline">Select a vehicle</a>
                    </div>
                @endif
            </div>

            <!-- Lead Driver Card -->
            <div class="bg-zinc-950/80 border border-zinc-800 rounded p-4 relative">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-cyan-400 font-bold">LEAD RACE DRIVER</span>
                    <a href="{{ route('drivers.index') }}" class="text-[10px] font-mono text-zinc-400 hover:text-cyan-400 underline underline-offset-2">
                        DRIVER ROSTER &rarr;
                    </a>
                </div>

                @if($primaryDriver)
                    <div class="flex items-baseline justify-between mb-3">
                        <span class="text-lg font-black text-white uppercase font-mono">{{ $primaryDriver->name }}</span>
                        <span class="text-xs font-mono font-bold text-cyan-300">
                            {{ number_format($primaryDriver->salary) }} <span class="text-[10px] text-zinc-500">CR/RACE</span>
                        </span>
                    </div>

                    <!-- Driver Stats -->
                    <div class="grid grid-cols-4 gap-2 text-center text-xs font-mono">
                        <div class="bg-zinc-900 p-2 rounded border border-zinc-800/80">
                            <div class="text-[9px] text-zinc-500 uppercase">Pace</div>
                            <div class="font-bold text-white">{{ $primaryDriver->pace }}</div>
                        </div>
                        <div class="bg-zinc-900 p-2 rounded border border-zinc-800/80">
                            <div class="text-[9px] text-zinc-500 uppercase">Cornering</div>
                            <div class="font-bold text-white">{{ $primaryDriver->cornering }}</div>
                        </div>
                        <div class="bg-zinc-900 p-2 rounded border border-zinc-800/80">
                            <div class="text-[9px] text-zinc-500 uppercase">Consistency</div>
                            <div class="font-bold text-white">{{ $primaryDriver->consistency }}</div>
                        </div>
                        <div class="bg-zinc-900 p-2 rounded border border-zinc-800/80">
                            <div class="text-[9px] text-zinc-500 uppercase">Experience</div>
                            <div class="font-bold text-white">{{ $primaryDriver->experience }}</div>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-zinc-900 flex justify-between items-center text-xs">
                        <span class="text-[11px] text-zinc-400 font-mono">Roster: {{ $totalDrivers }} driver(s)</span>
                        <a href="{{ route('drivers.show', $primaryDriver) }}" class="text-xs font-mono text-cyan-400 hover:text-cyan-300 font-semibold">
                            Profile Sheet &rarr;
                        </a>
                    </div>
                @else
                    <div class="py-6 text-center text-xs font-mono text-zinc-500">
                        No contracted driver available. <a href="{{ route('drivers.market') }}" class="text-cyan-400 underline">Scout Driver Market</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions / Command Deck Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Garage Link -->
        <a href="{{ route('garage.index') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-orange-500/60 rounded p-5 transition-all shadow hover:shadow-orange-500/10 block">
            <div class="flex items-center justify-between mb-3">
                <span class="w-8 h-8 rounded bg-orange-950 border border-orange-500/30 flex items-center justify-center text-orange-400 font-mono font-bold text-xs">
                    01
                </span>
                <span class="text-xs font-mono text-zinc-500 group-hover:text-orange-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-sm font-black text-white uppercase font-mono tracking-wide group-hover:text-orange-400 transition-colors">
                Garage Fleet
            </h2>
            <p class="text-xs text-zinc-400 mt-1 font-mono">
                Inspect chassis specs, view telemetry ratings, and select active cars.
            </p>
        </a>

        <!-- Driver Lineup Link (Active in Task 4) -->
        <a href="{{ route('drivers.index') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-cyan-500/60 rounded p-5 transition-all shadow hover:shadow-cyan-500/10 block">
            <div class="flex items-center justify-between mb-3">
                <span class="w-8 h-8 rounded bg-cyan-950 border border-cyan-500/30 flex items-center justify-center text-cyan-400 font-mono font-bold text-xs">
                    02
                </span>
                <span class="text-xs font-mono text-zinc-500 group-hover:text-cyan-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-sm font-black text-white uppercase font-mono tracking-wide group-hover:text-cyan-400 transition-colors">
                Driver Lineup
            </h2>
            <p class="text-xs text-zinc-400 mt-1 font-mono">
                Manage contracted drivers, assign lead seat, and scout the market.
            </p>
        </a>

        <!-- Race Hub (Active in Task 5) -->
        <a href="{{ route('races.index') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-red-500/60 rounded p-5 transition-all shadow hover:shadow-red-500/10 block">
            <div class="flex items-center justify-between mb-3">
                <span class="w-8 h-8 rounded bg-red-950 border border-red-500/30 flex items-center justify-center text-red-400 font-mono font-bold text-xs">
                    03
                </span>
                <span class="text-xs font-mono text-zinc-500 group-hover:text-red-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-sm font-black text-white uppercase font-mono tracking-wide group-hover:text-red-400 transition-colors">
                Grand Prix Hub
            </h2>
            <p class="text-xs text-zinc-400 mt-1 font-mono">
                Inspect track telemetry, review prize pools, and configure race entries.
            </p>
        </a>

        <!-- Engineering / Upgrades (Task 6) -->
        <div class="bg-zinc-900/60 border border-zinc-800/60 rounded p-5 opacity-75">
            <div class="flex items-center justify-between mb-3">
                <span class="w-8 h-8 rounded bg-zinc-950 border border-zinc-800 flex items-center justify-center text-zinc-500 font-mono font-bold text-xs">
                    04
                </span>
                <span class="text-[9px] font-mono bg-zinc-800 text-zinc-400 px-2 py-0.5 rounded">TASK 6</span>
            </div>
            <h2 class="text-sm font-black text-zinc-400 uppercase font-mono tracking-wide">
                R&D Upgrades
            </h2>
            <p class="text-xs text-zinc-500 mt-1 font-mono">
                Engine tuning, aero upgrades, suspension, and reliability parts.
            </p>
        </div>
    </div>
</div>
@endsection
