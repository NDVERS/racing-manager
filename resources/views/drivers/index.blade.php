@extends('layouts.app')

@section('title', 'Driver Lineup - ' . $team->name)

@section('content')
<div class="space-y-6">
    <!-- Header / Breadcrumb Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-cyan-500 via-blue-500 to-indigo-600"></div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1 text-xs font-mono text-zinc-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-cyan-400 transition-colors">PADDOCK</a>
                    <span>/</span>
                    <span class="text-cyan-400 font-bold uppercase">DRIVER LINEUP</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight">
                    Driver Lineup & Contracts
                </h1>
                <p class="text-xs text-zinc-400 mt-1 font-mono">
                    Manage your contracted drivers, designate the lead race driver, and recruit talent.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('drivers.market') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-mono font-bold tracking-wider uppercase transition shadow-md cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>Scout Free Agent Market</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Active Lead Driver Highlight Banner (if exists) -->
    @if($leadDriver)
        <div class="bg-gradient-to-r from-zinc-900 via-zinc-900/90 to-zinc-950 border border-cyan-500/40 rounded p-5 shadow-lg relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded bg-cyan-950 border border-cyan-500/60 flex items-center justify-center text-cyan-400 font-mono font-black text-lg shadow-inner shrink-0">
                        #1
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                            <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-cyan-400">DESIGNATED LEAD RACE DRIVER</span>
                        </div>
                        <h2 class="text-xl font-black text-white font-mono uppercase tracking-tight">{{ $leadDriver->name }}</h2>
                        <span class="text-xs font-mono text-zinc-400">
                            Overall Rating: <span class="text-cyan-300 font-bold">{{ $leadDriver->overallRating() }} OVR</span> &bull; Salary: <span class="text-amber-400 font-bold">{{ number_format($leadDriver->salary) }} CR/Race</span>
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('drivers.show', $leadDriver) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded bg-zinc-800 hover:bg-zinc-700 text-cyan-300 hover:text-white text-xs font-mono font-bold tracking-wider uppercase transition border border-zinc-700">
                        <span>Inspect Dossier</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Contracted Driver Roster Grid -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded bg-cyan-500"></span>
                <span>Contracted Team Roster</span>
            </h2>
            <span class="text-xs font-mono text-zinc-400">{{ $drivers->count() }} Driver(s) Employed</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @forelse($drivers as $driver)
                @php
                    $ovr = $driver->overallRating();
                @endphp
                <div class="bg-zinc-900/90 border {{ $driver->is_lead ? 'border-cyan-500/60 shadow-cyan-500/5 ring-1 ring-cyan-500/20' : 'border-zinc-800' }} rounded p-6 shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-xl font-black text-white font-mono uppercase tracking-tight">{{ $driver->name }}</h3>
                                    <span class="text-xs font-mono font-black px-2 py-0.5 rounded bg-zinc-800 text-cyan-300 border border-zinc-700">
                                        {{ $ovr }} OVR
                                    </span>
                                </div>
                                <p class="text-xs font-mono text-zinc-400 mt-0.5">Contract Fee: <span class="text-amber-400 font-bold">{{ number_format($driver->salary) }} CR</span> / race</p>
                            </div>

                            @if($driver->is_lead)
                                <span class="text-[10px] font-mono font-black uppercase tracking-wider bg-cyan-950 border border-cyan-500/50 text-cyan-400 px-2.5 py-1 rounded flex items-center gap-1.5 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                                    LEAD DRIVER
                                </span>
                            @else
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider bg-zinc-950 border border-zinc-800 text-zinc-500 px-2.5 py-1 rounded">
                                    RESERVE SEAT
                                </span>
                            @endif
                        </div>

                        <!-- 7 Core Stat Attributes Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4 text-center text-xs font-mono">
                            <div class="bg-zinc-950/80 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Pace</div>
                                <div class="font-bold text-white text-sm">{{ $driver->pace }}</div>
                            </div>
                            <div class="bg-zinc-950/80 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Cornering</div>
                                <div class="font-bold text-white text-sm">{{ $driver->cornering }}</div>
                            </div>
                            <div class="bg-zinc-950/80 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Consistency</div>
                                <div class="font-bold text-white text-sm">{{ $driver->consistency }}</div>
                            </div>
                            <div class="bg-zinc-950/80 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Overtaking</div>
                                <div class="font-bold text-white text-sm">{{ $driver->overtaking }}</div>
                            </div>
                            <div class="bg-zinc-950/80 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Defending</div>
                                <div class="font-bold text-white text-sm">{{ $driver->defensive }}</div>
                            </div>
                            <div class="bg-zinc-950/80 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Racecraft</div>
                                <div class="font-bold text-white text-sm">{{ $driver->racecraft }}</div>
                            </div>
                            <div class="bg-zinc-950/80 p-2 rounded border border-zinc-800/80 col-span-2">
                                <div class="text-[9px] text-zinc-500 uppercase">Experience</div>
                                <div class="font-bold text-white text-sm">{{ $driver->experience }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="pt-4 border-t border-zinc-800/80 flex items-center justify-between gap-3">
                        <a href="{{ route('drivers.show', $driver) }}" class="text-xs font-mono font-bold text-zinc-300 hover:text-white bg-zinc-950 hover:bg-zinc-800 border border-zinc-700/80 rounded px-3.5 py-2 transition-colors flex items-center gap-1.5">
                            <span>Driver Dossier</span>
                            <span class="text-zinc-400">&rarr;</span>
                        </a>

                        @if(!$driver->is_lead)
                            <form method="POST" action="{{ route('drivers.set-lead', $driver) }}">
                                @csrf
                                <button type="submit" class="text-xs font-mono font-bold text-cyan-300 hover:text-white bg-cyan-950 hover:bg-cyan-600 border border-cyan-500/50 hover:border-cyan-500 rounded px-3.5 py-2 transition-all cursor-pointer shadow-sm">
                                    Promote to Lead Driver
                                </button>
                            </form>
                        @else
                            <span class="text-xs font-mono font-bold text-cyan-400 bg-cyan-950/60 border border-cyan-500/30 rounded px-3.5 py-2 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Designated Lead Driver</span>
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-2 bg-zinc-900 border border-zinc-800 rounded p-12 text-center">
                    <p class="text-zinc-400 font-mono text-sm">No drivers currently contracted to your racing constructor.</p>
                    <a href="{{ route('drivers.market') }}" class="mt-3 inline-block text-xs font-mono font-bold text-cyan-400 underline">
                        Scout the Free Agent Market &rarr;
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
