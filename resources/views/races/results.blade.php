@extends('layouts.app')

@section('title', 'Official Race Debrief & Financial Ledger - ' . $race->name)

@section('content')
<div class="space-y-6">
    <!-- Header / Breadcrumb Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 via-amber-400 to-cyan-500"></div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1 text-xs font-mono text-zinc-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-amber-400 transition-colors">PADDOCK</a>
                    <span>/</span>
                    <a href="{{ route('races.index') }}" class="hover:text-amber-400 transition-colors uppercase">CALENDAR</a>
                    <span>/</span>
                    <span class="text-amber-400 font-bold uppercase">RACE DEBRIEF & FINANCIAL SETTLEMENT</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight">
                    {{ $race->name }} &bull; Debrief & Financial Ledger
                </h1>
                <p class="text-xs text-zinc-400 mt-1 font-mono">
                    Official race classification, economic settlement, prize payout ledger, and championship points.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('races.live', $race) }}" class="px-4 py-2 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 text-xs font-mono font-bold uppercase transition flex items-center gap-1.5">
                    <span>&larr; View Live Telemetry Replay</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Outcome & Classification Banner -->
    <div class="bg-gradient-to-r from-zinc-900 via-zinc-900/90 to-zinc-950 border {{ $result->position === 1 ? 'border-amber-500/60 shadow-amber-500/10' : ($result->position <= 3 ? 'border-emerald-500/60' : 'border-orange-500/40') }} rounded p-6 shadow-xl relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded bg-zinc-950 border {{ $result->position === 1 ? 'border-amber-400 text-amber-400' : ($result->position <= 3 ? 'border-emerald-400 text-emerald-400' : 'border-orange-400 text-orange-400') }} flex flex-col items-center justify-center font-mono font-black shadow-inner shrink-0">
                    <span class="text-2xl leading-none">P{{ $result->position }}</span>
                    <span class="text-[9px] uppercase tracking-wider text-zinc-400">FINISH</span>
                </div>

                <div>
                    <div class="flex items-center gap-2">
                        @if($result->position === 1)
                            <span class="text-[10px] font-mono font-black uppercase tracking-widest bg-amber-950 border border-amber-500/50 text-amber-400 px-2.5 py-0.5 rounded animate-pulse">
                                🏆 GRAND PRIX CHAMPION // 1ST PLACE VICTORY
                            </span>
                        @elseif($result->position <= 3)
                            <span class="text-[10px] font-mono font-black uppercase tracking-widest bg-emerald-950 border border-emerald-500/50 text-emerald-400 px-2.5 py-0.5 rounded">
                                🏁 OFFICIAL PODIUM FINISH // P{{ $result->position }}
                            </span>
                        @else
                            <span class="text-[10px] font-mono font-bold uppercase tracking-widest bg-zinc-800 text-zinc-300 px-2.5 py-0.5 rounded">
                                OFFICIAL CLASSIFICATION // P{{ $result->position }}
                            </span>
                        @endif
                    </div>

                    <h2 class="text-xl font-black text-white uppercase font-mono mt-1">
                        {{ $team->name }} &bull; {{ $result->driver->name ?? 'Lead Driver' }}
                    </h2>
                    <p class="text-xs font-mono text-zinc-400 mt-0.5">
                        Vehicle: <span class="text-zinc-200 font-bold">{{ $result->car->name ?? 'Race Chassis' }}</span> &bull; Race Time: <span class="text-white font-bold">{{ $result->race_time }}</span> &bull; Scrutineering Status: <span class="text-emerald-400 font-semibold uppercase">{{ $result->status }}</span>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="bg-zinc-950/80 border border-zinc-800 rounded px-4 py-2.5 text-right font-mono">
                    <div class="text-[10px] text-zinc-500 uppercase">Prize Earned</div>
                    <div class="text-base font-black text-amber-400">+{{ number_format($result->prize_money) }} CR</div>
                </div>

                <div class="bg-zinc-950/80 border border-zinc-800 rounded px-4 py-2.5 text-right font-mono">
                    <div class="text-[10px] text-zinc-500 uppercase">Reputation Added</div>
                    <div class="text-base font-black text-cyan-400">+{{ number_format($result->reputation_earned) }} PTS</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Settlement & Economy Ledger Deck -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Prize Payout -->
        <div class="bg-zinc-900 border border-zinc-800 rounded p-5 shadow">
            <div class="text-[10px] font-mono uppercase text-zinc-500">Gross Prize Payout</div>
            <div class="text-xl font-mono font-black text-emerald-400 mt-1">
                +{{ number_format($result->prize_money) }} <span class="text-xs text-zinc-500 font-normal">CR</span>
            </div>
            <div class="text-[10px] font-mono text-zinc-400 mt-1">Position P{{ $result->position }} Prize Allocation</div>
        </div>

        <!-- Entry Fee Deducted -->
        <div class="bg-zinc-900 border border-zinc-800 rounded p-5 shadow">
            <div class="text-[10px] font-mono uppercase text-zinc-500">Circuit Entry Fee</div>
            <div class="text-xl font-mono font-black text-red-400 mt-1">
                -{{ number_format($race->entry_fee) }} <span class="text-xs text-zinc-500 font-normal">CR</span>
            </div>
            <div class="text-[10px] font-mono text-zinc-400 mt-1">Settled at race start</div>
        </div>

        <!-- Net Financial Profit/Loss -->
        <div class="bg-zinc-900 border border-zinc-800 rounded p-5 shadow">
            <div class="text-[10px] font-mono uppercase text-zinc-500">Net Race Profit / Loss</div>
            <div class="text-xl font-mono font-black {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-red-400' }} mt-1">
                {{ $netProfit >= 0 ? '+' : '' }}{{ number_format($netProfit) }} <span class="text-xs text-zinc-500 font-normal">CR</span>
            </div>
            <div class="text-[10px] font-mono text-zinc-400 mt-1">Net constructor balance delta</div>
        </div>

        <!-- Current Treasury Balance -->
        <div class="bg-zinc-900 border border-zinc-800 rounded p-5 shadow">
            <div class="text-[10px] font-mono uppercase text-zinc-500">Current Team Treasury</div>
            <div class="text-xl font-mono font-black text-amber-400 mt-1">
                {{ number_format($team->money) }} <span class="text-xs text-zinc-500 font-normal">CR</span>
            </div>
            <div class="text-[10px] font-mono text-zinc-400 mt-1">Total: {{ $team->reputation }} Reputation PTS</div>
        </div>
    </div>

    <!-- Official 10-Driver Classification Table & Settlement Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Classification Grid -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded bg-amber-500"></span>
                        <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Official Grand Prix Results Table</h2>
                    </div>
                    <span class="text-xs font-mono text-zinc-400">10 Classified Finishers</span>
                </div>

                @if(isset($simulation['standings']))
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs font-mono">
                            <thead class="text-[10px] text-zinc-500 uppercase border-b border-zinc-800">
                                <tr>
                                    <th class="py-2.5 px-3">Pos</th>
                                    <th class="py-2.5 px-3">Driver / Constructor</th>
                                    <th class="py-2.5 px-3">Chassis</th>
                                    <th class="py-2.5 px-3 text-right">Race Time</th>
                                    <th class="py-2.5 px-3 text-right">Interval</th>
                                    <th class="py-2.5 px-3 text-right">Fastest Lap</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/60">
                                @foreach($simulation['standings'] as $driver)
                                    <tr class="{{ $driver['is_player'] ? 'bg-orange-950/40 border-l-2 border-l-orange-500 font-bold' : 'hover:bg-zinc-800/30' }} transition-colors">
                                        <td class="py-3 px-3">
                                            @if($driver['position'] === 1)
                                                <span class="w-6 h-6 rounded bg-amber-400 text-black font-black text-xs flex items-center justify-center shadow-sm">1</span>
                                            @elseif($driver['position'] === 2)
                                                <span class="w-6 h-6 rounded bg-zinc-300 text-black font-black text-xs flex items-center justify-center">2</span>
                                            @elseif($driver['position'] === 3)
                                                <span class="w-6 h-6 rounded bg-amber-700 text-white font-black text-xs flex items-center justify-center">3</span>
                                            @else
                                                <span class="text-zinc-400 font-bold pl-1.5">{{ $driver['position'] }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-white font-bold">{{ $driver['driver_name'] }}</span>
                                                @if($driver['is_player'])
                                                    <span class="text-[9px] bg-orange-600 text-white px-1.5 py-0.2 rounded font-black tracking-tighter">YOU</span>
                                                @endif
                                            </div>
                                            <div class="text-[10px] text-zinc-400 uppercase font-normal">{{ $driver['team_name'] }}</div>
                                        </td>
                                        <td class="py-3 px-3 text-zinc-400 text-[11px]">
                                            {{ $driver['car_name'] }}
                                        </td>
                                        <td class="py-3 px-3 text-right text-zinc-200">
                                            {{ $driver['total_time'] }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-bold {{ $driver['gap'] === 'LEADER' ? 'text-emerald-400' : 'text-zinc-400' }}">
                                            {{ $driver['gap'] }}
                                        </td>
                                        <td class="py-3 px-3 text-right text-cyan-400 font-mono">
                                            {{ $driver['fastest_lap'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-6 text-center text-xs font-mono text-zinc-400">
                        Detailed simulation classification saved in race log record #{{ $result->id }}.
                    </div>
                @endif
            </div>
        </div>

        <!-- Right 1 Col: Post-Race Pit-Wall Actions -->
        <div class="space-y-6">
            <!-- Sponsor Contract Performance & Payouts -->
            @if(isset($simulation['sponsor_settlements']) && count($simulation['sponsor_settlements']) > 0)
                <div class="bg-zinc-900 border border-amber-500/40 rounded p-6 shadow-lg space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-zinc-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded bg-amber-400"></span>
                            <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Commercial Sponsor Payouts</h3>
                        </div>
                        <span class="text-xs font-mono font-bold text-amber-400">
                            +{{ number_format($simulation['sponsor_bonus_total'] ?? 0) }} CR
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($simulation['sponsor_settlements'] as $settlement)
                            <div class="bg-zinc-950 p-3 rounded border {{ $settlement['achieved'] ? 'border-emerald-500/40 bg-emerald-950/10' : 'border-zinc-800' }} text-xs font-mono">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-white uppercase">{{ $settlement['sponsor_name'] }}</span>
                                    @if($settlement['achieved'])
                                        <span class="text-emerald-400 font-bold text-[10px] bg-emerald-950 border border-emerald-500/30 px-1.5 py-0.5 rounded">
                                            ✓ TARGET HIT (+{{ number_format($settlement['bonus_earned']) }} CR)
                                        </span>
                                    @else
                                        <span class="text-zinc-500 text-[10px] bg-zinc-900 px-1.5 py-0.5 rounded">
                                            MISSED (0 CR)
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-zinc-400 mt-1">
                                    Objective: <span class="text-zinc-300">{{ $settlement['objective_label'] ?? $settlement['target_objective'] }}</span>
                                </div>
                                <div class="text-[10px] text-zinc-500 mt-1 flex justify-between">
                                    <span>Duration: {{ $settlement['races_remaining'] }} races left</span>
                                    @if($settlement['expired'])
                                        <span class="text-amber-400 font-bold uppercase">Contract Expired</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Navigation Action Deck -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg space-y-3">
                <div class="text-xs font-mono font-bold uppercase tracking-wider text-zinc-400 pb-2 border-b border-zinc-800">
                    Post-Race Command Hub
                </div>

                <a href="{{ route('races.index') }}" class="w-full text-center py-3 px-4 rounded bg-orange-600 hover:bg-orange-500 text-white text-xs font-mono font-bold tracking-wider uppercase transition shadow-md block">
                    Next Grand Prix Schedule &rarr;
                </a>

                <a href="{{ route('dashboard') }}" class="w-full text-center py-2.5 px-4 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white text-xs font-mono font-bold uppercase transition block">
                    Return to Paddock Dashboard
                </a>

                <a href="{{ route('sponsors.index') }}" class="w-full text-center py-2.5 px-4 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-amber-400 hover:text-amber-300 text-xs font-mono font-bold uppercase transition block">
                    Manage Sponsor Contracts
                </a>

                <a href="{{ route('garage.show', $result->car_id) }}" class="w-full text-center py-2.5 px-4 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white text-xs font-mono font-bold uppercase transition block">
                    Inspect Race Chassis in Garage
                </a>

                <a href="{{ route('drivers.show', $result->driver_id) }}" class="w-full text-center py-2.5 px-4 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white text-xs font-mono font-bold uppercase transition block">
                    View Driver Dossier & Career
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
