@extends('layouts.app')

@section('title', 'Race Archives & Performance History - ' . $team->name)

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
                    <a href="{{ route('races.index') }}" class="hover:text-red-400 transition-colors uppercase">GRAND PRIX</a>
                    <span>/</span>
                    <span class="text-red-400 font-bold uppercase">RACE ARCHIVES</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight">
                        Championship Race Archives
                    </h1>
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded bg-zinc-800 text-zinc-300 border border-zinc-700">
                        {{ $stats['total_races'] }} SESSIONS LOGGED
                    </span>
                </div>
                <p class="text-xs text-zinc-400 mt-1 font-mono">
                    Historical telemetry logs, official FIA results, debrief telemetry, and career constructor earnings.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('races.index') }}" class="px-4 py-2.5 rounded bg-orange-600 hover:bg-orange-500 text-white text-xs font-mono font-bold uppercase transition flex items-center gap-1.5 shadow-md">
                    <span>Race Calendar &rarr;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Performance Metrics Ribbon (KPI Deck) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Total Races -->
        <div class="bg-zinc-900/90 border border-zinc-800 rounded p-4">
            <div class="text-[10px] font-mono text-zinc-400 uppercase tracking-wider">STARTS</div>
            <div class="text-2xl font-mono font-black text-white mt-0.5">
                {{ $stats['total_races'] }}
            </div>
            <div class="text-[10px] font-mono text-zinc-500 mt-1">Official GP Entries</div>
        </div>

        <!-- Victories / Wins (P1) -->
        <div class="bg-zinc-900/90 border border-zinc-800 rounded p-4">
            <div class="text-[10px] font-mono text-amber-400 uppercase tracking-wider font-bold">VICTORIES (P1)</div>
            <div class="text-2xl font-mono font-black text-amber-400 mt-0.5">
                {{ $stats['total_wins'] }}
            </div>
            <div class="text-[10px] font-mono text-zinc-400 mt-1">
                Win Rate: <span class="text-white font-bold">{{ $stats['win_rate'] }}%</span>
            </div>
        </div>

        <!-- Podiums (P1 - P3) -->
        <div class="bg-zinc-900/90 border border-zinc-800 rounded p-4">
            <div class="text-[10px] font-mono text-cyan-400 uppercase tracking-wider font-bold">PODIUMS (P1-P3)</div>
            <div class="text-2xl font-mono font-black text-cyan-400 mt-0.5">
                {{ $stats['total_podiums'] }}
            </div>
            <div class="text-[10px] font-mono text-zinc-400 mt-1">
                Podium: <span class="text-white font-bold">{{ $stats['podium_rate'] }}%</span>
            </div>
        </div>

        <!-- Championship Points -->
        <div class="bg-zinc-900/90 border border-zinc-800 rounded p-4">
            <div class="text-[10px] font-mono text-purple-400 uppercase tracking-wider font-bold">TOTAL POINTS</div>
            <div class="text-2xl font-mono font-black text-purple-400 mt-0.5">
                {{ number_format($stats['total_points']) }}
            </div>
            <div class="text-[10px] font-mono text-zinc-500 mt-1">FIA Scoring Scale</div>
        </div>

        <!-- Total Prize Money -->
        <div class="bg-zinc-900/90 border border-zinc-800 rounded p-4">
            <div class="text-[10px] font-mono text-emerald-400 uppercase tracking-wider font-bold">PRIZE EARNINGS</div>
            <div class="text-2xl font-mono font-black text-emerald-400 mt-0.5">
                {{ number_format($stats['total_earnings']) }} <span class="text-xs text-zinc-500 font-normal">CR</span>
            </div>
            <div class="text-[10px] font-mono text-zinc-500 mt-1">Gross Prize Payouts</div>
        </div>

        <!-- Best Finish -->
        <div class="bg-zinc-900/90 border border-zinc-800 rounded p-4">
            <div class="text-[10px] font-mono text-zinc-400 uppercase tracking-wider">BEST FINISH</div>
            <div class="text-2xl font-mono font-black {{ $stats['best_finish'] === 1 ? 'text-amber-400' : 'text-white' }} mt-0.5">
                {{ $stats['best_finish'] ? 'P' . $stats['best_finish'] : '--' }}
            </div>
            <div class="text-[10px] font-mono text-zinc-400 mt-1">
                +{{ number_format($stats['total_reputation']) }} REP Gained
            </div>
        </div>
    </div>

    <!-- Historical Race Logs Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded shadow-lg overflow-hidden">
        <div class="p-5 border-b border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-zinc-950/50">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded bg-red-500"></span>
                <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Grand Prix Race Ledger & Telemetry Archives</h2>
            </div>
            <div class="text-xs font-mono text-zinc-400">
                Displaying {{ $results->firstItem() ?? 0 }} - {{ $results->lastItem() ?? 0 }} of {{ $results->total() }} record(s)
            </div>
        </div>

        @if($results->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-zinc-950 text-zinc-400 uppercase border-b border-zinc-800 text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Event / Circuit</th>
                            <th class="py-3 px-4">Chassis & Driver</th>
                            <th class="py-3 px-4 text-center">Finish Pos</th>
                            <th class="py-3 px-4">Race Time / Pace</th>
                            <th class="py-3 px-4">Prize & Rep</th>
                            <th class="py-3 px-4 text-right">Debrief Telemetry</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60">
                        @foreach($results as $res)
                            <tr class="hover:bg-zinc-800/40 transition-colors">
                                <!-- Circuit & Date -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-white text-sm uppercase">
                                            {{ $res->race->name }}
                                        </span>
                                        <span class="text-[9px] font-mono font-bold bg-orange-950/80 text-orange-400 border border-orange-500/40 px-1.5 py-0.5 rounded">
                                            SEASON {{ $res->season ?? 1 }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px] text-zinc-400 mt-1">
                                        <span>{{ $res->race->country }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $res->created_at->format('M d, Y H:i') }}</span>
                                        <span>&bull;</span>
                                        <span class="uppercase text-[10px] px-1.5 py-0.2 rounded {{ $res->race->weather === 'wet' ? 'bg-cyan-950 text-cyan-300 border border-cyan-800' : 'bg-amber-950 text-amber-300 border border-amber-800' }}">
                                            {{ $res->race->weather }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Chassis & Driver -->
                                <td class="py-4 px-4">
                                    <div class="text-zinc-200 font-bold">
                                        {{ $res->driver->name }}
                                    </div>
                                    <div class="text-[11px] text-zinc-400 mt-0.5">
                                        {{ $res->car->name }} <span class="text-amber-400 text-[10px]">Tier {{ $res->car->level }}</span>
                                    </div>
                                </td>

                                <!-- Finish Position Badge -->
                                <td class="py-4 px-4 text-center">
                                    @if($res->position === 1)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded bg-amber-950 border border-amber-500/60 text-amber-300 font-black text-xs shadow-sm">
                                            <span>&#127942;</span>
                                            <span>P1 WINNER</span>
                                        </span>
                                    @elseif($res->position === 2)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-slate-900 border border-slate-400/60 text-slate-200 font-bold text-xs">
                                            <span>&#129352;</span>
                                            <span>P2 PODIUM</span>
                                        </span>
                                    @elseif($res->position === 3)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-orange-950 border border-orange-700/60 text-orange-300 font-bold text-xs">
                                            <span>&#129353;</span>
                                            <span>P3 PODIUM</span>
                                        </span>
                                    @elseif($res->status === 'dnf')
                                        <span class="inline-block px-2.5 py-1 rounded bg-red-950 border border-red-600/60 text-red-400 font-bold text-xs">
                                            DNF
                                        </span>
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded bg-zinc-800 border border-zinc-700 text-zinc-300 font-bold text-xs">
                                            P{{ $res->position }}
                                            @if($res->points > 0)
                                                <span class="text-purple-400 text-[10px] ml-1">(+{{ $res->points }} PTS)</span>
                                            @endif
                                        </span>
                                    @endif
                                </td>

                                <!-- Race Time / Strategy -->
                                <td class="py-4 px-4">
                                    <div class="font-mono text-zinc-300">
                                        {{ $res->race_time }}
                                    </div>
                                    <div class="text-[10px] font-mono text-zinc-500 uppercase mt-0.5">
                                        Strategy: <span class="text-zinc-400">{{ $res->strategy }}</span>
                                    </div>
                                </td>

                                <!-- Prize & Reputation -->
                                <td class="py-4 px-4">
                                    <div class="font-mono font-bold text-emerald-400">
                                        +{{ number_format($res->prize_money) }} <span class="text-[10px] text-zinc-500">CR</span>
                                    </div>
                                    <div class="text-[10px] font-mono text-cyan-400 mt-0.5">
                                        +{{ $res->reputation_earned }} REP
                                    </div>
                                </td>

                                <!-- Debrief Action -->
                                <td class="py-4 px-4 text-right">
                                    <a href="{{ route('race-results.show', $res) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded bg-zinc-800 hover:bg-zinc-700 hover:text-white text-zinc-300 border border-zinc-700 text-xs font-mono font-bold uppercase transition">
                                        <span>View Debrief</span>
                                        <span>&rarr;</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            @if($results->hasPages())
                <div class="p-4 border-t border-zinc-800 bg-zinc-950/60">
                    {{ $results->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="p-12 text-center">
                <div class="w-14 h-14 rounded-full bg-zinc-950 border border-zinc-800 mx-auto flex items-center justify-center text-zinc-600 text-2xl mb-4 font-mono">
                    &#127937;
                </div>
                <h3 class="text-base font-mono font-bold text-white uppercase tracking-wide">
                    No Championship Telemetry Recorded
                </h3>
                <p class="text-xs font-mono text-zinc-400 mt-1 max-w-md mx-auto">
                    Your team has not competed in any Grand Prix championship races yet. Enter an upcoming round from the race calendar to start logging official results.
                </p>
                <div class="mt-6">
                    <a href="{{ route('races.index') }}" class="px-5 py-2.5 rounded bg-red-600 hover:bg-red-500 text-white text-xs font-mono font-bold uppercase tracking-wider transition inline-flex items-center gap-2 shadow-lg">
                        <span>Enter Next Grand Prix</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
