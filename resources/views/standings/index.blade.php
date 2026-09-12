@extends('layouts.app')

@section('title', 'Season ' . $selectedSeason . ' Championship Standings - ' . $team->name)

@section('content')
<div class="space-y-6">
    <!-- Header / Breadcrumb Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 via-orange-500 to-red-600"></div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1 text-xs font-mono text-zinc-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-amber-400 transition-colors">PADDOCK</a>
                    <span>/</span>
                    <a href="{{ route('standings') }}" class="hover:text-amber-400 transition-colors uppercase">CHAMPIONSHIP STANDINGS</a>
                    <span>/</span>
                    <span class="text-amber-400 font-bold uppercase">SEASON {{ $selectedSeason }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight flex items-center gap-3">
                    <span>Season Championship Standings</span>
                    @if($selectedSeason < $team->current_season)
                        <span class="text-xs font-mono font-bold bg-zinc-800 text-zinc-300 border border-zinc-700 px-2.5 py-1 rounded">
                            SEASON {{ $selectedSeason }} ARCHIVE
                        </span>
                    @else
                        <span class="text-xs font-mono font-bold bg-amber-950/80 text-amber-400 border border-amber-500/40 px-2.5 py-1 rounded">
                            CURRENT // SEASON {{ $selectedSeason }}
                        </span>
                    @endif
                </h1>
                <p class="text-xs text-zinc-400 mt-1 font-mono">
                    Official FIA Drivers' & Constructors' World Championship points classification table for Season {{ $selectedSeason }}.
                </p>
            </div>

            <!-- Season Selector Dropdown / Pills & Calendar Progress -->
            <div class="flex flex-wrap items-center gap-3 font-mono">
                <!-- Season Switcher Pills -->
                <div class="flex items-center gap-1.5 bg-zinc-950 border border-zinc-800 p-1 rounded">
                    <span class="text-[10px] text-zinc-500 uppercase px-2 font-bold">Season:</span>
                    @foreach($availableSeasons as $s)
                        <a
                            href="{{ route('standings', ['season' => $s]) }}"
                            class="px-3 py-1.5 rounded text-xs font-bold transition-all {{ $s === $selectedSeason ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900' }}"
                        >
                            S{{ $s }}
                        </a>
                    @endforeach
                </div>

                <div class="bg-zinc-950/80 border border-zinc-800 px-4 py-2.5 rounded text-right">
                    <div class="text-[10px] text-zinc-500 uppercase">Season Calendar Progress</div>
                    <div class="text-sm font-bold text-white mt-0.5">
                        <span class="text-orange-400 font-black">{{ $season['completed_rounds'] }}</span> / {{ $season['total_rounds'] }} Rounds Done
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Season Finale Call-to-Action Banner (Shown on current completed season) -->
    @if($season['is_current_season'] && $season['is_completed'])
        <div class="bg-gradient-to-r from-amber-950/60 via-zinc-900 to-zinc-950 border-2 border-amber-500/60 rounded-lg p-5 shadow-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
                    <span class="text-xs font-mono font-bold uppercase tracking-widest text-amber-400">SEASON {{ $selectedSeason }} COMPLETE</span>
                </div>
                <h3 class="text-base font-bold text-white font-mono mt-1">Ready for Season {{ $selectedSeason + 1 }}?</h3>
                <p class="text-xs text-zinc-300 font-mono mt-0.5">
                    Advance to the next championship year to claim season finale prizes and start a new 5-round campaign!
                </p>
            </div>
            <form method="POST" action="{{ route('season.advance') }}">
                @csrf
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-mono font-black text-xs uppercase tracking-wider transition shadow-lg flex items-center gap-2 cursor-pointer whitespace-nowrap"
                >
                    <span>🏁 ADVANCE TO SEASON {{ $selectedSeason + 1 }}</span>
                    <span>&rarr;</span>
                </button>
            </form>
        </div>
    @endif

    <!-- Player Championship Status Overview Deck -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Constructor Standing Overview -->
        <div class="bg-gradient-to-r from-zinc-900 via-zinc-900/95 to-zinc-950 border border-orange-500/40 rounded p-5 shadow-lg flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded bg-orange-950 border border-orange-500/60 flex items-center justify-center text-orange-400 font-mono font-black text-lg shadow-inner shrink-0">
                    P{{ $season['player_constructor_rank'] }}
                </div>
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-orange-400">YOUR CONSTRUCTOR STANDING</span>
                    <h2 class="text-lg font-black text-white font-mono uppercase tracking-tight mt-0.5">{{ $team->name }}</h2>
                    <div class="text-xs font-mono text-zinc-400 mt-0.5">
                        <span class="text-amber-400 font-bold">{{ $season['player_constructor_points'] }} PTS</span> &bull; Gap: <span class="text-zinc-300 font-semibold">{{ $season['constructor_leader_gap'] }}</span>
                    </div>
                </div>
            </div>
            <div class="hidden sm:block text-right font-mono text-xs text-zinc-400">
                <div class="text-[10px] text-zinc-500 uppercase">Season Leader</div>
                <div class="font-bold text-white mt-0.5">{{ $season['leader_constructor_name'] }}</div>
                <div class="text-amber-400 font-bold">{{ $season['leader_constructor_points'] }} PTS</div>
            </div>
        </div>

        <!-- Driver Standing Overview -->
        <div class="bg-gradient-to-r from-zinc-900 via-zinc-900/95 to-zinc-950 border border-cyan-500/40 rounded p-5 shadow-lg flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded bg-cyan-950 border border-cyan-500/60 flex items-center justify-center text-cyan-400 font-mono font-black text-lg shadow-inner shrink-0">
                    P{{ $season['player_driver_rank'] }}
                </div>
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-cyan-400">YOUR LEAD DRIVER STANDING</span>
                    <h2 class="text-lg font-black text-white font-mono uppercase tracking-tight mt-0.5">
                        {{ $team->primaryDriver() ? $team->primaryDriver()->name : 'Lead Driver' }}
                    </h2>
                    <div class="text-xs font-mono text-zinc-400 mt-0.5">
                        <span class="text-cyan-300 font-bold">{{ $season['player_driver_points'] }} PTS</span> &bull; Gap: <span class="text-zinc-300 font-semibold">{{ $season['driver_leader_gap'] }}</span>
                    </div>
                </div>
            </div>
            <div class="hidden sm:block text-right font-mono text-xs text-zinc-400">
                <div class="text-[10px] text-zinc-500 uppercase">Season Leader</div>
                <div class="font-bold text-white mt-0.5">{{ $season['leader_driver_name'] }}</div>
                <div class="text-cyan-300 font-bold">{{ $season['leader_driver_points'] }} PTS</div>
            </div>
        </div>
    </div>

    <!-- Interactive Championship Standings Section -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg space-y-6">
        <!-- Standings Tab Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-zinc-800">
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    id="tabBtnConstructors"
                    onclick="switchStandingsTab('constructors')"
                    class="px-4 py-2 rounded text-xs font-mono uppercase tracking-wider transition cursor-pointer flex items-center gap-2 bg-orange-600 text-white font-bold shadow"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span>Constructors' Championship</span>
                </button>

                <button
                    type="button"
                    id="tabBtnDrivers"
                    onclick="switchStandingsTab('drivers')"
                    class="px-4 py-2 rounded text-xs font-mono uppercase tracking-wider transition cursor-pointer flex items-center gap-2 bg-zinc-950 text-zinc-400 hover:text-white border border-zinc-800"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>Drivers' Championship</span>
                </button>
            </div>

            <div class="text-xs font-mono text-zinc-400 flex items-center gap-3">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                    <span>Player Constructor</span>
                </span>
                <span class="text-zinc-600">&bull;</span>
                <span>FIA Scoring: 25-18-15-12-10-8-6-4-2-1 (+1 FL)</span>
            </div>
        </div>

        <!-- 1. Constructors' Championship Table -->
        <div id="constructorsTable" class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-zinc-950 text-zinc-500 uppercase text-[11px] border-b border-zinc-800">
                    <tr>
                        <th class="py-3 px-3">Pos</th>
                        <th class="py-3 px-3">Constructor / Chassis</th>
                        <th class="py-3 px-3">Lead Driver</th>
                        <th class="py-3 px-3 text-center">Races</th>
                        <th class="py-3 px-3 text-center">Wins (P1)</th>
                        <th class="py-3 px-3 text-center">Podiums</th>
                        <th class="py-3 px-3 text-center">Best</th>
                        <th class="py-3 px-3 text-right">Points</th>
                        <th class="py-3 px-3 text-right">Gap</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @foreach($constructorsStandings as $c)
                        <tr class="{{ $c['is_player'] ? 'bg-orange-950/30 border-l-2 border-l-orange-500 font-bold' : 'hover:bg-zinc-800/30' }} transition-colors">
                            <td class="py-3 px-3">
                                @if($c['rank'] === 1)
                                    <span class="w-6 h-6 rounded bg-amber-400 text-black font-black text-xs flex items-center justify-center shadow-sm">1</span>
                                @elseif($c['rank'] === 2)
                                    <span class="w-6 h-6 rounded bg-zinc-300 text-black font-black text-xs flex items-center justify-center">2</span>
                                @elseif($c['rank'] === 3)
                                    <span class="w-6 h-6 rounded bg-amber-700 text-white font-black text-xs flex items-center justify-center">3</span>
                                @else
                                    <span class="text-zinc-400 font-bold pl-1.5">{{ $c['rank'] }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-white font-bold text-sm">{{ $c['team_name'] }}</span>
                                    @if($c['is_player'])
                                        <span class="text-[9px] bg-orange-600 text-white px-1.5 py-0.2 rounded font-black tracking-tighter">YOU</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-zinc-500 uppercase">{{ $c['car_name'] }}</div>
                            </td>
                            <td class="py-3 px-3 text-zinc-300">
                                {{ $c['lead_driver'] }}
                            </td>
                            <td class="py-3 px-3 text-center text-zinc-400">
                                {{ $c['races_entered'] }}
                            </td>
                            <td class="py-3 px-3 text-center font-bold {{ $c['wins'] > 0 ? 'text-amber-400' : 'text-zinc-600' }}">
                                {{ $c['wins'] }}
                            </td>
                            <td class="py-3 px-3 text-center font-bold {{ $c['podiums'] > 0 ? 'text-emerald-400' : 'text-zinc-600' }}">
                                {{ $c['podiums'] }}
                            </td>
                            <td class="py-3 px-3 text-center text-zinc-300 font-semibold">
                                {{ $c['best_finish'] }}
                            </td>
                            <td class="py-3 px-3 text-right font-black text-sm text-amber-400">
                                {{ $c['points'] }} <span class="text-[10px] text-zinc-500 font-normal">PTS</span>
                            </td>
                            <td class="py-3 px-3 text-right font-semibold {{ $c['gap'] === 'LEADER' ? 'text-emerald-400' : 'text-zinc-400' }}">
                                {{ $c['gap'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- 2. Drivers' Championship Table -->
        <div id="driversTable" class="overflow-x-auto hidden">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-zinc-950 text-zinc-500 uppercase text-[11px] border-b border-zinc-800">
                    <tr>
                        <th class="py-3 px-3">Pos</th>
                        <th class="py-3 px-3">Driver Name</th>
                        <th class="py-3 px-3">Team / Constructor</th>
                        <th class="py-3 px-3 text-center">Races</th>
                        <th class="py-3 px-3 text-center">Wins (P1)</th>
                        <th class="py-3 px-3 text-center">Podiums</th>
                        <th class="py-3 px-3 text-center">Fastest Laps</th>
                        <th class="py-3 px-3 text-center">Best</th>
                        <th class="py-3 px-3 text-right">Points</th>
                        <th class="py-3 px-3 text-right">Gap</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @foreach($driversStandings as $d)
                        <tr class="{{ $d['is_player'] ? 'bg-cyan-950/30 border-l-2 border-l-cyan-500 font-bold' : 'hover:bg-zinc-800/30' }} transition-colors">
                            <td class="py-3 px-3">
                                @if($d['rank'] === 1)
                                    <span class="w-6 h-6 rounded bg-amber-400 text-black font-black text-xs flex items-center justify-center shadow-sm">1</span>
                                @elseif($d['rank'] === 2)
                                    <span class="w-6 h-6 rounded bg-zinc-300 text-black font-black text-xs flex items-center justify-center">2</span>
                                @elseif($d['rank'] === 3)
                                    <span class="w-6 h-6 rounded bg-amber-700 text-white font-black text-xs flex items-center justify-center">3</span>
                                @else
                                    <span class="text-zinc-400 font-bold pl-1.5">{{ $d['rank'] }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-white font-bold text-sm">{{ $d['driver_name'] }}</span>
                                    @if($d['is_player'])
                                        <span class="text-[9px] bg-cyan-600 text-white px-1.5 py-0.2 rounded font-black tracking-tighter">YOU</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-zinc-500 uppercase">{{ $d['car_name'] }}</div>
                            </td>
                            <td class="py-3 px-3 text-zinc-300">
                                {{ $d['team_name'] }}
                            </td>
                            <td class="py-3 px-3 text-center text-zinc-400">
                                {{ $d['races_entered'] }}
                            </td>
                            <td class="py-3 px-3 text-center font-bold {{ $d['wins'] > 0 ? 'text-amber-400' : 'text-zinc-600' }}">
                                {{ $d['wins'] }}
                            </td>
                            <td class="py-3 px-3 text-center font-bold {{ $d['podiums'] > 0 ? 'text-emerald-400' : 'text-zinc-600' }}">
                                {{ $d['podiums'] }}
                            </td>
                            <td class="py-3 px-3 text-center font-bold {{ $d['fastest_laps'] > 0 ? 'text-purple-400' : 'text-zinc-600' }}">
                                {{ $d['fastest_laps'] }}
                            </td>
                            <td class="py-3 px-3 text-center text-zinc-300 font-semibold">
                                {{ $d['best_finish'] }}
                            </td>
                            <td class="py-3 px-3 text-right font-black text-sm text-cyan-300">
                                {{ $d['points'] }} <span class="text-[10px] text-zinc-500 font-normal">PTS</span>
                            </td>
                            <td class="py-3 px-3 text-right font-semibold {{ $d['gap'] === 'LEADER' ? 'text-emerald-400' : 'text-zinc-400' }}">
                                {{ $d['gap'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Navigation Bar -->
    <div class="flex flex-wrap items-center justify-between gap-3 pt-2 font-mono text-xs">
        <a href="{{ route('races.index') }}" class="px-4 py-2.5 rounded bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white transition flex items-center gap-2">
            <span>&rarr; Enter Next Grand Prix</span>
        </a>
        <a href="{{ route('races.history') }}" class="px-4 py-2.5 rounded bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white transition flex items-center gap-2">
            <span>&rarr; View Race History Archives</span>
        </a>
        <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded bg-zinc-950 hover:bg-zinc-900 border border-zinc-800 text-orange-400 hover:text-orange-300 transition flex items-center gap-2">
            <span>&larr; Back to Paddock Dashboard</span>
        </a>
    </div>
</div>

<script>
    function switchStandingsTab(tab) {
        const constructorsBtn = document.getElementById('tabBtnConstructors');
        const driversBtn = document.getElementById('tabBtnDrivers');
        const constructorsTable = document.getElementById('constructorsTable');
        const driversTable = document.getElementById('driversTable');

        if (!constructorsBtn || !driversBtn || !constructorsTable || !driversTable) return;

        if (tab === 'drivers') {
            // Activate Drivers tab
            driversBtn.className = 'px-4 py-2 rounded text-xs font-mono uppercase tracking-wider transition cursor-pointer flex items-center gap-2 bg-cyan-600 text-white font-bold shadow';
            constructorsBtn.className = 'px-4 py-2 rounded text-xs font-mono uppercase tracking-wider transition cursor-pointer flex items-center gap-2 bg-zinc-950 text-zinc-400 hover:text-white border border-zinc-800';

            constructorsTable.classList.add('hidden');
            driversTable.classList.remove('hidden');
        } else {
            // Activate Constructors tab
            constructorsBtn.className = 'px-4 py-2 rounded text-xs font-mono uppercase tracking-wider transition cursor-pointer flex items-center gap-2 bg-orange-600 text-white font-bold shadow';
            driversBtn.className = 'px-4 py-2 rounded text-xs font-mono uppercase tracking-wider transition cursor-pointer flex items-center gap-2 bg-zinc-950 text-zinc-400 hover:text-white border border-zinc-800';

            driversTable.classList.add('hidden');
            constructorsTable.classList.remove('hidden');
        }
    }
</script>
@endsection
