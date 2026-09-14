@extends('layouts.app')

@section('title', $team->name . ' - Paddock Command Hub')

@section('content')
<div class="space-y-6">
    <!-- Top Team Header / Constructor Banner -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 sm:p-7 shadow-xl relative overflow-hidden">
        <!-- Racing Livery Top Accent Stripe -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 via-amber-400 to-red-600"></div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <div class="flex items-center gap-2.5 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[11px] font-telemetry uppercase tracking-widest text-zinc-400">PADDOCK COMMAND DECK</span>
                    <span class="text-[10px] font-telemetry bg-zinc-800 text-zinc-300 px-2 py-0.5 rounded">SEASON {{ $team->current_season }}</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight uppercase">
                    {{ $team->name }}
                </h1>
                <p class="text-xs text-zinc-400 mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1">
                    <span>CHIEF PRINCIPAL: <strong class="text-zinc-200 font-semibold">{{ auth()->user()->name }}</strong></span>
                    <span class="text-zinc-600">&bull;</span>
                    <span>FLEET: <strong class="text-zinc-200 font-telemetry">{{ $totalCars }} Cars</strong></span>
                    <span class="text-zinc-600">&bull;</span>
                    <span>ROSTER: <strong class="text-zinc-200 font-telemetry">{{ $totalDrivers }} Drivers</strong></span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Championship Standing Card -->
                <a href="{{ route('standings') }}" class="bg-zinc-950/90 hover:bg-zinc-900 border border-zinc-800 hover:border-orange-500/50 transition-all rounded px-4 py-3 min-w-[140px] block group">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-telemetry font-bold text-zinc-400 uppercase tracking-wider group-hover:text-orange-400">CHAMPIONSHIP</span>
                        <span class="text-[10px] text-zinc-500 group-hover:text-orange-400">&rarr;</span>
                    </div>
                    <div class="text-xl sm:text-2xl font-telemetry font-black text-white mt-0.5 flex items-baseline gap-1.5">
                        <span class="text-orange-400">P{{ $season['player_constructor_rank'] }}</span>
                        <span class="text-xs text-zinc-400 font-normal">({{ $season['player_constructor_points'] }} PTS)</span>
                    </div>
                </a>

                <!-- Money Card -->
                <div class="bg-zinc-950/90 border border-zinc-800 rounded px-4 py-3 min-w-[140px]">
                    <div class="text-[10px] font-telemetry font-bold text-zinc-400 uppercase tracking-wider">TEAM TREASURY</div>
                    <div class="text-xl sm:text-2xl font-telemetry font-black text-amber-400 mt-0.5">
                        {{ number_format($team->money) }} <span class="text-xs text-zinc-500 font-normal">CR</span>
                    </div>
                </div>

                <!-- Reputation Card -->
                <div class="bg-zinc-950/90 border border-zinc-800 rounded px-4 py-3 min-w-[120px]">
                    <div class="text-[10px] font-telemetry font-bold text-zinc-400 uppercase tracking-wider">REPUTATION</div>
                    <div class="text-xl sm:text-2xl font-telemetry font-black text-cyan-400 mt-0.5">
                        {{ number_format($team->reputation) }} <span class="text-xs text-zinc-500 font-normal">REP</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Principal Onboarding Directives Checklist -->
    <div class="bg-zinc-900 border {{ $directives['is_complete'] && !$directives['is_bonus_claimed'] ? 'border-amber-500/60 shadow-amber-500/10' : 'border-zinc-800' }} rounded p-6 shadow-xl relative overflow-hidden transition-all">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-zinc-800 gap-3">
            <div class="flex items-center gap-3">
                <span class="w-2.5 h-2.5 rounded-full {{ $directives['is_complete'] ? 'bg-emerald-500' : 'bg-orange-500 animate-pulse' }}"></span>
                <div>
                    <h2 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span>Team Principal Directives</span>
                        @if($directives['is_bonus_claimed'])
                            <span class="text-[9px] font-telemetry bg-emerald-950 text-emerald-400 border border-emerald-500/40 px-2 py-0.5 rounded font-bold">
                                COMPLETED &bull; GRANT CLAIMED
                            </span>
                        @elseif($directives['is_complete'])
                            <span class="text-[9px] font-telemetry bg-amber-950 text-amber-300 border border-amber-500/50 px-2 py-0.5 rounded font-bold animate-pulse">
                                READY TO CLAIM GRANT
                            </span>
                        @else
                            <span class="text-[9px] font-telemetry bg-zinc-800 text-zinc-300 px-2 py-0.5 rounded font-bold">
                                {{ $directives['completed_count'] }} / {{ $directives['total'] }} MILESTONES
                            </span>
                        @endif
                    </h2>
                    <p class="text-[11px] text-zinc-400 mt-0.5">
                        Selesaikan persiapan awal konstruktor untuk mengklaim starter grant senilai <span class="text-amber-400 font-semibold font-telemetry">+5,000 CR</span> & <span class="text-cyan-400 font-semibold font-telemetry">+25 REP</span>.
                    </p>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="flex items-center gap-3 min-w-[180px]">
                <div class="w-full bg-zinc-950 rounded h-2 overflow-hidden border border-zinc-800">
                    <div class="bg-gradient-to-r from-orange-500 to-emerald-500 h-full transition-all duration-500" style="width: {{ $directives['progress_percent'] }}%"></div>
                </div>
                <span class="text-xs font-telemetry font-bold text-white shrink-0">{{ $directives['progress_percent'] }}%</span>
            </div>
        </div>

        <!-- Directives Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <!-- 1. Chassis Selection -->
            <div class="bg-zinc-950/80 border {{ $directives['has_active_car'] ? 'border-emerald-500/40 bg-emerald-950/10' : 'border-zinc-800' }} rounded p-3.5 flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-telemetry text-[10px] {{ $directives['has_active_car'] ? 'text-emerald-400 font-bold' : 'text-zinc-400' }}">
                            {{ $directives['has_active_car'] ? '✓ COMPLETED' : '01 // PENDING' }}
                        </span>
                    </div>
                    <div class="font-bold text-white mt-1">Chassis Fleet</div>
                    <div class="text-[11px] text-zinc-400 mt-0.5">Tetapkan mobil balap aktif tim.</div>
                </div>
                @if(!$directives['has_active_car'])
                    <a href="{{ route('garage.index') }}" class="text-[10px] text-orange-400 hover:text-orange-300 font-semibold underline mt-1">
                        Garasi &rarr;
                    </a>
                @endif
            </div>

            <!-- 2. Driver Lineup -->
            <div class="bg-zinc-950/80 border {{ $directives['has_primary_driver'] ? 'border-emerald-500/40 bg-emerald-950/10' : 'border-zinc-800' }} rounded p-3.5 flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-telemetry text-[10px] {{ $directives['has_primary_driver'] ? 'text-emerald-400 font-bold' : 'text-zinc-400' }}">
                            {{ $directives['has_primary_driver'] ? '✓ COMPLETED' : '02 // PENDING' }}
                        </span>
                    </div>
                    <div class="font-bold text-white mt-1">Lead Driver</div>
                    <div class="text-[11px] text-zinc-400 mt-0.5">Kontrak & assign pembalap utama.</div>
                </div>
                @if(!$directives['has_primary_driver'])
                    <a href="{{ route('drivers.index') }}" class="text-[10px] text-cyan-400 hover:text-cyan-300 font-semibold underline mt-1">
                        Drivers &rarr;
                    </a>
                @endif
            </div>

            <!-- 3. R&D Engineering Upgrade -->
            <div class="bg-zinc-950/80 border {{ $directives['has_upgrades'] ? 'border-emerald-500/40 bg-emerald-950/10' : 'border-zinc-800' }} rounded p-3.5 flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-telemetry text-[10px] {{ $directives['has_upgrades'] ? 'text-emerald-400 font-bold' : 'text-zinc-400' }}">
                            {{ $directives['has_upgrades'] ? '✓ COMPLETED' : '03 // PENDING' }}
                        </span>
                    </div>
                    <div class="font-bold text-white mt-1">R&D Upgrade</div>
                    <div class="text-[11px] text-zinc-400 mt-0.5">Pasang 1 modifikasi mesin/aero.</div>
                </div>
                @if(!$directives['has_upgrades'])
                    <a href="{{ $activeCar ? route('garage.show', $activeCar) : route('garage.index') }}" class="text-[10px] text-amber-400 hover:text-amber-300 font-semibold underline mt-1">
                        Upgrade &rarr;
                    </a>
                @endif
            </div>

            <!-- 4. First Grand Prix -->
            <div class="bg-zinc-950/80 border {{ $directives['has_completed_race'] ? 'border-emerald-500/40 bg-emerald-950/10' : 'border-zinc-800' }} rounded p-3.5 flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-telemetry text-[10px] {{ $directives['has_completed_race'] ? 'text-emerald-400 font-bold' : 'text-zinc-400' }}">
                            {{ $directives['has_completed_race'] ? '✓ COMPLETED' : '04 // PENDING' }}
                        </span>
                    </div>
                    <div class="font-bold text-white mt-1">First Grand Prix</div>
                    <div class="text-[11px] text-zinc-400 mt-0.5">Ikuti 1 sesi balapan kejuaraan.</div>
                </div>
                @if(!$directives['has_completed_race'])
                    <a href="{{ route('races.index') }}" class="text-[10px] text-red-400 hover:text-red-300 font-semibold underline mt-1">
                        Races &rarr;
                    </a>
                @endif
            </div>
        </div>

        <!-- Bonus Claim Banner (If complete and unclaimed) -->
        @if($directives['is_complete'] && !$directives['is_bonus_claimed'])
            <div class="mt-4 pt-4 border-t border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-amber-950/20 -mx-6 -mb-6 p-6">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">&#127881;</span>
                    <div>
                        <div class="font-bold text-amber-300 uppercase text-xs">All Directives Completed!</div>
                        <div class="text-[11px] text-zinc-300 mt-0.5">
                            Klaim dana hibah operasional awal dari komisi kejuaraan untuk tim Anda.
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('tutorial.claim-bonus') }}">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded bg-amber-500 hover:bg-amber-400 text-black text-xs font-bold uppercase tracking-wider transition shadow-lg flex items-center gap-2 cursor-pointer">
                        <span>Claim +5,000 CR & +25 REP</span>
                        <span>&rarr;</span>
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Race Readiness & Telemetry Status Panel (Two-Car Entry Support) -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-5 border-b border-zinc-800 gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full {{ $isRaceReady ? 'bg-emerald-500 animate-ping' : 'bg-amber-500' }}"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-white">RACE READINESS & DUAL-CAR TELEMETRY</span>
            </div>
            <div class="flex items-center gap-2">
                @if($isRaceReady)
                    <span class="text-[10px] font-telemetry font-bold bg-emerald-950 border border-emerald-500/40 text-emerald-300 px-2.5 py-1 rounded">
                        STATUS: GREEN FLAG // READY TO COMPETE
                    </span>
                @else
                    <span class="text-[10px] font-telemetry font-bold bg-amber-950 border border-amber-500/40 text-amber-300 px-2.5 py-1 rounded">
                        STATUS: INCOMPLETE SETUP // CONFIGURE ASSETS
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- CAR #1 & DRIVER #1 (PRIMARY ENTRY) -->
            <div class="bg-zinc-950/80 border border-zinc-800/90 rounded p-4 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-zinc-800/80">
                        <div class="flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded bg-orange-950 border border-orange-500/40 text-orange-400 font-telemetry font-black text-[10px]">
                                ENTRY #1
                            </span>
                            <span class="text-[11px] font-bold text-white uppercase">PRIMARY CAR</span>
                        </div>
                        <a href="{{ route('garage.index') }}" class="text-[10px] text-zinc-400 hover:text-orange-400 font-telemetry underline">
                            GARAGE &rarr;
                        </a>
                    </div>

                    <!-- Car Slot 1 -->
                    @if($car1)
                        <div class="mb-3">
                            <div class="flex items-baseline justify-between">
                                <span class="text-base font-black text-white uppercase tracking-tight">{{ $car1->name }}</span>
                                <span class="text-[10px] font-telemetry font-bold text-orange-400 bg-orange-950/60 px-1.5 py-0.5 rounded border border-orange-500/30">
                                    LVL {{ $car1->level }}
                                </span>
                            </div>

                            <!-- Gauges -->
                            <div class="grid grid-cols-5 gap-1 text-center text-xs font-telemetry mt-2">
                                <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800">
                                    <div class="text-[8px] text-zinc-500 uppercase">SPD</div>
                                    <div class="font-bold text-white text-[11px]">{{ $car1->speed }}</div>
                                </div>
                                <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800">
                                    <div class="text-[8px] text-zinc-500 uppercase">ACC</div>
                                    <div class="font-bold text-white text-[11px]">{{ $car1->acceleration }}</div>
                                </div>
                                <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800">
                                    <div class="text-[8px] text-zinc-500 uppercase">HND</div>
                                    <div class="font-bold text-white text-[11px]">{{ $car1->handling }}</div>
                                </div>
                                <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800">
                                    <div class="text-[8px] text-zinc-500 uppercase">BRK</div>
                                    <div class="font-bold text-white text-[11px]">{{ $car1->braking }}</div>
                                </div>
                                <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800">
                                    <div class="text-[8px] text-zinc-500 uppercase">REL</div>
                                    <div class="font-bold text-emerald-400 text-[11px]">{{ $car1->reliability }}%</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="py-4 text-center text-xs text-zinc-500 bg-zinc-900/50 rounded border border-zinc-800/60 mb-3">
                            No car assigned to Slot 1.<br>
                            <a href="{{ route('garage.index') }}" class="text-orange-400 underline font-semibold mt-1 inline-block">Assign Car #1</a>
                        </div>
                    @endif

                    <!-- Driver Slot 1 -->
                    <div class="pt-2 border-t border-zinc-800/60">
                        <div class="flex items-center justify-between text-[10px] text-zinc-400 uppercase font-telemetry mb-1.5">
                            <span>COCKPIT PILOT 1</span>
                            @if($driver1)
                                <a href="{{ route('drivers.show', $driver1) }}" class="text-cyan-400 hover:underline">Profile &rarr;</a>
                            @endif
                        </div>
                        @if($driver1)
                            <div class="flex items-center justify-between bg-zinc-900/80 border border-zinc-800 p-2 rounded">
                                <div>
                                    <div class="font-bold text-white text-xs uppercase">{{ $driver1->name }}</div>
                                    <div class="text-[10px] font-telemetry text-zinc-400">
                                        Pace: <strong class="text-zinc-200">{{ $driver1->pace }}</strong> &bull; Cons: <strong class="text-zinc-200">{{ $driver1->consistency }}</strong>
                                    </div>
                                </div>
                                <span class="text-[10px] font-telemetry font-bold text-cyan-400 bg-cyan-950/60 px-1.5 py-0.5 rounded border border-cyan-500/30">
                                    {{ number_format($driver1->salary) }} CR
                                </span>
                            </div>
                        @else
                            <div class="text-xs text-zinc-500 py-2 text-center bg-zinc-900/50 rounded border border-zinc-800/60">
                                No lead driver contracted. <a href="{{ route('drivers.market') }}" class="text-cyan-400 underline">Scout Market</a>
                            </div>
                        @endif
                    </div>
                </div>

                @if($car1)
                    <div class="mt-3 pt-2.5 border-t border-zinc-900 flex justify-between items-center text-xs">
                        <span class="text-[10px] text-zinc-400 font-telemetry">{{ $car1->upgrades->count() }} R&D Upgrades</span>
                        <a href="{{ route('garage.show', $car1) }}" class="text-xs text-orange-400 hover:text-orange-300 font-semibold">
                            Tune Car &rarr;
                        </a>
                    </div>
                @endif
            </div>

            <!-- CAR #2 & DRIVER #2 (SECONDARY ENTRY) -->
            <div class="bg-zinc-950/80 border border-zinc-800/90 rounded p-4 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-zinc-800/80">
                        <div class="flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded bg-amber-950 border border-amber-500/40 text-amber-400 font-telemetry font-black text-[10px]">
                                ENTRY #2
                            </span>
                            <span class="text-[11px] font-bold text-white uppercase">SECONDARY CAR</span>
                        </div>
                        <a href="{{ route('garage.dealership') }}" class="text-[10px] text-zinc-400 hover:text-amber-400 font-telemetry underline">
                            SHOWROOM &rarr;
                        </a>
                    </div>

                    <!-- Car Slot 2 -->
                    @if($car2)
                        <div class="mb-3">
                            <div class="flex items-baseline justify-between">
                                <span class="text-base font-black text-white uppercase tracking-tight">{{ $car2->name }}</span>
                                <span class="text-[10px] font-telemetry font-bold text-amber-400 bg-amber-950/60 px-1.5 py-0.5 rounded border border-amber-500/30">
                                    LVL {{ $car2->level }}
                                </span>
                            </div>

                            <!-- Gauges -->
                            <div class="grid grid-cols-5 gap-1 text-center text-xs font-telemetry mt-2">
                                <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800">
                                    <div class="text-[8px] text-zinc-500 uppercase">SPD</div>
                                    <div class="font-bold text-white text-[11px]">{{ $car2->speed }}</div>
                                </div>
                                <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800">
                                    <div class="text-[8px] text-zinc-500 uppercase">ACC</div>
                                    <div class="font-bold text-white text-[11px]">{{ $car2->acceleration }}</div>
                                </div>
                                <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800">
                                    <div class="text-[8px] text-zinc-500 uppercase">HND</div>
                                    <div class="font-bold text-white text-[11px]">{{ $car2->handling }}</div>
                                </div>
                                <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800">
                                    <div class="text-[8px] text-zinc-500 uppercase">BRK</div>
                                    <div class="font-bold text-white text-[11px]">{{ $car2->braking }}</div>
                                </div>
                                <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800">
                                    <div class="text-[8px] text-zinc-500 uppercase">REL</div>
                                    <div class="font-bold text-emerald-400 text-[11px]">{{ $car2->reliability }}%</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="py-4 text-center text-xs text-zinc-500 bg-zinc-900/50 rounded border border-zinc-800/60 mb-3">
                            Slot 2 Chassis Standby.<br>
                            <a href="{{ route('garage.dealership') }}" class="text-amber-400 underline font-semibold mt-1 inline-block">Buy 2nd Chassis at Showroom</a>
                        </div>
                    @endif

                    <!-- Driver Slot 2 -->
                    <div class="pt-2 border-t border-zinc-800/60">
                        <div class="flex items-center justify-between text-[10px] text-zinc-400 uppercase font-telemetry mb-1.5">
                            <span>COCKPIT PILOT 2</span>
                            @if($driver2)
                                <a href="{{ route('drivers.show', $driver2) }}" class="text-amber-400 hover:underline">Profile &rarr;</a>
                            @endif
                        </div>
                        @if($driver2)
                            <div class="flex items-center justify-between bg-zinc-900/80 border border-zinc-800 p-2 rounded">
                                <div>
                                    <div class="font-bold text-white text-xs uppercase">{{ $driver2->name }}</div>
                                    <div class="text-[10px] font-telemetry text-zinc-400">
                                        Pace: <strong class="text-zinc-200">{{ $driver2->pace }}</strong> &bull; Cons: <strong class="text-zinc-200">{{ $driver2->consistency }}</strong>
                                    </div>
                                </div>
                                <span class="text-[10px] font-telemetry font-bold text-amber-400 bg-amber-950/60 px-1.5 py-0.5 rounded border border-amber-500/30">
                                    {{ number_format($driver2->salary) }} CR
                                </span>
                            </div>
                        @else
                            <div class="text-xs text-zinc-500 py-2 text-center bg-zinc-900/50 rounded border border-zinc-800/60">
                                Slot 2 Driver empty. <a href="{{ route('drivers.market') }}" class="text-amber-400 underline">Hire 2nd Driver</a>
                            </div>
                        @endif
                    </div>
                </div>

                @if($car2)
                    <div class="mt-3 pt-2.5 border-t border-zinc-900 flex justify-between items-center text-xs">
                        <span class="text-[10px] text-zinc-400 font-telemetry">{{ $car2->upgrades->count() }} R&D Upgrades</span>
                        <a href="{{ route('garage.show', $car2) }}" class="text-xs text-amber-400 hover:text-amber-300 font-semibold">
                            Tune Car &rarr;
                        </a>
                    </div>
                @endif
            </div>

            <!-- Commercial Partnerships Card -->
            <div class="bg-zinc-950/80 border border-zinc-800/90 rounded p-4 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-zinc-800/80">
                        <div class="flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded bg-emerald-950 border border-emerald-500/40 text-emerald-400 font-telemetry font-black text-[10px]">
                                COMMERCIAL
                            </span>
                            <span class="text-[11px] font-bold text-white uppercase">SPONSOR HUB</span>
                        </div>
                        <a href="{{ route('sponsors.index') }}" class="text-[10px] text-zinc-400 hover:text-emerald-400 font-telemetry underline">
                            CONTRACTS &rarr;
                        </a>
                    </div>

                    @if($activeSponsors->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($activeSponsors as $activeSp)
                                <div class="bg-zinc-900/90 border border-zinc-800 p-2.5 rounded text-xs font-telemetry flex items-center justify-between">
                                    <div>
                                        <div class="font-bold text-white uppercase text-[11px]">{{ $activeSp->sponsor->name }}</div>
                                        <div class="text-[10px] text-amber-400 mt-0.5">{{ $activeSp->sponsor->objectiveLabel() }}</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-950/80 border border-amber-500/30 text-amber-300 font-bold">
                                            {{ $activeSp->races_remaining }} GP Left
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-7 text-center text-xs text-zinc-500 font-telemetry bg-zinc-900/50 rounded border border-zinc-800/60">
                            No corporate sponsors signed.<br>
                            <a href="{{ route('sponsors.index') }}" class="text-amber-400 hover:text-amber-300 underline font-semibold mt-1.5 inline-block">
                                Sign Commercial Deals &rarr;
                            </a>
                        </div>
                    @endif
                </div>

                <div class="mt-3 pt-2.5 border-t border-zinc-900 flex justify-between items-center text-xs">
                    <span class="text-[10px] text-zinc-400 font-telemetry">Active Deals: {{ $activeSponsors->count() }}</span>
                    <a href="{{ route('sponsors.index') }}" class="text-xs text-amber-400 hover:text-amber-300 font-semibold">
                        Sponsor Marketplace &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions / Command Deck Grid (8 Balanced Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 01. Garage Fleet -->
        <a href="{{ route('garage.index') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-orange-500/60 rounded p-4 transition-all shadow hover:shadow-orange-500/10 block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-orange-950 border border-orange-500/30 flex items-center justify-center text-orange-400 font-telemetry font-bold text-xs">
                    01
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-orange-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-orange-400 transition-colors">
                Garage Fleet
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                Chassis telemetry, slot assignment & R&D upgrades.
            </p>
        </a>

        <!-- 02. Chassis Showroom -->
        <a href="{{ route('garage.dealership') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-amber-500/60 rounded p-4 transition-all shadow hover:shadow-amber-500/10 block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-amber-950 border border-amber-500/30 flex items-center justify-center text-amber-400 font-telemetry font-bold text-xs">
                    02
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-amber-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-amber-400 transition-colors">
                Chassis Showroom
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                Dealership to purchase 2nd car for dual-car grid entry.
            </p>
        </a>

        <!-- 03. Driver Lineup & Market -->
        <a href="{{ route('drivers.index') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-cyan-500/60 rounded p-4 transition-all shadow hover:shadow-cyan-500/10 block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-cyan-950 border border-cyan-500/30 flex items-center justify-center text-cyan-400 font-telemetry font-bold text-xs">
                    03
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-cyan-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-cyan-400 transition-colors">
                Driver Lineup & Market
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                Roster contracts, Car 1/2 seats & Legend scouts.
            </p>
        </a>

        <!-- 04. Commercial Sponsors -->
        <a href="{{ route('sponsors.index') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-emerald-500/60 rounded p-4 transition-all shadow hover:shadow-emerald-500/10 block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-emerald-950 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-telemetry font-bold text-xs">
                    04
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-emerald-400 transition-colors">
                Sponsor Hub
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                Corporate grants, race payouts & objective bonuses.
            </p>
        </a>

        <!-- 05. Grand Prix Calendar -->
        <a href="{{ route('races.index') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-red-500/60 rounded p-4 transition-all shadow hover:shadow-red-500/10 block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-red-950 border border-red-500/30 flex items-center justify-center text-red-400 font-telemetry font-bold text-xs">
                    05
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-red-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-red-400 transition-colors">
                Grand Prix Hub
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                Race calendar, pit-wall telemetry & live simulation.
            </p>
        </a>

        <!-- 06. Championship Standings -->
        <a href="{{ route('standings') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-amber-400/60 rounded p-4 transition-all shadow hover:shadow-amber-400/10 block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-amber-950 border border-amber-400/30 flex items-center justify-center text-amber-300 font-telemetry font-bold text-xs">
                    06
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-amber-300 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-amber-300 transition-colors">
                Championship Standings
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                Constructors & Drivers championship points table.
            </p>
        </a>

        <!-- 07. Trophy Room & Hall of Fame 🏆 -->
        <a href="{{ route('hall-of-fame.index') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-yellow-500/60 rounded p-4 transition-all shadow hover:shadow-yellow-500/10 block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-yellow-950 border border-yellow-500/30 flex items-center justify-center text-yellow-400 font-telemetry font-bold text-xs">
                    07
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-yellow-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-yellow-400 transition-colors flex items-center gap-1.5">
                <span>Trophy Room 🏆</span>
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                Championship titles, race winner records & Hall of Fame.
            </p>
        </a>

        <!-- 08. Race Archives & History -->
        <a href="{{ route('races.history') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-purple-500/60 rounded p-4 transition-all shadow hover:shadow-purple-500/10 block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-purple-950 border border-purple-500/30 flex items-center justify-center text-purple-400 font-telemetry font-bold text-xs">
                    08
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-purple-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-purple-400 transition-colors">
                Race Archives
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                Historic debriefs, lap telemetry & podium records.
            </p>
        </a>
    </div>
</div>
@endsection

