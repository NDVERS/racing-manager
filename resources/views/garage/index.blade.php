@extends('layouts.app')

@section('title', 'Garage Fleet Management - ' . $team->name)

@section('content')
<div class="space-y-6">
    <!-- Header / Breadcrumb Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 via-amber-400 to-red-600"></div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1 text-xs font-mono text-zinc-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-orange-400 transition-colors">PADDOCK</a>
                    <span>/</span>
                    <span class="text-orange-400 font-bold uppercase">GARAGE FLEET</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight">
                    Garage Fleet & Technical Deck
                </h1>
                <p class="text-xs text-zinc-400 mt-1 font-mono">
                    Inspect telemetry, review chassis specifications, and assign your primary race vehicle.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="bg-zinc-950/90 border border-zinc-800 rounded px-3.5 py-2 text-right">
                    <div class="text-[10px] font-mono text-zinc-400 uppercase tracking-wider">TOTAL ASSETS</div>
                    <div class="text-lg font-mono font-black text-white">{{ $cars->count() }} <span class="text-xs text-zinc-400 font-normal">CHASSIS</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Vehicle Highlight Summary (if available) -->
    @if($activeCar)
        <div class="bg-gradient-to-r from-zinc-900 via-zinc-900/90 to-zinc-950 border border-orange-500/40 rounded p-5 shadow-lg relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded bg-orange-950 border border-orange-500/60 flex items-center justify-center text-orange-400 font-mono font-black text-lg shadow-inner shrink-0">
                        #01
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-orange-400">CURRENT DESIGNATED RACE VEHICLE</span>
                        </div>
                        <h2 class="text-xl font-black text-white font-mono uppercase tracking-tight">{{ $activeCar->name }}</h2>
                        <span class="text-xs font-mono text-zinc-400">Level {{ $activeCar->level }} Chassis &bull; {{ $activeCar->upgrades->count() }} Installed Upgrades</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('garage.show', $activeCar) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded bg-orange-600 hover:bg-orange-500 text-white text-xs font-mono font-bold tracking-wider uppercase transition shadow-md">
                        <span>Inspect Tech Sheet</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Car Roster Grid -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded bg-orange-500"></span>
                <span>Fleet Roster & Performance Ratings</span>
            </h2>
            <span class="text-xs font-mono text-zinc-400">{{ $cars->count() }} Vehicle(s) Assigned</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @forelse($cars as $car)
                @php
                    $perfIndex = (int) round(($car->speed + $car->acceleration + $car->handling + $car->braking + $car->reliability) / 5);
                @endphp
                <div class="bg-zinc-900/90 border {{ $car->is_active ? 'border-orange-500/60 shadow-orange-500/5 ring-1 ring-orange-500/20' : 'border-zinc-800' }} rounded p-6 shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <!-- Header / Status Badge -->
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-xl font-black text-white font-mono uppercase tracking-tight">{{ $car->name }}</h3>
                                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-zinc-800 text-zinc-300 border border-zinc-700">
                                        LVL {{ $car->level }}
                                    </span>
                                </div>
                                <p class="text-xs font-mono text-zinc-400 mt-0.5">Constructor Chassis &bull; ID: #{{ str_pad($car->id, 4, '0', STR_PAD_LEFT) }}</p>
                            </div>

                            @if($car->is_active)
                                <span class="text-[10px] font-mono font-black uppercase tracking-wider bg-emerald-950 border border-emerald-500/50 text-emerald-400 px-2.5 py-1 rounded flex items-center gap-1.5 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    ACTIVE
                                </span>
                            @else
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider bg-zinc-950 border border-zinc-800 text-zinc-500 px-2.5 py-1 rounded">
                                    STANDBY
                                </span>
                            @endif
                        </div>

                        <!-- Overall Composite Rating -->
                        <div class="bg-zinc-950/80 border border-zinc-800/80 rounded p-3 mb-5 flex items-center justify-between">
                            <div class="text-xs font-mono text-zinc-400 uppercase">Composite Index (OVR)</div>
                            <div class="flex items-center gap-2">
                                <div class="w-32 bg-zinc-800 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-2 rounded-full" style="width: {{ min(100, $perfIndex) }}%"></div>
                                </div>
                                <span class="text-sm font-mono font-black text-orange-400">{{ $perfIndex }}</span>
                            </div>
                        </div>

                        <!-- Telemetry Stat Bars (0-100) -->
                        <div class="space-y-2.5 mb-6 text-xs font-mono">
                            <!-- Top Speed -->
                            <div>
                                <div class="flex justify-between text-zinc-400 mb-1 text-[11px]">
                                    <span class="uppercase">Top Speed</span>
                                    <span class="text-zinc-200 font-bold">{{ $car->speed }} / 100</span>
                                </div>
                                <div class="w-full bg-zinc-950 rounded h-1.5 overflow-hidden border border-zinc-800/80">
                                    <div class="bg-blue-500 h-full rounded" style="width: {{ min(100, $car->speed) }}%"></div>
                                </div>
                            </div>

                            <!-- Acceleration -->
                            <div>
                                <div class="flex justify-between text-zinc-400 mb-1 text-[11px]">
                                    <span class="uppercase">Acceleration</span>
                                    <span class="text-zinc-200 font-bold">{{ $car->acceleration }} / 100</span>
                                </div>
                                <div class="w-full bg-zinc-950 rounded h-1.5 overflow-hidden border border-zinc-800/80">
                                    <div class="bg-cyan-500 h-full rounded" style="width: {{ min(100, $car->acceleration) }}%"></div>
                                </div>
                            </div>

                            <!-- Handling -->
                            <div>
                                <div class="flex justify-between text-zinc-400 mb-1 text-[11px]">
                                    <span class="uppercase">Handling</span>
                                    <span class="text-zinc-200 font-bold">{{ $car->handling }} / 100</span>
                                </div>
                                <div class="w-full bg-zinc-950 rounded h-1.5 overflow-hidden border border-zinc-800/80">
                                    <div class="bg-emerald-500 h-full rounded" style="width: {{ min(100, $car->handling) }}%"></div>
                                </div>
                            </div>

                            <!-- Braking -->
                            <div>
                                <div class="flex justify-between text-zinc-400 mb-1 text-[11px]">
                                    <span class="uppercase">Braking</span>
                                    <span class="text-zinc-200 font-bold">{{ $car->braking }} / 100</span>
                                </div>
                                <div class="w-full bg-zinc-950 rounded h-1.5 overflow-hidden border border-zinc-800/80">
                                    <div class="bg-amber-500 h-full rounded" style="width: {{ min(100, $car->braking) }}%"></div>
                                </div>
                            </div>

                            <!-- Reliability -->
                            <div>
                                <div class="flex justify-between text-zinc-400 mb-1 text-[11px]">
                                    <span class="uppercase">Reliability</span>
                                    <span class="text-emerald-400 font-bold">{{ $car->reliability }}%</span>
                                </div>
                                <div class="w-full bg-zinc-950 rounded h-1.5 overflow-hidden border border-zinc-800/80">
                                    <div class="bg-emerald-400 h-full rounded" style="width: {{ min(100, $car->reliability) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="pt-4 border-t border-zinc-800/80 flex items-center justify-between gap-3">
                        <a href="{{ route('garage.show', $car) }}" class="text-xs font-mono font-bold text-zinc-300 hover:text-white bg-zinc-950 hover:bg-zinc-800 border border-zinc-700/80 rounded px-3.5 py-2 transition-colors flex items-center gap-1.5">
                            <span>Inspection Sheet</span>
                            <span class="text-zinc-400">&rarr;</span>
                        </a>

                        @if(!$car->is_active)
                            <form method="POST" action="{{ route('garage.set-active', $car) }}">
                                @csrf
                                <button type="submit" class="text-xs font-mono font-bold text-orange-300 hover:text-white bg-orange-950 hover:bg-orange-600 border border-orange-500/50 hover:border-orange-500 rounded px-3.5 py-2 transition-all cursor-pointer shadow-sm">
                                    Set as Primary Car
                                </button>
                            </form>
                        @else
                            <span class="text-xs font-mono font-bold text-emerald-400 bg-emerald-950/60 border border-emerald-500/30 rounded px-3.5 py-2 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Primary Race Car</span>
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-2 bg-zinc-900 border border-zinc-800 rounded p-12 text-center">
                    <p class="text-zinc-400 font-mono text-sm">No cars found in your team garage roster.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
