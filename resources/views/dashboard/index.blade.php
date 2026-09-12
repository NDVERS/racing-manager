@extends('layouts.app')

@section('title', $team->name . ' - Paddock Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Top Team Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-900/90 to-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 relative z-10">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-mono uppercase tracking-widest text-slate-400">Official Constructor</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight uppercase">
                    {{ $team->name }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Managed by <span class="text-slate-200 font-semibold">{{ auth()->user()->name }}</span> &bull; Status: <span class="text-emerald-400 font-semibold">Active in Championship</span>
                </p>
            </div>

            <div class="flex flex-wrap gap-4">
                <!-- Money Card -->
                <div class="bg-slate-950/80 border border-slate-800/80 rounded-2xl px-5 py-3.5 min-w-[150px]">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Treasury</div>
                    <div class="text-xl sm:text-2xl font-mono font-black text-amber-400 mt-0.5">
                        {{ number_format($team->money) }} <span class="text-xs font-sans text-slate-400">CR</span>
                    </div>
                </div>

                <!-- Reputation Card -->
                <div class="bg-slate-950/80 border border-slate-800/80 rounded-2xl px-5 py-3.5 min-w-[130px]">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Reputation</div>
                    <div class="text-xl sm:text-2xl font-mono font-black text-cyan-400 mt-0.5">
                        {{ number_format($team->reputation) }} <span class="text-xs font-sans text-slate-400">PTS</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Paddock Assets Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Garage (Cars) -->
        <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 font-bold text-xs">
                        CAR
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-white uppercase tracking-wider">Garage Roster</h2>
                        <span class="text-xs text-slate-400">{{ $team->cars->count() }} active vehicle(s)</span>
                    </div>
                </div>
                <span class="text-xs font-mono bg-slate-800 text-slate-300 px-2.5 py-1 rounded-full">Primary</span>
            </div>

            @forelse($team->cars as $car)
                <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-lg font-extrabold text-white">{{ $car->name }}</span>
                        <span class="text-xs font-mono text-amber-400 font-bold">Level {{ $car->level }}</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-center text-xs">
                        <div class="bg-slate-900/90 p-2 rounded-lg border border-slate-800">
                            <div class="text-[10px] text-slate-500 uppercase">Speed</div>
                            <div class="font-mono font-bold text-white">{{ $car->speed }}</div>
                        </div>
                        <div class="bg-slate-900/90 p-2 rounded-lg border border-slate-800">
                            <div class="text-[10px] text-slate-500 uppercase">Accel</div>
                            <div class="font-mono font-bold text-white">{{ $car->acceleration }}</div>
                        </div>
                        <div class="bg-slate-900/90 p-2 rounded-lg border border-slate-800">
                            <div class="text-[10px] text-slate-500 uppercase">Handling</div>
                            <div class="font-mono font-bold text-white">{{ $car->handling }}</div>
                        </div>
                        <div class="bg-slate-900/90 p-2 rounded-lg border border-slate-800">
                            <div class="text-[10px] text-slate-500 uppercase">Braking</div>
                            <div class="font-mono font-bold text-white">{{ $car->braking }}</div>
                        </div>
                        <div class="bg-slate-900/90 p-2 rounded-lg border border-slate-800 col-span-2 sm:col-span-1">
                            <div class="text-[10px] text-slate-500 uppercase">Reliability</div>
                            <div class="font-mono font-bold text-white">{{ $car->reliability }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-slate-500 text-xs">No cars in garage.</div>
            @endforelse
        </div>

        <!-- Drivers -->
        <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400 font-bold text-xs">
                        DRV
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-white uppercase tracking-wider">Driver Lineup</h2>
                        <span class="text-xs text-slate-400">{{ $team->drivers->count() }} contracted driver(s)</span>
                    </div>
                </div>
                <span class="text-xs font-mono bg-slate-800 text-slate-300 px-2.5 py-1 rounded-full">Lead</span>
            </div>

            @forelse($team->drivers as $driver)
                <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-lg font-extrabold text-white">{{ $driver->name }}</span>
                        <span class="text-xs font-mono text-cyan-400 font-bold">{{ number_format($driver->salary) }} CR/race</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
                        <div class="bg-slate-900/90 p-2 rounded-lg border border-slate-800">
                            <div class="text-[10px] text-slate-500 uppercase">Pace</div>
                            <div class="font-mono font-bold text-white">{{ $driver->pace }}</div>
                        </div>
                        <div class="bg-slate-900/90 p-2 rounded-lg border border-slate-800">
                            <div class="text-[10px] text-slate-500 uppercase">Cornering</div>
                            <div class="font-mono font-bold text-white">{{ $driver->cornering }}</div>
                        </div>
                        <div class="bg-slate-900/90 p-2 rounded-lg border border-slate-800">
                            <div class="text-[10px] text-slate-500 uppercase">Consistency</div>
                            <div class="font-mono font-bold text-white">{{ $driver->consistency }}</div>
                        </div>
                        <div class="bg-slate-900/90 p-2 rounded-lg border border-slate-800">
                            <div class="text-[10px] text-slate-500 uppercase">Experience</div>
                            <div class="font-mono font-bold text-white">{{ $driver->experience }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-slate-500 text-xs">No drivers contracted.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
