@extends('layouts.app')

@section('title', $team->name . ' - Paddock Command Hub')

@section('content')
<div class="space-y-6">
    <!-- Top Team Header / Constructor Banner -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 sm:p-8 shadow-xl relative overflow-hidden">
        <!-- Racing Livery Top Accent Stripe -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 via-amber-400 to-red-600"></div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-2.5 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[11px] font-telemetry uppercase tracking-widest text-zinc-400">OFFICIAL CONSTRUCTOR PADDOCK</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight uppercase">
                    {{ $team->name }}
                </h1>
                <p class="text-xs text-zinc-400 mt-1.5">
                    CHIEF MANAGER: <span class="text-zinc-200 font-semibold">{{ auth()->user()->name }}</span> &bull; STATUS: <span class="text-emerald-400 font-medium">ACTIVE IN CHAMPIONSHIP</span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Championship Standing Card -->
                <a href="{{ route('standings') }}" class="bg-zinc-950/90 hover:bg-zinc-900 border border-zinc-800 hover:border-orange-500/50 transition-all rounded px-4 py-3 min-w-[150px] block group">
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
                        {{ number_format($team->reputation) }} <span class="text-xs text-zinc-500 font-normal">PTS</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Principal Onboarding Directives Checklist -->
    <div class="bg-zinc-900 border {{ $directives['is_complete'] && !$directives['is_bonus_claimed'] ? 'border-amber-500/60 shadow-amber-500/10' : 'border-zinc-800' }} rounded p-6 shadow-xl relative overflow-hidden transition-all">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-zinc-800 gap-3">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full {{ $directives['is_complete'] ? 'bg-emerald-500' : 'bg-orange-500 animate-pulse' }}"></span>
                <div>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span>Team Principal Directives</span>
                        @if($directives['is_bonus_claimed'])
                            <span class="text-[10px] font-telemetry bg-emerald-950 text-emerald-400 border border-emerald-500/40 px-2 py-0.5 rounded font-bold">
                                COMPLETED &bull; GRANT CLAIMED
                            </span>
                        @elseif($directives['is_complete'])
                            <span class="text-[10px] font-telemetry bg-amber-950 text-amber-300 border border-amber-500/50 px-2 py-0.5 rounded font-bold animate-pulse">
                                READY TO CLAIM GRANT
                            </span>
                        @else
                            <span class="text-[10px] font-telemetry bg-zinc-800 text-zinc-300 px-2 py-0.5 rounded">
                                {{ $directives['completed_count'] }} / {{ $directives['total'] }} MILESTONES
                            </span>
                        @endif
                    </h2>
                    <p class="text-[11px] text-zinc-400 mt-0.5">
                        Selesaikan langkah awal persiapan konstruktor untuk mengklaim starter grant senilai <span class="text-amber-400 font-semibold font-telemetry">+5,000 CR</span> & <span class="text-cyan-400 font-semibold font-telemetry">+25 REP</span>.
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
                        <span class="font-telemetry text-[11px] {{ $directives['has_active_car'] ? 'text-emerald-400 font-bold' : 'text-zinc-400' }}">
                            {{ $directives['has_active_car'] ? '✓ DONE' : '01 // PENDING' }}
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
                        <span class="font-telemetry text-[11px] {{ $directives['has_primary_driver'] ? 'text-emerald-400 font-bold' : 'text-zinc-400' }}">
                            {{ $directives['has_primary_driver'] ? '✓ DONE' : '02 // PENDING' }}
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
                        <span class="font-telemetry text-[11px] {{ $directives['has_upgrades'] ? 'text-emerald-400 font-bold' : 'text-zinc-400' }}">
                            {{ $directives['has_upgrades'] ? '✓ DONE' : '03 // PENDING' }}
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
                        <span class="font-telemetry text-[11px] {{ $directives['has_completed_race'] ? 'text-emerald-400 font-bold' : 'text-zinc-400' }}">
                            {{ $directives['has_completed_race'] ? '✓ DONE' : '04 // PENDING' }}
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

    <!-- Race Readiness & Telemetry Status Panel -->
    <div class="bg-zinc-900/90 border border-zinc-800 rounded p-6 shadow-lg">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-5 border-b border-zinc-800/80 gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full {{ $isRaceReady ? 'bg-emerald-500' : 'bg-amber-500' }} animate-ping"></span>
                <span class="text-xs font-semibold uppercase tracking-wider text-white">RACE READINESS TELEMETRY</span>
            </div>
            <div class="flex items-center gap-2">
                @if($isRaceReady)
                    <span class="text-[11px] font-telemetry font-bold bg-emerald-950 border border-emerald-500/40 text-emerald-300 px-2.5 py-1 rounded">
                        STATUS: GREEN FLAG // READY TO COMPETE
                    </span>
                @else
                    <span class="text-[11px] font-telemetry font-bold bg-amber-950 border border-amber-500/40 text-amber-300 px-2.5 py-1 rounded">
                        STATUS: INCOMPLETE SETUP // CONFIGURE ASSETS
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Active Vehicle Card -->
            <div class="bg-zinc-950/80 border border-zinc-800 rounded p-4 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-telemetry uppercase tracking-wider text-orange-400 font-bold">PRIMARY RACE CHASSIS</span>
                        <a href="{{ route('garage.index') }}" class="text-[11px] text-zinc-400 hover:text-orange-400 underline underline-offset-2">
                            GARAGE FLEET &rarr;
                        </a>
                    </div>

                    @if($activeCar)
                        <div class="flex items-baseline justify-between mb-3">
                            <span class="text-lg font-bold text-white uppercase">{{ $activeCar->name }}</span>
                            <span class="text-xs font-telemetry font-bold bg-orange-950 border border-orange-500/40 text-orange-300 px-2 py-0.5 rounded">
                                LEVEL {{ $activeCar->level }}
                            </span>
                        </div>

                        <!-- Quick Telemetry Gauges -->
                        <div class="grid grid-cols-5 gap-1.5 text-center text-xs font-telemetry">
                            <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">SPD</div>
                                <div class="font-bold text-white">{{ $activeCar->speed }}</div>
                            </div>
                            <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">ACC</div>
                                <div class="font-bold text-white">{{ $activeCar->acceleration }}</div>
                            </div>
                            <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">HND</div>
                                <div class="font-bold text-white">{{ $activeCar->handling }}</div>
                            </div>
                            <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">BRK</div>
                                <div class="font-bold text-white">{{ $activeCar->braking }}</div>
                            </div>
                            <div class="bg-zinc-900 p-1.5 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">REL</div>
                                <div class="font-bold text-emerald-400">{{ $activeCar->reliability }}%</div>
                            </div>
                        </div>
                    @else
                        <div class="py-6 text-center text-xs text-zinc-500">
                            No active car selected. <a href="{{ route('garage.index') }}" class="text-orange-400 underline">Select a vehicle</a>
                        </div>
                    @endif
                </div>

                @if($activeCar)
                    <div class="mt-3 pt-3 border-t border-zinc-900 flex justify-between items-center text-xs">
                        <span class="text-[11px] text-zinc-400 font-telemetry">Total Fleet: {{ $totalCars }} car(s)</span>
                        <a href="{{ route('garage.show', $activeCar) }}" class="text-xs text-orange-400 hover:text-orange-300 font-semibold">
                            Technical Sheet &rarr;
                        </a>
                    </div>
                @endif
            </div>

            <!-- Lead Driver Card -->
            <div class="bg-zinc-950/80 border border-zinc-800 rounded p-4 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-telemetry uppercase tracking-wider text-cyan-400 font-bold">LEAD RACE DRIVER</span>
                        <a href="{{ route('drivers.index') }}" class="text-[11px] text-zinc-400 hover:text-cyan-400 underline underline-offset-2">
                            DRIVER ROSTER &rarr;
                        </a>
                    </div>

                    @if($primaryDriver)
                        <div class="flex items-baseline justify-between mb-3">
                            <span class="text-lg font-bold text-white uppercase">{{ $primaryDriver->name }}</span>
                            <span class="text-xs font-telemetry font-bold text-cyan-300">
                                {{ number_format($primaryDriver->salary) }} <span class="text-[10px] text-zinc-500">CR/RACE</span>
                            </span>
                        </div>

                        <!-- Driver Stats -->
                        <div class="grid grid-cols-4 gap-2 text-center text-xs font-telemetry">
                            <div class="bg-zinc-900 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Pace</div>
                                <div class="font-bold text-white">{{ $primaryDriver->pace }}</div>
                            </div>
                            <div class="bg-zinc-900 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Corner</div>
                                <div class="font-bold text-white">{{ $primaryDriver->cornering }}</div>
                            </div>
                            <div class="bg-zinc-900 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Cons</div>
                                <div class="font-bold text-white">{{ $primaryDriver->consistency }}</div>
                            </div>
                            <div class="bg-zinc-900 p-2 rounded border border-zinc-800/80">
                                <div class="text-[9px] text-zinc-500 uppercase">Exp</div>
                                <div class="font-bold text-white">{{ $primaryDriver->experience }}</div>
                            </div>
                        </div>
                    @else
                        <div class="py-6 text-center text-xs text-zinc-500">
                            No contracted driver available. <a href="{{ route('drivers.market') }}" class="text-cyan-400 underline">Scout Driver Market</a>
                        </div>
                    @endif
                </div>

                @if($primaryDriver)
                    <div class="mt-3 pt-3 border-t border-zinc-900 flex justify-between items-center text-xs">
                        <span class="text-[11px] text-zinc-400 font-telemetry">Roster: {{ $totalDrivers }} driver(s)</span>
                        <a href="{{ route('drivers.show', $primaryDriver) }}" class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold">
                            Profile Sheet &rarr;
                        </a>
                    </div>
                @endif
            </div>

            <!-- Commercial Partnerships Card -->
            <div class="bg-zinc-950/80 border border-zinc-800 rounded p-4 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-telemetry uppercase tracking-wider text-amber-400 font-bold">COMMERCIAL PARTNERS</span>
                        <a href="{{ route('sponsors.index') }}" class="text-[11px] text-zinc-400 hover:text-amber-400 underline underline-offset-2">
                            SPONSOR HUB &rarr;
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
                        <div class="py-6 text-center text-xs text-zinc-500 font-telemetry">
                            No corporate sponsors signed yet.<br>
                            <a href="{{ route('sponsors.index') }}" class="text-amber-400 hover:text-amber-300 underline font-semibold mt-1 inline-block">
                                Sign Commercial Deals &rarr;
                            </a>
                        </div>
                    @endif
                </div>

                <div class="mt-3 pt-3 border-t border-zinc-900 flex justify-between items-center text-xs">
                    <span class="text-[11px] text-zinc-400 font-telemetry">Active Deals: {{ $activeSponsors->count() }}</span>
                    <a href="{{ route('sponsors.index') }}" class="text-xs text-amber-400 hover:text-amber-300 font-semibold">
                        Marketplace &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions / Command Deck Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Garage Link -->
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
                Chassis telemetry and setup.
            </p>
        </a>

        <!-- Driver Lineup Link -->
        <a href="{{ route('drivers.index') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-cyan-500/60 rounded p-4 transition-all shadow hover:shadow-cyan-500/10 block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-cyan-950 border border-cyan-500/30 flex items-center justify-center text-cyan-400 font-telemetry font-bold text-xs">
                    02
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-cyan-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-cyan-400 transition-colors">
                Driver Lineup
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                Roster, lead seat & market.
            </p>
        </a>

        <!-- Commercial Sponsors Link -->
        <a href="{{ route('sponsors.index') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-amber-500/60 rounded p-4 transition-all shadow hover:shadow-amber-500/10 block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-amber-950 border border-amber-500/30 flex items-center justify-center text-amber-400 font-telemetry font-bold text-xs">
                    03
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-amber-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-amber-400 transition-colors">
                Sponsor Hub
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                Corporate grants & bonuses.
            </p>
        </a>

        <!-- Race Hub -->
        <a href="{{ route('races.index') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-red-500/60 rounded p-4 transition-all shadow hover:shadow-red-500/10 block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-red-950 border border-red-500/30 flex items-center justify-center text-red-400 font-telemetry font-bold text-xs">
                    04
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-red-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-red-400 transition-colors">
                Grand Prix Hub
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                Race calendar & simulation.
            </p>
        </a>

        <!-- Championship Standings -->
        <a href="{{ route('standings') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-amber-400/60 rounded p-4 transition-all shadow hover:shadow-amber-400/10 block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-amber-950 border border-amber-400/30 flex items-center justify-center text-amber-300 font-telemetry font-bold text-xs">
                    05
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-amber-300 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-amber-300 transition-colors">
                Championship Standings
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                Constructors & Drivers ladder.
            </p>
        </a>

        <!-- Engineering / Upgrades -->
        <a href="{{ $activeCar ? route('garage.show', $activeCar) : route('garage.index') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-emerald-500/60 rounded p-4 transition-all shadow hover:shadow-emerald-500/10 block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-emerald-950 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-telemetry font-bold text-xs">
                    06
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-emerald-400 transition-colors">
                R&D Upgrades
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                5 modular vehicle packages.
            </p>
        </a>

        <!-- Race Archives & History -->
        <a href="{{ route('races.history') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-purple-500/60 rounded p-4 transition-all shadow hover:shadow-purple-500/10 block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-purple-950 border border-purple-500/30 flex items-center justify-center text-purple-400 font-telemetry font-bold text-xs">
                    07
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-purple-400 group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-purple-400 transition-colors">
                Race Archives
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                Podiums, points & history.
            </p>
        </a>

        <!-- Handbook Guide -->
        <a href="{{ route('guide') }}" class="group bg-zinc-900 border border-zinc-800 hover:border-zinc-600 rounded p-4 transition-all shadow block">
            <div class="flex items-center justify-between mb-2">
                <span class="w-7 h-7 rounded bg-zinc-950 border border-zinc-700 flex items-center justify-center text-zinc-400 font-telemetry font-bold text-xs">
                    08
                </span>
                <span class="text-xs text-zinc-500 group-hover:text-white group-hover:translate-x-1 transition-all">&rarr;</span>
            </div>
            <h2 class="text-xs font-bold text-white uppercase tracking-wide group-hover:text-white transition-colors">
                Team Handbook
            </h2>
            <p class="text-[11px] text-zinc-400 mt-1 leading-relaxed">
                Operations & physics guide.
            </p>
        </a>
    </div>
</div>
@endsection
