@extends('layouts.app')

@section('title', 'Grand Prix Calendar - ' . $team->name)

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
                    <span class="text-red-400 font-bold uppercase">GRAND PRIX CALENDAR</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight">
                    Official Championship Schedule
                </h1>
                <p class="text-xs text-zinc-400 mt-1 font-mono">
                    Select a Grand Prix circuit, review track characteristics, and prepare your constructor grid entry.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="bg-zinc-950/90 border border-zinc-800 rounded px-4 py-2.5 text-right">
                    <div class="text-[10px] font-mono text-zinc-400 uppercase tracking-wider">SEASON STAGES</div>
                    <div class="text-xl font-mono font-black text-white">
                        {{ $races->count() }} <span class="text-xs text-zinc-500 font-normal">ROUNDS</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lineup Telemetry Status Banner -->
    <div class="bg-zinc-900/90 border border-zinc-800 rounded p-5 shadow-lg">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded bg-zinc-950 border border-zinc-800 flex items-center justify-center font-mono font-bold text-lg {{ $isRaceReady ? 'text-emerald-400' : 'text-amber-400' }} shrink-0">
                    @if($isRaceReady)
                        <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    @else
                        <span class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></span>
                    @endif
                </div>

                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest {{ $isRaceReady ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ $isRaceReady ? 'RACE READINESS // GRID READY' : 'RACE READINESS // INCOMPLETE SETUP' }}
                        </span>
                    </div>
                    <div class="text-xs font-mono text-zinc-300 mt-0.5">
                        Active Chassis:
                        @if($activeCar)
                            <a href="{{ route('garage.show', $activeCar) }}" class="text-orange-400 font-bold hover:underline">{{ $activeCar->name }} (Lvl {{ $activeCar->level }})</a>
                        @else
                            <a href="{{ route('garage.index') }}" class="text-red-400 underline font-bold">None Selected</a>
                        @endif
                        &bull; Lead Driver:
                        @if($primaryDriver)
                            <a href="{{ route('drivers.show', $primaryDriver) }}" class="text-cyan-400 font-bold hover:underline">{{ $primaryDriver->name }} ({{ $primaryDriver->overallRating() }} OVR)</a>
                        @else
                            <a href="{{ route('drivers.index') }}" class="text-red-400 underline font-bold">None Assigned</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if(!$isRaceReady)
                    <span class="text-xs font-mono text-amber-300 bg-amber-950/60 border border-amber-500/30 px-3 py-1.5 rounded">
                        Configure assets in Garage & Drivers
                    </span>
                @else
                    <span class="text-xs font-mono text-emerald-300 bg-emerald-950/60 border border-emerald-500/30 px-3 py-1.5 rounded flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>All Systems Go</span>
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Race Calendar Grid -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded bg-red-500"></span>
                <span>Grand Prix Championship Rounds</span>
            </h2>
            <span class="text-xs font-mono text-zinc-400">{{ $races->count() }} Circuits Open</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($races as $race)
                @php
                    $result = $teamResults->get($race->id);
                @endphp
                <div class="bg-zinc-900/90 border border-zinc-800 rounded p-6 shadow-md transition-all flex flex-col justify-between hover:border-zinc-700">
                    <div>
                        <!-- Header & Location -->
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div>
                                <span class="text-[10px] font-mono text-zinc-500 uppercase tracking-wider">ROUND #{{ str_pad($race->id, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3 class="text-lg font-black text-white font-mono uppercase tracking-tight">{{ $race->name }}</h3>
                                <p class="text-xs font-mono text-zinc-400 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                    <span>{{ $race->location }}</span>
                                </p>
                            </div>

                            @if($result)
                                <span class="text-[10px] font-mono font-black uppercase tracking-wider bg-indigo-950 border border-indigo-500/50 text-indigo-300 px-2 py-0.5 rounded">
                                    P{{ $result->position }} FINISH
                                </span>
                            @else
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider bg-zinc-950 border border-zinc-800 text-zinc-400 px-2 py-0.5 rounded">
                                    UPCOMING
                                </span>
                            @endif
                        </div>

                        <!-- Circuit Telemetry Tags -->
                        <div class="flex flex-wrap gap-1.5 mb-4">
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-zinc-950 border border-zinc-800 text-zinc-300">
                                {{ $race->laps }} LAPS
                            </span>
                            <span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded {{ $race->track_type === 'technical' ? 'bg-amber-950/80 border border-amber-500/40 text-amber-300' : ($race->track_type === 'high_speed' ? 'bg-blue-950/80 border border-blue-500/40 text-blue-300' : 'bg-emerald-950/80 border border-emerald-500/40 text-emerald-300') }}">
                                {{ str_replace('_', ' ', $race->track_type) }}
                            </span>
                            <span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded {{ $race->weather === 'wet' ? 'bg-cyan-950/80 border border-cyan-500/40 text-cyan-300' : 'bg-zinc-800 border border-zinc-700 text-zinc-300' }}">
                                {{ $race->weather === 'wet' ? '🌧️ WET RAIN' : '☀️ DRY ASPHALT' }}
                            </span>
                        </div>

                        <!-- Financial & Prize Pool Breakdown -->
                        <div class="bg-zinc-950/80 border border-zinc-800/80 rounded p-3 mb-5 space-y-1.5 text-xs font-mono">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Entry Fee</span>
                                <span class="text-zinc-200 font-bold">{{ number_format($race->entry_fee) }} CR</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Total Prize Pool</span>
                                <span class="text-amber-400 font-black">{{ number_format($race->prize_pool) }} CR</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="pt-4 border-t border-zinc-800/80 flex items-center justify-between gap-3">
                        <a href="{{ route('races.show', $race) }}" class="w-full text-center text-xs font-mono font-bold text-white bg-red-600 hover:bg-red-500 rounded py-2.5 transition-all shadow-md flex items-center justify-center gap-1.5">
                            <span>Enter Paddock Grid</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 bg-zinc-900 border border-zinc-800 rounded p-12 text-center">
                    <p class="text-zinc-400 font-mono text-sm">No Grand Prix races scheduled for this championship season.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
