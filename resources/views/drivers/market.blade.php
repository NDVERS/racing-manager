@extends('layouts.app')

@section('title', 'Free Agent Driver Market - ' . $team->name)

@section('content')
<div class="space-y-6">
    <!-- Header / Treasury Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 via-cyan-500 to-blue-600"></div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1 text-xs font-mono text-zinc-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-cyan-400 transition-colors">PADDOCK</a>
                    <span>/</span>
                    <a href="{{ route('drivers.index') }}" class="hover:text-cyan-400 transition-colors uppercase">DRIVER LINEUP</a>
                    <span>/</span>
                    <span class="text-cyan-400 font-bold uppercase">FREE AGENT MARKET</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight">
                    Free Agent Driver Market
                </h1>
                <p class="text-xs text-zinc-400 mt-1 font-mono">
                    Scout uncontracted drivers, evaluate their ratings and salary demands, and sign new talent.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="bg-zinc-950/90 border border-zinc-800 rounded px-4 py-2.5 text-right">
                    <div class="text-[10px] font-mono text-zinc-400 uppercase tracking-wider">AVAILABLE TREASURY</div>
                    <div class="text-xl font-mono font-black text-amber-400">
                        {{ number_format($team->money) }} <span class="text-xs text-zinc-500 font-normal">CR</span>
                    </div>
                </div>
                <a href="{{ route('drivers.index') }}" class="px-4 py-2.5 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 text-xs font-mono font-bold uppercase transition flex items-center gap-1.5">
                    <span>&larr; Return to Lineup</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Free Agents List -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded bg-emerald-500"></span>
                <span>Available Free Agents Pool</span>
            </h2>
            <span class="text-xs font-mono text-zinc-400">{{ $freeAgents->count() }} Driver(s) Available</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @forelse($freeAgents as $agent)
                @php
                    $ovr = $agent->overallRating();
                    $cost = $agent->hiringCost();
                    $canAfford = $team->money >= $cost;
                    $isLegend = $agent->isLegend();
                    $tierBadge = $agent->tierBadge();
                @endphp
                <div class="rounded p-6 shadow-md transition-all flex flex-col justify-between relative overflow-hidden border {{ $isLegend ? 'border-amber-500/50 hover:border-amber-400 bg-gradient-to-b from-amber-950/20 via-zinc-900 to-zinc-950 shadow-amber-950/30' : 'border-zinc-800 hover:border-zinc-700 bg-zinc-900/90' }}">
                    @if($isLegend)
                        <div class="absolute top-0 right-0 bg-gradient-to-l from-amber-500 to-yellow-500 text-black text-[10px] font-mono font-black px-3 py-0.5 rounded-bl uppercase tracking-widest shadow-sm">
                            👑 MOTORSPORT LEGEND
                        </div>
                    @endif

                    <div>
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-xl font-black text-white font-mono uppercase tracking-tight">{{ $agent->name }}</h3>
                                    <span class="text-xs font-mono font-black px-2 py-0.5 rounded {{ $isLegend ? 'bg-amber-950 text-amber-300 border border-amber-500/60' : 'bg-zinc-800 text-cyan-300 border border-zinc-700' }}">
                                        {{ $ovr }} OVR
                                    </span>
                                </div>
                                <span class="text-[10px] font-mono {{ $isLegend ? 'text-amber-400/90 font-bold' : 'text-zinc-400' }} uppercase">
                                    {{ $isLegend ? '🏆 HALL OF FAME LEGEND &bull; FREE AGENT' : 'UNCONTRACTED FREE AGENT' }}
                                </span>
                            </div>

                            <div class="text-right {{ $isLegend ? 'mt-3 sm:mt-0' : '' }}">
                                <span class="text-[10px] font-mono text-zinc-500 uppercase block">Signing Fee</span>
                                <span class="text-sm font-mono font-black text-amber-400">{{ number_format($cost) }} CR</span>
                            </div>
                        </div>

                        <!-- 7 Core Stat Attributes Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4 text-center text-xs font-mono">
                            <div class="bg-zinc-950/80 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Pace</div>
                                <div class="font-bold text-white text-sm">{{ $agent->pace }}</div>
                            </div>
                            <div class="bg-zinc-950/80 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Cornering</div>
                                <div class="font-bold text-white text-sm">{{ $agent->cornering }}</div>
                            </div>
                            <div class="bg-zinc-950/80 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Consistency</div>
                                <div class="font-bold text-white text-sm">{{ $agent->consistency }}</div>
                            </div>
                            <div class="bg-zinc-950/80 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Overtaking</div>
                                <div class="font-bold text-white text-sm">{{ $agent->overtaking }}</div>
                            </div>
                            <div class="bg-zinc-950/80 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Defending</div>
                                <div class="font-bold text-white text-sm">{{ $agent->defensive }}</div>
                            </div>
                            <div class="bg-zinc-950/80 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Racecraft</div>
                                <div class="font-bold text-white text-sm">{{ $agent->racecraft }}</div>
                            </div>
                            <div class="bg-zinc-950/80 p-2 rounded border border-zinc-800/80 col-span-2">
                                <div class="text-[9px] text-zinc-500 uppercase">Experience</div>
                                <div class="font-bold text-white text-sm">{{ $agent->experience }}</div>
                            </div>
                        </div>

                        <div class="bg-zinc-950/80 border border-zinc-800/80 rounded p-3 mb-4 flex items-center justify-between text-xs font-mono">
                            <span class="text-zinc-400">Demanded Race Salary:</span>
                            <span class="text-amber-300 font-bold">{{ number_format($agent->salary) }} CR <span class="text-[10px] text-zinc-500">/ race</span></span>
                        </div>
                    </div>

                    <!-- Footer / Hire Action -->
                    <div class="pt-4 border-t border-zinc-800/80 flex items-center justify-between gap-3">
                        <div class="text-xs font-mono text-zinc-400">
                            Total cost: <span class="text-amber-400 font-bold">{{ number_format($cost) }} CR</span>
                        </div>

                        @if($canAfford)
                            <form method="POST" action="{{ route('drivers.hire', $agent) }}">
                                @csrf
                                <button type="submit" class="text-xs font-mono font-bold text-emerald-300 hover:text-white bg-emerald-950 hover:bg-emerald-600 border border-emerald-500/50 hover:border-emerald-500 rounded px-4 py-2 transition-all cursor-pointer shadow-sm">
                                    Sign Contract ({{ number_format($cost) }} CR)
                                </button>
                            </form>
                        @else
                            <button type="button" disabled class="text-xs font-mono font-bold text-zinc-600 bg-zinc-950 border border-zinc-800 rounded px-4 py-2 cursor-not-allowed" title="Not enough credits">
                                Insufficient Credits
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-2 bg-zinc-900 border border-zinc-800 rounded p-12 text-center">
                    <p class="text-zinc-400 font-mono text-sm">All free agent drivers have been contracted by racing constructors.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
