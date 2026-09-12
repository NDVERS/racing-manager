@extends('layouts.app')

@section('title', 'Engineering Workshop - ' . $car->name)

@section('content')
<div class="space-y-6">
    <!-- Header / Breadcrumb Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 via-amber-400 to-red-600"></div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1 text-xs font-mono text-zinc-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-orange-400 transition-colors">PADDOCK</a>
                    <span>/</span>
                    <a href="{{ route('garage.index') }}" class="hover:text-orange-400 transition-colors uppercase">GARAGE FLEET</a>
                    <span>/</span>
                    <span class="text-orange-400 font-bold uppercase">CHASSIS #{{ str_pad($car->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight">
                        {{ $car->name }}
                    </h1>
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded bg-zinc-800 text-amber-400 border border-zinc-700">
                        CHASSIS TIER {{ $car->level }}
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

            <div class="flex flex-wrap items-center gap-3">
                <div class="bg-zinc-950 border border-zinc-800 rounded px-3 py-2 text-right">
                    <div class="text-[9px] font-mono text-zinc-500 uppercase tracking-widest">TREASURY BALANCE</div>
                    <div class="text-sm font-mono font-black text-amber-400">
                        {{ number_format($team->money) }} <span class="text-xs text-zinc-500">CR</span>
                    </div>
                </div>

                <a href="{{ route('garage.index') }}" class="px-4 py-2.5 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 text-xs font-mono font-bold uppercase transition flex items-center gap-1.5">
                    <span>&larr; Fleet</span>
                </a>

                @if(!$car->is_active)
                    <form method="POST" action="{{ route('garage.set-active', $car) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 rounded bg-orange-600 hover:bg-orange-500 text-white text-xs font-mono font-bold tracking-wider uppercase transition shadow-md cursor-pointer">
                            Assign Active
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Telemetry & Technical Specs Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Telemetry Gauges + R&D Engineering Workshop -->
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

                <div class="space-y-4">
                    <!-- Top Speed Gauge -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-3.5">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold uppercase text-blue-400">01 // TOP SPEED</span>
                                <span class="text-[10px] font-mono text-zinc-500">Straight-line velocity & peak RPM</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $car->speed }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-2.5 overflow-hidden border border-zinc-800">
                            <div class="bg-blue-500 h-full rounded transition-all duration-500" style="width: {{ min(100, $car->speed) }}%"></div>
                        </div>
                    </div>

                    <!-- Acceleration Gauge -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-3.5">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold uppercase text-cyan-400">02 // ACCELERATION</span>
                                <span class="text-[10px] font-mono text-zinc-500">Torque delivery & launch response</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $car->acceleration }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-2.5 overflow-hidden border border-zinc-800">
                            <div class="bg-cyan-500 h-full rounded transition-all duration-500" style="width: {{ min(100, $car->acceleration) }}%"></div>
                        </div>
                    </div>

                    <!-- Handling Gauge -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-3.5">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold uppercase text-emerald-400">03 // CORNERING HANDLING</span>
                                <span class="text-[10px] font-mono text-zinc-500">Downforce, aero balance & grip</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $car->handling }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-2.5 overflow-hidden border border-zinc-800">
                            <div class="bg-emerald-500 h-full rounded transition-all duration-500" style="width: {{ min(100, $car->handling) }}%"></div>
                        </div>
                    </div>

                    <!-- Braking Gauge -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-3.5">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold uppercase text-amber-400">04 // BRAKING EFFICIENCY</span>
                                <span class="text-[10px] font-mono text-zinc-500">Deceleration rate & heat fade prevention</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $car->braking }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-2.5 overflow-hidden border border-zinc-800">
                            <div class="bg-amber-500 h-full rounded transition-all duration-500" style="width: {{ min(100, $car->braking) }}%"></div>
                        </div>
                    </div>

                    <!-- Reliability Gauge -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-3.5">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold uppercase text-purple-400">05 // MECHANICAL RELIABILITY</span>
                                <span class="text-[10px] font-mono text-zinc-500">Structural integrity & failure resistance</span>
                            </div>
                            <span class="text-sm font-mono font-black text-purple-400">{{ $car->reliability }}%</span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-2.5 overflow-hidden border border-zinc-800">
                            <div class="bg-purple-500 h-full rounded transition-all duration-500" style="width: {{ min(100, $car->reliability) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- R&D Engineering Workshop (Car Upgrade Matrix) -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg relative">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-6 border-b border-zinc-800 gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded bg-orange-500 animate-pulse"></span>
                        <div>
                            <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Pit-Wall R&D Engineering Workshop</h2>
                            <p class="text-[11px] font-mono text-zinc-400">Fabricate and calibrate high-performance component packages (Max Tier {{ \App\Models\CarUpgrade::MAX_LEVEL }}).</p>
                        </div>
                    </div>
                    <div class="text-xs font-mono text-zinc-400">
                        Active Packages: <span class="text-orange-400 font-bold">{{ $upgrades->count() }} / 5</span>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach($partsMatrix as $partKey => $part)
                        <div class="bg-zinc-950/80 border {{ $part['is_max'] ? 'border-emerald-500/40' : 'border-zinc-800' }} rounded p-4 transition-all">
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                <!-- Component Info & Description -->
                                <div class="space-y-1.5 flex-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-mono font-bold text-white uppercase">{{ $part['name'] }}</h3>
                                        @if($part['is_max'])
                                            <span class="text-[10px] font-mono font-black uppercase px-2 py-0.5 rounded bg-emerald-950 border border-emerald-500/50 text-emerald-400">
                                                MAX TIER
                                            </span>
                                        @else
                                            <span class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded bg-zinc-800 text-zinc-300 border border-zinc-700">
                                                TIER {{ $part['current_level'] }} / {{ $part['max_level'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs font-mono text-zinc-400">{{ $part['description'] }}</p>

                                    <!-- Progress Pips -->
                                    <div class="flex items-center gap-1.5 pt-1">
                                        @for($lvl = 1; $lvl <= $part['max_level']; $lvl++)
                                            <div class="h-1.5 flex-1 rounded-sm {{ $lvl <= $part['current_level'] ? 'bg-orange-500 shadow-sm shadow-orange-500/50' : 'bg-zinc-800' }}"></div>
                                        @endfor
                                    </div>
                                </div>

                                <!-- Stat Projection & Action Button -->
                                <div class="flex flex-col sm:flex-row sm:items-center lg:flex-col lg:items-end gap-3 min-w-[220px]">
                                    <!-- Stat Delta Preview -->
                                    <div class="text-left sm:text-right lg:text-right">
                                        <div class="text-[10px] font-mono text-zinc-500 uppercase tracking-wider">
                                            {{ $part['stat_name'] }}
                                        </div>
                                        <div class="text-xs font-mono font-bold">
                                            <span class="text-white">{{ $part['current_stat'] }}</span>
                                            @if(!$part['is_max'])
                                                <span class="text-zinc-500 mx-1">&rarr;</span>
                                                <span class="text-emerald-400">{{ $part['projected_stat'] }}</span>
                                                <span class="text-emerald-400 font-normal">(+{{ $part['delta'] }})</span>
                                            @else
                                                <span class="text-emerald-400 ml-1.5 font-bold">[MAX]</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Upgrade Form or Status Button -->
                                    @if($part['is_max'])
                                        <div class="px-4 py-2 rounded bg-emerald-950/40 border border-emerald-500/30 text-emerald-400 text-xs font-mono font-bold uppercase text-center w-full sm:w-auto">
                                            Fully Upgraded
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('garage.upgrades.purchase', $car) }}" class="w-full sm:w-auto">
                                            @csrf
                                            <input type="hidden" name="part_type" value="{{ $partKey }}">
                                            @if($part['can_afford'])
                                                <button type="submit" class="w-full px-4 py-2 rounded bg-orange-600 hover:bg-orange-500 active:bg-orange-700 text-white text-xs font-mono font-black uppercase tracking-wider transition shadow cursor-pointer flex items-center justify-center gap-1.5">
                                                    <span>UPGRADE TIER {{ $part['next_level'] }}</span>
                                                    <span class="text-orange-200 text-[11px] font-normal">({{ number_format($part['next_cost']) }} CR)</span>
                                                </button>
                                            @else
                                                <button type="button" disabled class="w-full px-4 py-2 rounded bg-zinc-800 text-zinc-500 text-xs font-mono font-bold uppercase tracking-wider cursor-not-allowed border border-zinc-700/50 flex items-center justify-center gap-1.5" title="Insufficient Credits">
                                                    <span>NEED {{ number_format($part['next_cost']) }} CR</span>
                                                </button>
                                            @endif
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
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

            <!-- Engineering Workshop Guidance Card -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-5 shadow-lg">
                <div class="text-xs font-mono font-bold uppercase tracking-wider text-orange-400 mb-3 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                    <span>Engineering R&D Notes</span>
                </div>
                <div class="space-y-2 text-xs font-mono text-zinc-400 leading-relaxed">
                    <p>&bull; Component upgrades directly increase car attributes during Grand Prix race simulations.</p>
                    <p>&bull; Every 3 upgrade levels completed elevates the overall Chassis Tier.</p>
                    <p>&bull; Higher tiers cost exponentially more Credits. Budget race prize money wisely.</p>
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
