@extends('layouts.app')

@section('title', 'Constructor Chassis Showroom & Dealership - ' . $team->name)

@section('content')
<div class="space-y-6">
    <!-- Header / Treasury Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 via-amber-400 to-red-600"></div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1 text-xs font-mono text-zinc-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-orange-400 transition-colors">PADDOCK</a>
                    <span>/</span>
                    <a href="{{ route('garage.index') }}" class="hover:text-orange-400 transition-colors uppercase">GARAGE FLEET</a>
                    <span>/</span>
                    <span class="text-orange-400 font-bold uppercase">CHASSIS SHOWROOM</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight flex items-center gap-3">
                    <span>Chassis Showroom & Dealership</span>
                    <span class="text-xs font-mono font-bold bg-orange-950/80 text-orange-400 border border-orange-500/40 px-2.5 py-1 rounded">
                        OFFICIAL FACTORY CATALOG
                    </span>
                </h1>
                <p class="text-xs text-zinc-400 mt-1 font-mono">
                    Acquire manufacturer-certified race chassis blueprints to expand your constructor fleet and match diverse circuit characteristics.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="bg-zinc-950/90 border border-zinc-800 rounded px-4 py-2.5 text-right">
                    <div class="text-[10px] font-mono text-zinc-400 uppercase tracking-wider">AVAILABLE TREASURY</div>
                    <div class="text-xl font-mono font-black text-amber-400 font-telemetry">
                        {{ number_format($team->credits) }} <span class="text-xs text-zinc-500 font-normal">CR</span>
                    </div>
                </div>
                <a href="{{ route('garage.index') }}" class="px-4 py-2.5 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 text-xs font-mono font-bold uppercase transition flex items-center gap-1.5 shadow">
                    <span>&larr; Return to Garage</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Active Chassis Reference Banner -->
    @if($activeCar)
        @php
            $activePerf = (int) round(($activeCar->speed + $activeCar->acceleration + $activeCar->handling + $activeCar->braking + $activeCar->reliability) / 5);
        @endphp
        <div class="bg-zinc-900/80 border border-zinc-800 rounded p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 font-mono text-xs">
            <div class="flex items-center gap-3">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-zinc-400 uppercase">Current Active Race Car:</span>
                <span class="text-white font-black text-sm uppercase">{{ $activeCar->name }}</span>
                <span class="text-[10px] bg-emerald-950 text-emerald-400 border border-emerald-500/40 px-2 py-0.5 rounded font-bold">
                    {{ $activePerf }} OVR (LVL {{ $activeCar->level }})
                </span>
            </div>
            <div class="flex items-center gap-4 text-zinc-400">
                <span>SPD: <strong class="text-white">{{ $activeCar->speed }}</strong></span>
                <span>ACC: <strong class="text-white">{{ $activeCar->acceleration }}</strong></span>
                <span>HND: <strong class="text-white">{{ $activeCar->handling }}</strong></span>
                <span>BRK: <strong class="text-white">{{ $activeCar->braking }}</strong></span>
                <span>REL: <strong class="text-white">{{ $activeCar->reliability }}%</strong></span>
            </div>
        </div>
    @endif

    <!-- Showroom Catalog Grid -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded bg-orange-500"></span>
                <span>Certified Constructor Blueprints</span>
            </h2>
            <span class="text-xs font-mono text-zinc-400">{{ $templates->count() }} Model(s) in Showroom</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @forelse($templates as $car)
                @php
                    $perfIndex = (int) round(($car->speed + $car->acceleration + $car->handling + $car->braking + $car->reliability) / 5);
                    $isOwned = in_array($car->name, $ownedCarNames, true);
                    $price = (int) ($car->purchase_price ?? 30000);
                    $canAfford = $team->credits >= $price;

                    // Descriptions and circuit specialty traits
                    $specialty = match ($car->name) {
                        'Apex Cyclone' => 'High-Speed Straight Specialist & Aerodynamic Downforce Machine',
                        'Vortex R1' => 'High-G Cornering Agility & Technical Street Circuit Master',
                        'Falcon RS' => 'Rapid Corner-Exit Torque & Explosive Low-End Acceleration',
                        'Phantom GTS' => 'Elite Factory Prototype & High-Reliability Balanced Flagship',
                        'Kuro GT' => 'Standard Balanced Starter Constructor Platform',
                        default => 'Certified Factory Racing Platform',
                    };
                @endphp

                <div class="bg-zinc-900/90 border {{ $isOwned ? 'border-zinc-800 opacity-90' : ($canAfford ? 'border-zinc-800 hover:border-orange-500/50' : 'border-zinc-800/80') }} rounded-lg p-6 shadow-xl transition-all flex flex-col justify-between relative overflow-hidden">
                    @if($isOwned)
                        <div class="absolute top-0 right-0 bg-zinc-800 text-zinc-400 text-[10px] font-mono font-bold px-3 py-1 rounded-bl border-b border-l border-zinc-700 uppercase tracking-wider">
                            ✓ ALREADY IN FLEET
                        </div>
                    @endif

                    <div>
                        <!-- Header / Title & OVR -->
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <h3 class="text-2xl font-black text-white font-mono uppercase tracking-tight">{{ $car->name }}</h3>
                                    <span class="text-xs font-mono font-black px-2.5 py-0.5 rounded bg-zinc-950 text-orange-400 border border-orange-500/40">
                                        {{ $perfIndex }} OVR
                                    </span>
                                </div>
                                <p class="text-xs font-mono text-zinc-400 mt-1 leading-relaxed">{{ $specialty }}</p>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="text-[10px] font-mono text-zinc-500 uppercase block">Acquisition Price</span>
                                <span class="text-lg font-mono font-black {{ $canAfford ? 'text-amber-400' : 'text-zinc-400' }}">
                                    {{ number_format($price) }} <span class="text-xs font-normal">CR</span>
                                </span>
                            </div>
                        </div>

                        <!-- 5 Telemetry Performance Bars -->
                        <div class="space-y-2.5 my-5 bg-zinc-950/70 p-4 rounded border border-zinc-800/80">
                            <!-- Speed -->
                            <div>
                                <div class="flex justify-between text-xs font-mono mb-1">
                                    <span class="text-zinc-400">Top Speed</span>
                                    <span class="text-white font-bold">{{ $car->speed }} / 100</span>
                                </div>
                                <div class="w-full bg-zinc-900 rounded h-1.5 overflow-hidden">
                                    <div class="bg-red-500 h-full rounded" style="width: {{ $car->speed }}%"></div>
                                </div>
                            </div>

                            <!-- Acceleration -->
                            <div>
                                <div class="flex justify-between text-xs font-mono mb-1">
                                    <span class="text-zinc-400">Acceleration</span>
                                    <span class="text-white font-bold">{{ $car->acceleration }} / 100</span>
                                </div>
                                <div class="w-full bg-zinc-900 rounded h-1.5 overflow-hidden">
                                    <div class="bg-amber-500 h-full rounded" style="width: {{ $car->acceleration }}%"></div>
                                </div>
                            </div>

                            <!-- Handling -->
                            <div>
                                <div class="flex justify-between text-xs font-mono mb-1">
                                    <span class="text-zinc-400">Handling</span>
                                    <span class="text-white font-bold">{{ $car->handling }} / 100</span>
                                </div>
                                <div class="w-full bg-zinc-900 rounded h-1.5 overflow-hidden">
                                    <div class="bg-cyan-500 h-full rounded" style="width: {{ $car->handling }}%"></div>
                                </div>
                            </div>

                            <!-- Braking -->
                            <div>
                                <div class="flex justify-between text-xs font-mono mb-1">
                                    <span class="text-zinc-400">Braking</span>
                                    <span class="text-white font-bold">{{ $car->braking }} / 100</span>
                                </div>
                                <div class="w-full bg-zinc-900 rounded h-1.5 overflow-hidden">
                                    <div class="bg-blue-500 h-full rounded" style="width: {{ $car->braking }}%"></div>
                                </div>
                            </div>

                            <!-- Reliability -->
                            <div>
                                <div class="flex justify-between text-xs font-mono mb-1">
                                    <span class="text-zinc-400">Base Reliability</span>
                                    <span class="text-white font-bold">{{ $car->reliability }}%</span>
                                </div>
                                <div class="w-full bg-zinc-900 rounded h-1.5 overflow-hidden">
                                    <div class="bg-emerald-500 h-full rounded" style="width: {{ $car->reliability }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="pt-2 border-t border-zinc-800/80">
                        @if($isOwned)
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-mono text-zinc-400 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>Operating in your constructor fleet</span>
                                </span>
                                <a href="{{ route('garage.index') }}" class="px-3 py-1.5 rounded bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-mono font-bold uppercase transition">
                                    View in Garage &rarr;
                                </a>
                            </div>
                        @elseif($canAfford)
                            <form method="POST" action="{{ route('garage.buy', $car) }}" onsubmit="return confirm('Commission the {{ $car->name }} chassis to your constructor fleet for {{ number_format($price) }} CR?');">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full py-2.5 px-4 rounded bg-gradient-to-r from-orange-600 via-amber-500 to-orange-500 hover:from-orange-500 hover:to-amber-400 text-black font-mono font-black text-xs uppercase tracking-wider transition shadow-lg flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <span>🛒 COMMISSION CHASSIS — {{ number_format($price) }} CR</span>
                                    <span>&rarr;</span>
                                </button>
                            </form>
                        @else
                            <div class="flex items-center justify-between gap-3">
                                <button
                                    type="button"
                                    disabled
                                    class="w-full py-2.5 px-4 rounded bg-zinc-950 border border-zinc-800 text-zinc-500 font-mono font-bold text-xs uppercase tracking-wider cursor-not-allowed text-center"
                                >
                                    INSUFFICIENT FUNDS (NEED +{{ number_format($price - $team->credits) }} CR)
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-2 bg-zinc-900 border border-zinc-800 rounded p-8 text-center text-zinc-400 font-mono">
                    <p class="text-sm">No chassis blueprints currently available in the factory showroom catalog.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
