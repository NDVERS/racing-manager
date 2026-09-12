@extends('layouts.app')

@section('title', 'Commercial Sponsorships & Partnerships - ' . $team->name)

@section('content')
<div class="space-y-6">
    <!-- Header / Breadcrumb Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 via-orange-500 to-yellow-400"></div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1 text-xs font-mono text-zinc-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-amber-400 transition-colors">PADDOCK</a>
                    <span>/</span>
                    <span class="text-amber-400 font-bold uppercase">COMMERCIAL PARTNERSHIPS</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight">
                    Sponsors & Commercial Hub
                </h1>
                <p class="text-xs text-zinc-400 mt-1 font-mono">
                    Negotiate corporate backing, earn upfront signing capital, and unlock per-race performance milestone bonuses.
                </p>
            </div>

            <!-- Commercial Status Badges -->
            <div class="flex items-center gap-3">
                <div class="bg-zinc-950/80 border border-zinc-800 px-3 py-2 rounded text-right">
                    <div class="text-[10px] font-mono text-zinc-500 uppercase">Total Sponsor Revenue</div>
                    <div class="text-sm font-mono font-bold text-amber-400">+{{ number_format($totalSponsorEarnings) }} CR</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Sponsor Contracts Section -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-zinc-800">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded bg-amber-500"></span>
                <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Active Commercial Partnerships</h2>
                <span class="text-xs font-mono text-zinc-500">({{ $activeContracts->count() }} Signed)</span>
            </div>
            <div class="flex items-center gap-3 text-xs font-mono">
                <span class="text-zinc-400">
                    Primary Slot: <span class="font-bold {{ $activePrimaryCount >= 1 ? 'text-amber-400' : 'text-emerald-400' }}">{{ $activePrimaryCount }}/1</span>
                </span>
                <span class="text-zinc-600">&bull;</span>
                <span class="text-zinc-400">
                    Technical Slots: <span class="font-bold {{ $activeSecondaryCount >= 2 ? 'text-amber-400' : 'text-emerald-400' }}">{{ $activeSecondaryCount }}/2</span>
                </span>
            </div>
        </div>

        @if($activeContracts->isEmpty())
            <div class="text-center py-10 bg-zinc-950/60 border border-zinc-800/80 rounded-lg">
                <div class="w-12 h-12 mx-auto rounded-full bg-amber-950/40 border border-amber-500/30 flex items-center justify-center text-amber-400 text-xl mb-3">
                    💼
                </div>
                <h3 class="text-sm font-mono font-bold text-white uppercase">No Active Sponsor Contracts</h3>
                <p class="text-xs text-zinc-400 max-w-md mx-auto mt-1 font-mono">
                    Your team does not currently have any active sponsors. Browse the open market below to secure signing grants and race performance bonuses.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($activeContracts as $contract)
                    @php
                        $sponsor = $contract->sponsor;
                        $progressPercent = max(0, min(100, round(($contract->races_remaining / $sponsor->duration_races) * 100)));
                    @endphp
                    <div class="bg-zinc-950 border border-zinc-800 hover:border-amber-500/50 rounded-lg p-5 shadow transition-all flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider px-2 py-0.5 rounded border {{ $sponsor->tierBadgeClasses() }}">
                                        {{ $sponsor->tierLabel() }}
                                    </span>
                                    <h3 class="text-base font-mono font-black text-white uppercase mt-2">
                                        {{ $sponsor->name }}
                                    </h3>
                                </div>
                                <span class="flex h-2.5 w-2.5 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                </span>
                            </div>

                            <p class="text-xs text-zinc-400 line-clamp-2">
                                {{ $sponsor->description }}
                            </p>

                            <!-- Target Objective Box -->
                            <div class="bg-zinc-900 border border-zinc-800/80 rounded p-3 text-xs font-mono space-y-1">
                                <div class="text-[10px] text-zinc-500 uppercase tracking-wide">Race Milestone Objective</div>
                                <div class="text-zinc-200 font-bold flex items-center gap-1.5">
                                    <span class="text-amber-400">&bull;</span>
                                    <span>{{ $sponsor->objectiveLabel() }}</span>
                                </div>
                                <div class="text-[11px] text-emerald-400 font-bold pt-0.5">
                                    +{{ number_format($sponsor->bonus_per_race) }} CR / Race upon completion
                                </div>
                            </div>
                        </div>

                        <!-- Duration Progress Bar -->
                        <div class="pt-3 border-t border-zinc-900 space-y-1.5 font-mono text-xs">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-zinc-500">Contract Duration:</span>
                                <span class="font-bold text-amber-400">{{ $contract->races_remaining }} of {{ $sponsor->duration_races }} Races Left</span>
                            </div>
                            <div class="w-full bg-zinc-900 rounded-full h-2 overflow-hidden border border-zinc-800">
                                <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-full rounded-full transition-all duration-300" style="width: {{ $progressPercent }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Available Commercial Market Catalog -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg space-y-4">
        <div class="flex items-center justify-between pb-4 border-b border-zinc-800">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded bg-cyan-500"></span>
                <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Commercial Sponsor Market</h2>
            </div>
            <div class="text-xs font-mono text-zinc-400">
                Team Reputation: <span class="text-cyan-400 font-bold">{{ $team->reputation }} REP</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($allSponsors as $sponsor)
                @php
                    $isSigned = $team->hasActiveSponsor($sponsor->id);
                    $hasRep = ($team->reputation >= $sponsor->min_reputation);
                    $canSign = $team->canSignSponsor($sponsor);
                @endphp
                <div class="bg-zinc-950 border {{ $isSigned ? 'border-amber-500/40 bg-zinc-950/90' : ($hasRep ? 'border-zinc-800 hover:border-zinc-700' : 'border-zinc-800/60 opacity-75') }} rounded-lg p-5 shadow flex flex-col justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider px-2 py-0.5 rounded border {{ $sponsor->tierBadgeClasses() }}">
                                    {{ $sponsor->tierLabel() }}
                                </span>
                                <h3 class="text-base font-mono font-black text-white uppercase mt-2">
                                    {{ $sponsor->name }}
                                </h3>
                            </div>
                            @if($isSigned)
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-amber-500/20 border border-amber-500/40 text-amber-400 uppercase">
                                    Active
                                </span>
                            @elseif(! $hasRep)
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-red-950/60 border border-red-500/30 text-red-400 uppercase">
                                    Locked
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-zinc-400 line-clamp-2">
                            {{ $sponsor->description }}
                        </p>

                        <!-- Deal Financial Terms -->
                        <div class="bg-zinc-900/90 border border-zinc-800/80 rounded p-3 text-xs font-mono space-y-2">
                            <div class="flex items-center justify-between border-b border-zinc-800/60 pb-1.5">
                                <span class="text-zinc-500">Signing Grant:</span>
                                <span class="text-amber-400 font-bold">+{{ number_format($sponsor->signing_bonus) }} CR</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-zinc-800/60 pb-1.5">
                                <span class="text-zinc-500">Target Objective:</span>
                                <span class="text-zinc-200 font-semibold">{{ $sponsor->objectiveLabel() }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-zinc-800/60 pb-1.5">
                                <span class="text-zinc-500">Race Bonus:</span>
                                <span class="text-emerald-400 font-bold">+{{ number_format($sponsor->bonus_per_race) }} CR/Race</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">Contract Length:</span>
                                <span class="text-zinc-300">{{ $sponsor->duration_races }} Grand Prix</span>
                            </div>
                        </div>

                        <!-- Requirements Check -->
                        <div class="text-[11px] font-mono flex items-center justify-between">
                            <span class="text-zinc-500">Min Reputation:</span>
                            @if($hasRep)
                                <span class="text-emerald-400 font-bold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ $sponsor->min_reputation }} REP (Met)</span>
                                </span>
                            @else
                                <span class="text-red-400 font-bold">
                                    {{ $sponsor->min_reputation }} REP (Need {{ $sponsor->min_reputation - $team->reputation }} more)
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-3 border-t border-zinc-900">
                        @if($isSigned)
                            <button type="button" disabled class="w-full py-2.5 px-3 rounded bg-zinc-900 border border-zinc-800 text-zinc-500 text-xs font-mono font-bold uppercase cursor-not-allowed text-center">
                                Currently Under Contract
                            </button>
                        @elseif($canSign)
                            <form method="POST" action="{{ route('sponsors.sign', $sponsor) }}">
                                @csrf
                                <button type="submit" class="w-full py-2.5 px-3 rounded bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-400 hover:to-orange-500 text-black font-mono font-bold text-xs uppercase tracking-wider transition shadow-md cursor-pointer flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>Sign Partnership Deal</span>
                                </button>
                            </form>
                        @elseif(! $hasRep)
                            <button type="button" disabled class="w-full py-2.5 px-3 rounded bg-zinc-900/60 border border-zinc-800/80 text-zinc-600 text-xs font-mono font-bold uppercase cursor-not-allowed text-center">
                                Reputation Locked
                            </button>
                        @else
                            <button type="button" disabled class="w-full py-2.5 px-3 rounded bg-zinc-900/60 border border-zinc-800/80 text-zinc-600 text-xs font-mono font-bold uppercase cursor-not-allowed text-center">
                                Tier Slots Full
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Historical Expired Contracts Table -->
    @if($expiredContracts->isNotEmpty())
        <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-zinc-800">
                <span class="w-2 h-2 rounded bg-zinc-600"></span>
                <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-zinc-400">Concluded Partnerships Archive</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-zinc-950 text-zinc-500 uppercase text-[11px] border-b border-zinc-800">
                        <tr>
                            <th class="py-2.5 px-3">Sponsor Entity</th>
                            <th class="py-2.5 px-3">Tier</th>
                            <th class="py-2.5 px-3">Signing Grant</th>
                            <th class="py-2.5 px-3">Target Objective</th>
                            <th class="py-2.5 px-3">Signed Date</th>
                            <th class="py-2.5 px-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60">
                        @foreach($expiredContracts as $expired)
                            <tr class="hover:bg-zinc-800/30 text-zinc-400">
                                <td class="py-2.5 px-3 font-bold text-zinc-300">{{ $expired->sponsor->name }}</td>
                                <td class="py-2.5 px-3 uppercase text-[10px]">{{ $expired->sponsor->tier }}</td>
                                <td class="py-2.5 px-3 text-amber-400">+{{ number_format($expired->sponsor->signing_bonus) }} CR</td>
                                <td class="py-2.5 px-3">{{ $expired->sponsor->objectiveLabel() }}</td>
                                <td class="py-2.5 px-3 text-zinc-500">{{ $expired->signed_at?->format('M d, Y') ?? 'N/A' }}</td>
                                <td class="py-2.5 px-3 text-right">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-zinc-800 text-zinc-400">EXPIRED</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
