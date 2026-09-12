@extends('layouts.app')

@section('title', 'Technical Inspection - ' . $car->name)

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
                    <a href="{{ route('garage.index') }}" class="hover:text-orange-400 transition-colors uppercase">GARAGE FLEET</a>
                    <span>/</span>
                    <span class="text-orange-400 font-bold uppercase">CHASSIS #{{ str_pad($car->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight">
                        {{ $car->name }}
                    </h1>
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded bg-zinc-800 text-zinc-300 border border-zinc-700">
                        LEVEL {{ $car->level }}
                    </span>
                    @if($car->is_active)
                        <span class="text-xs font-mono font-black uppercase tracking-wider bg-emerald-950 border border-emerald-500/50 text-emerald-400 px-2.5 py-1 rounded flex items-center gap-1.5 shadow-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            ACTIVE RACE CHASSIS
                        </span>
                    @else
                        <span class="text-xs font-mono font-bold uppercase tracking-wider bg-zinc-950 border border-zinc-800 text-zinc-500 px-2.5 py-1 rounded">
                            STANDBY UNIT
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('garage.index') }}" class="px-4 py-2 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 text-xs font-mono font-bold uppercase transition flex items-center gap-1.5">
                    <span>&larr; Back to Fleet</span>
                </a>

                @if(!$car->is_active)
                    <form method="POST" action="{{ route('garage.set-active', $car) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded bg-orange-600 hover:bg-orange-500 text-white text-xs font-mono font-bold tracking-wider uppercase transition shadow-md cursor-pointer">
                            Assign as Active Car
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Telemetry & Technical Specs Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Detailed Telemetry Stat Gauges -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Telemetry Performance Sheet -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg">
                <div class="flex items-center justify-between pb-4 mb-6 border-b border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded bg-orange-500"></span>
                        <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Chassis Performance & Telemetry Matrix</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono text-zinc-400 uppercase">Composite OVR:</span>
                        <span class="text-base font-mono font-black text-orange-400">{{ $performanceRating }} / 100</span>
                    </div>
                </div>

                <div class="space-y-5">
                    <!-- Top Speed Gauge -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold uppercase text-blue-400">01 // TOP SPEED</span>
                                <span class="text-[10px] font-mono text-zinc-500">Straight-line velocity & aerodynamic drag</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $car->speed }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-3 overflow-hidden border border-zinc-800">
                            <div class="bg-blue-500 h-full rounded transition-all duration-500" style="width: {{ min(100, $car->speed) }}%"></div>
                        </div>
                    </div>

                    <!-- Acceleration Gauge -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold uppercase text-cyan-400">02 // ACCELERATION</span>
                                <span class="text-[10px] font-mono text-zinc-500">Torque delivery & launch response</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $car->acceleration }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-3 overflow-hidden border border-zinc-800">
                            <div class="bg-cyan-500 h-full rounded transition-all duration-500" style="width: {{ min(100, $car->acceleration) }}%"></div>
                        </div>
                    </div>

                    <!-- Handling Gauge -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold uppercase text-emerald-400">03 // CORNERING HANDLING</span>
                                <span class="text-[10px] font-mono text-zinc-500">Downforce, grip & steering response</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $car->handling }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-3 overflow-hidden border border-zinc-800">
                            <div class="bg-emerald-500 h-full rounded transition-all duration-500" style="width: {{ min(100, $car->handling) }}%"></div>
                        </div>
                    </div>

                    <!-- Braking Gauge -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold uppercase text-amber-400">04 // BRAKING EFFICIENCY</span>
                                <span class="text-[10px] font-mono text-zinc-500">Deceleration rate & heat dissipation</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $car->braking }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-3 overflow-hidden border border-zinc-800">
                            <div class="bg-amber-500 h-full rounded transition-all duration-500" style="width: {{ min(100, $car->braking) }}%"></div>
                        </div>
                    </div>

                    <!-- Reliability Gauge -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold uppercase text-emerald-400">05 // MECHANICAL RELIABILITY</span>
                                <span class="text-[10px] font-mono text-zinc-500">Component wear resistance & failure prevention</span>
                            </div>
                            <span class="text-sm font-mono font-black text-emerald-400">{{ $car->reliability }}%</span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-3 overflow-hidden border border-zinc-800">
                            <div class="bg-emerald-400 h-full rounded transition-all duration-500" style="width: {{ min(100, $car->reliability) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Installed Upgrade Parts Matrix -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded bg-cyan-500"></span>
                        <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Installed Upgrade Packages</h2>
                    </div>
                    <span class="text-xs font-mono text-zinc-400">{{ $upgrades->count() }} Mod(s) Active</span>
                </div>

                @if($upgrades->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($upgrades as $upgrade)
                            <div class="bg-zinc-950/80 border border-zinc-800 rounded p-3.5 flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-mono font-bold text-white uppercase">{{ ucfirst($upgrade->part_type) }} Package</div>
                                    <div class="text-[10px] font-mono text-zinc-500 uppercase">Upgrade Level {{ $upgrade->level }}</div>
                                </div>
                                <div class="text-right">
                                    @if(is_array($upgrade->stat_increases))
                                        @foreach($upgrade->stat_increases as $stat => $val)
                                            <span class="text-xs font-mono font-bold text-emerald-400 block">+{{ $val }} {{ strtoupper($stat) }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-zinc-950/60 border border-zinc-800/80 rounded p-6 text-center">
                        <p class="text-xs font-mono text-zinc-400">No aftermarket R&D upgrades installed on this chassis yet.</p>
                        <p class="text-[11px] font-mono text-zinc-500 mt-1">Car upgrade mechanics will be unlocked during R&D Engineering development (Task 6).</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right 1 Col: Chassis Card, Telemetry Blueprint, & Quick Info -->
        <div class="space-y-6">
            <!-- Chassis Technical Card -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg">
                <div class="text-xs font-mono font-bold uppercase tracking-wider text-zinc-400 mb-4 pb-2 border-b border-zinc-800">
                    Chassis Blueprint Data
                </div>

                <div class="space-y-3 text-xs font-mono">
                    <div class="flex justify-between py-1.5 border-b border-zinc-800/60">
                        <span class="text-zinc-500">Constructor</span>
                        <span class="text-white font-bold">{{ $team->name }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-zinc-800/60">
                        <span class="text-zinc-500">Model Name</span>
                        <span class="text-white font-bold">{{ $car->name }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-zinc-800/60">
                        <span class="text-zinc-500">Chassis Level</span>
                        <span class="text-orange-400 font-bold">Tier {{ $car->level }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-zinc-800/60">
                        <span class="text-zinc-500">Race Assignment</span>
                        @if($car->is_active)
                            <span class="text-emerald-400 font-bold uppercase">Primary Car</span>
                        @else
                            <span class="text-zinc-500 uppercase">Standby Roster</span>
                        @endif
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-zinc-800/60">
                        <span class="text-zinc-500">Chassis UID</span>
                        <span class="text-zinc-400 font-mono">#{{ str_pad($car->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-zinc-500">Commission Date</span>
                        <span class="text-zinc-400">{{ $car->created_at->format('M d, Y') }}</span>
                    </div>
                </div>

                <!-- Active Status Actions -->
                <div class="mt-6 pt-4 border-t border-zinc-800">
                    @if(!$car->is_active)
                        <form method="POST" action="{{ route('garage.set-active', $car) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full text-center py-2.5 px-4 rounded bg-orange-600 hover:bg-orange-500 text-white text-xs font-mono font-bold tracking-wider uppercase transition shadow cursor-pointer">
                                Set as Primary Race Car
                            </button>
                        </form>
                    @else
                        <div class="w-full py-2 px-3 rounded bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 text-center text-xs font-mono font-bold flex items-center justify-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Currently Active for Racing</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Navigation Box -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-5 shadow-lg">
                <div class="text-xs font-mono font-bold uppercase tracking-wider text-zinc-400 mb-3">
                    Pit-Wall Quick Nav
                </div>
                <div class="space-y-2 text-xs font-mono">
                    <a href="{{ route('dashboard') }}" class="block p-2.5 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white transition-colors">
                        &rarr; Return to Paddock Dashboard
                    </a>
                    <a href="{{ route('garage.index') }}" class="block p-2.5 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white transition-colors">
                        &rarr; View All Garage Vehicles
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
