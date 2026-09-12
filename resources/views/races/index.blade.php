@extends('layouts.app')

@section('title', 'Grand Prix Calendar - Season ' . $currentSeason . ' - ' . $team->name)

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
                    <span>/</span>
                    <span class="text-amber-400 font-bold uppercase">SEASON {{ $currentSeason }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight flex items-center gap-3">
                    <span>Championship Schedule</span>
                    <span class="text-xs font-mono font-bold bg-orange-950/80 text-orange-400 border border-orange-500/40 px-2.5 py-1 rounded">
                        SEASON {{ $currentSeason }}
                    </span>
                </h1>
                <p class="text-xs text-zinc-400 mt-1 font-mono">
                    Official {{ $races->count() }}-round FIA championship calendar. Complete all Grand Prix rounds to conclude Season {{ $currentSeason }}.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('standings') }}" class="px-3.5 py-2 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 hover:border-amber-500/50 text-amber-400 hover:text-amber-300 text-xs font-mono font-bold uppercase transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span>Standings &rarr;</span>
                </a>

                <a href="{{ route('races.history') }}" class="px-3.5 py-2 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 hover:border-red-500/50 text-zinc-300 hover:text-white text-xs font-mono font-bold uppercase transition flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span>Archives</span>
                </a>

                <div class="bg-zinc-950/90 border border-zinc-800 rounded px-4 py-2 text-right font-mono">
                    <div class="text-[10px] text-zinc-400 uppercase tracking-wider">SEASON PROGRESS</div>
                    <div class="text-base font-black text-white">
                        <span class="text-orange-400">{{ $teamResults->count() }}</span> / {{ $races->count() }} <span class="text-xs text-zinc-500 font-normal">DONE</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Season Finale Call-to-Action Banner (Shown when all rounds completed) -->
    @if($isSeasonCompleted)
        <div class="bg-gradient-to-r from-amber-950/60 via-zinc-900 to-zinc-950 border-2 border-amber-500/60 rounded-lg p-6 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-400 animate-ping"></span>
                        <span class="text-xs font-mono font-bold uppercase tracking-widest text-amber-400 bg-amber-950/80 px-2.5 py-0.5 rounded border border-amber-500/30">
                            SEASON {{ $currentSeason }} FINALE COMPLETED
                        </span>
                    </div>
                    <h2 class="text-xl font-black text-white uppercase font-mono tracking-tight">
                        All Grand Prix Rounds Concluded for Season {{ $currentSeason }}!
                    </h2>
                    <p class="text-xs text-zinc-300 font-mono leading-relaxed max-w-2xl">
                        Tim Anda telah menyelesaikan seluruh seri kalender balap. Selesaikan evaluasi klasemen akhir dan mulailah musim kompetisi baru di <strong class="text-amber-400">Season {{ $currentSeason + 1 }}</strong> dengan membawa armada mobil & pembalap yang sudah Anda kembangkan!
                    </p>
                    <div class="flex flex-wrap items-center gap-4 text-xs font-mono pt-1 text-zinc-400">
                        <div>Constructor Rank: <span class="text-white font-bold">P{{ $seasonOverview['player_constructor_rank'] }}</span> ({{ $seasonOverview['player_constructor_points'] }} PTS)</div>
                        <div>&bull;</div>
                        <div>Season Finale Bonus: <span class="text-amber-400 font-bold font-telemetry">+Bonus CR & REP</span></div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0">
                    <a href="{{ route('standings') }}" class="w-full sm:w-auto px-4 py-3 rounded bg-zinc-900 hover:bg-zinc-800 border border-zinc-700 text-zinc-200 text-xs font-mono font-bold uppercase transition text-center shadow">
                        Review Final Standings
                    </a>

                    <form method="POST" action="{{ route('season.advance') }}" class="w-full sm:w-auto">
                        @csrf
                        <button
                            type="submit"
                            class="w-full sm:w-auto px-6 py-3 rounded bg-gradient-to-r from-amber-500 via-orange-500 to-red-600 hover:from-amber-400 hover:to-red-500 text-black font-mono font-black text-xs uppercase tracking-wider transition shadow-lg shadow-orange-950/50 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <span>🏁 ADVANCE TO SEASON {{ $currentSeason + 1 }}</span>
                            <span>&rarr;</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

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
                <span>Season {{ $currentSeason }} Grand Prix Rounds</span>
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

