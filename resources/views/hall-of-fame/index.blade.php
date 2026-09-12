@extends('layouts.app')

@section('title', 'Trophy Cabinet & Team Hall of Fame - ' . $team->name)

@section('content')
<div class="space-y-8">
    <!-- Hero Header / Trophy Showcase Title Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6 sm:p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-500 via-yellow-400 to-orange-500"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2 text-xs font-mono text-zinc-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-amber-400 transition-colors">PADDOCK</a>
                    <span>/</span>
                    <a href="{{ route('standings') }}" class="hover:text-amber-400 transition-colors uppercase">STANDINGS</a>
                    <span>/</span>
                    <span class="text-amber-400 font-bold uppercase">TROPHY CABINET & HALL OF FAME</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-950 border border-amber-500/50 flex items-center justify-center text-xl shadow-lg shadow-amber-500/20 shrink-0">
                        🏆
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-4xl font-black text-white uppercase font-mono tracking-tight">
                            Trophy Room & Hall of Fame
                        </h1>
                        <p class="text-xs font-mono text-zinc-400 mt-1">
                            Permanent constructor honors, world championship titles, victory trophies, and career telemetry milestones.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Milestone Unlocks Badge -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="bg-zinc-950/90 border border-amber-500/40 rounded-lg px-4 py-3 text-right font-mono shadow-inner">
                    <div class="text-[10px] text-zinc-400 uppercase tracking-wider">ACCOLADES UNLOCKED</div>
                    <div class="text-2xl font-black text-amber-400">
                        {{ $stats['unlocked_milestones'] }} <span class="text-xs text-zinc-500">/ {{ $stats['total_milestones'] }}</span>
                    </div>
                </div>
                <a href="{{ route('standings') }}" class="px-4 py-3 rounded-lg bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 text-xs font-mono font-bold uppercase transition flex items-center gap-1.5 shadow">
                    <span>&larr; Standings</span>
                </a>
            </div>
        </div>
    </div>

    <!-- All-Time Career Statistics Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 font-mono">
        <!-- 1. Total Races -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4 shadow">
            <div class="text-[10px] uppercase text-zinc-500">All-Time Entries</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($stats['total_races']) }}</div>
            <div class="text-[10px] text-zinc-400 mt-0.5">Grand Prix Starts</div>
        </div>

        <!-- 2. Victories -->
        <div class="bg-zinc-900 border border-amber-500/30 rounded-lg p-4 shadow">
            <div class="text-[10px] uppercase text-amber-400 font-bold">P1 Victories</div>
            <div class="text-2xl font-black text-amber-400 mt-1">{{ number_format($stats['total_wins']) }}</div>
            <div class="text-[10px] text-amber-300/70 mt-0.5">Win Rate: {{ $stats['win_rate'] }}%</div>
        </div>

        <!-- 3. Podiums -->
        <div class="bg-zinc-900 border border-cyan-500/30 rounded-lg p-4 shadow">
            <div class="text-[10px] uppercase text-cyan-400 font-bold">Total Podiums</div>
            <div class="text-2xl font-black text-cyan-300 mt-1">{{ number_format($stats['total_podiums']) }}</div>
            <div class="text-[10px] text-cyan-300/70 mt-0.5">Podium Rate: {{ $stats['podium_rate'] }}%</div>
        </div>

        <!-- 4. Total Points -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4 shadow">
            <div class="text-[10px] uppercase text-zinc-500">Career FIA Points</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($stats['total_points']) }}</div>
            <div class="text-[10px] text-zinc-400 mt-0.5">Championship PTS</div>
        </div>

        <!-- 5. Total Prize Money -->
        <div class="bg-zinc-900 border border-emerald-500/30 rounded-lg p-4 shadow">
            <div class="text-[10px] uppercase text-emerald-400 font-bold">Prize Money</div>
            <div class="text-xl font-black text-emerald-400 mt-1">+{{ number_format($stats['total_prize_money']) }}</div>
            <div class="text-[10px] text-emerald-300/70 mt-0.5">Grand Prix Rewards</div>
        </div>

        <!-- 6. Total Career Income -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4 shadow">
            <div class="text-[10px] uppercase text-zinc-500">Gross Income</div>
            <div class="text-xl font-black text-amber-300 mt-1">{{ number_format($stats['total_career_earnings']) }}</div>
            <div class="text-[10px] text-zinc-400 mt-0.5">Treasury Inflows</div>
        </div>
    </div>

    <!-- Section 1: World Championship Titles (WCC & WDC) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-800 font-mono">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                <h2 class="text-sm font-bold uppercase tracking-wider text-white">World Championship Titles</h2>
            </div>
            <span class="text-xs text-zinc-400">{{ count($wccTitles) + count($wdcTitles) }} Crown(s) Claimed</span>
        </div>

        @if(count($wccTitles) === 0 && count($wdcTitles) === 0)
            <div class="bg-zinc-900/60 border border-zinc-800 border-dashed rounded-lg p-8 text-center font-mono">
                <div class="text-3xl mb-2">👑</div>
                <h3 class="text-sm font-bold text-white uppercase">No World Championships Claimed Yet</h3>
                <p class="text-xs text-zinc-400 mt-1 max-w-md mx-auto">
                    Finish P1 in the World Constructors Championship (WCC) or World Drivers Championship (WDC) standings at the end of a season to claim championship silverware.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <!-- WCC Titles -->
                @foreach($wccTitles as $wcc)
                    <div class="bg-gradient-to-b from-amber-950/40 via-zinc-900 to-zinc-950 border border-amber-500/60 rounded-xl p-5 shadow-xl relative overflow-hidden font-mono">
                        <div class="absolute -right-6 -bottom-6 text-7xl opacity-10 pointer-events-none">👑</div>
                        <div class="flex items-start justify-between mb-3">
                            <span class="text-[10px] font-black uppercase text-amber-400 bg-amber-950 border border-amber-500/40 px-2 py-0.5 rounded tracking-wider">
                                SEASON {{ $wcc['season'] }} WORLD TITLE
                            </span>
                            <span class="text-2xl">🏆</span>
                        </div>
                        <h3 class="text-lg font-black text-white uppercase">World Constructors Champion</h3>
                        <p class="text-xs text-zinc-300 mt-1">
                            Team: <strong class="text-amber-400">{{ $wcc['team_name'] }}</strong> &bull; {{ $wcc['points'] }} Points &bull; {{ $wcc['wins'] }} Wins
                        </p>
                    </div>
                @endforeach

                <!-- WDC Titles -->
                @foreach($wdcTitles as $wdc)
                    <div class="bg-gradient-to-b from-cyan-950/40 via-zinc-900 to-zinc-950 border border-cyan-500/60 rounded-xl p-5 shadow-xl relative overflow-hidden font-mono">
                        <div class="absolute -right-6 -bottom-6 text-7xl opacity-10 pointer-events-none">🏎️</div>
                        <div class="flex items-start justify-between mb-3">
                            <span class="text-[10px] font-black uppercase text-cyan-400 bg-cyan-950 border border-cyan-500/40 px-2 py-0.5 rounded tracking-wider">
                                SEASON {{ $wdc['season'] }} DRIVERS TITLE
                            </span>
                            <span class="text-2xl">🥇</span>
                        </div>
                        <h3 class="text-lg font-black text-white uppercase">World Drivers Champion</h3>
                        <p class="text-xs text-zinc-300 mt-1">
                            Pilot: <strong class="text-cyan-300">{{ $wdc['driver_name'] }}</strong> &bull; {{ $wdc['points'] }} Points &bull; {{ $wdc['wins'] }} Wins
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Section 2: Grand Prix Victory Trophies -->
    <div class="space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-800 font-mono">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                <h2 class="text-sm font-bold uppercase tracking-wider text-white">Grand Prix Victory Silverware</h2>
            </div>
            <span class="text-xs text-zinc-400">{{ count($gpVictories) }} Grand Prix Win(s)</span>
        </div>

        @if(count($gpVictories) === 0)
            <div class="bg-zinc-900/60 border border-zinc-800 border-dashed rounded-lg p-8 text-center font-mono">
                <div class="text-3xl mb-2">🏁</div>
                <h3 class="text-sm font-bold text-white uppercase">Trophy Cabinet Empty</h3>
                <p class="text-xs text-zinc-400 mt-1 max-w-md mx-auto">
                    Win a Grand Prix round to claim your first commemorative race trophy here in the Hall of Fame.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($gpVictories as $trophy)
                    <div class="bg-zinc-900 border border-amber-500/40 hover:border-amber-400/80 rounded-lg p-4 shadow-lg transition-all font-mono relative overflow-hidden group">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[9px] font-black uppercase text-amber-400 bg-amber-950/80 border border-amber-500/30 px-2 py-0.5 rounded">
                                S{{ $trophy['season'] }} &bull; P1 VICTORY
                            </span>
                            <span class="text-lg group-hover:scale-125 transition-transform">🏆</span>
                        </div>
                        <h4 class="text-base font-black text-white uppercase tracking-tight">{{ $trophy['race_name'] }}</h4>
                        <div class="text-[11px] text-zinc-400 mb-1">📍 {{ $trophy['location'] }}</div>
                        <div class="text-xs text-zinc-400 mt-1 space-y-0.5 border-t border-zinc-800/80 pt-1.5">
                            <div>Pilot: <strong class="text-zinc-200">{{ $trophy['driver_name'] }}</strong></div>
                            <div>Chassis: <strong class="text-zinc-300">{{ $trophy['car_name'] }}</strong> (Slot #{{ $trophy['car_slot'] }})</div>
                            <div class="text-emerald-400 font-bold mt-1">+{{ number_format($trophy['prize_money']) }} CR Prize</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Section 3: Career Milestones & Accolades Locker -->
    <div class="space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-800 font-mono">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-400"></span>
                <h2 class="text-sm font-bold uppercase tracking-wider text-white">Career Milestones & Accolades</h2>
            </div>
            <span class="text-xs text-zinc-400">{{ $stats['unlocked_milestones'] }} / {{ $stats['total_milestones'] }} Completed</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($milestones as $badge)
                <div class="bg-zinc-900 border {{ $badge['unlocked'] ? 'border-amber-500/50 bg-amber-950/10 shadow-lg shadow-amber-950/20' : 'border-zinc-800 opacity-60' }} rounded-lg p-5 flex flex-col justify-between font-mono relative overflow-hidden">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-lg {{ $badge['unlocked'] ? 'bg-amber-950 border border-amber-500/50 text-amber-300' : 'bg-zinc-950 border border-zinc-800 text-zinc-600' }} flex items-center justify-center text-xl shadow">
                                {{ $badge['icon'] }}
                            </div>
                            @if($badge['unlocked'])
                                <span class="text-[9px] font-black uppercase text-emerald-400 bg-emerald-950 border border-emerald-500/40 px-2 py-0.5 rounded">
                                    ✓ UNLOCKED
                                </span>
                            @else
                                <span class="text-[9px] font-bold uppercase text-zinc-500 bg-zinc-950 px-2 py-0.5 rounded border border-zinc-800">
                                    LOCKED
                                </span>
                            @endif
                        </div>

                        <h4 class="text-sm font-black uppercase {{ $badge['unlocked'] ? 'text-white' : 'text-zinc-400' }}">{{ $badge['name'] }}</h4>
                        <p class="text-xs text-zinc-400 mt-1 leading-relaxed">{{ $badge['description'] }}</p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-zinc-800/80 text-[10px] text-zinc-400 flex items-center justify-between">
                        <span>Progress:</span>
                        <strong class="{{ $badge['unlocked'] ? 'text-amber-400 font-black' : 'text-zinc-500' }}">{{ $badge['progress'] }}</strong>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
