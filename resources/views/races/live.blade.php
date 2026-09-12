@extends('layouts.app')

@section('title', 'Live Pit-Wall Telemetry - ' . $race->name)

@section('content')
<div x-data="{
    playbackSpeed: 1,
    currentLap: 1,
    totalLaps: {{ max(1, (int)$race->laps) }},
    isFinished: false,
    timer: null,

    init() {
        this.startTicker();
    },

    setSpeed(speed) {
        this.playbackSpeed = speed;
        if (!this.isFinished) {
            this.startTicker();
        }
    },

    startTicker() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
        if (this.currentLap >= this.totalLaps) {
            this.isFinished = true;
            return;
        }
        const intervalMs = Math.round(1000 / this.playbackSpeed);
        this.timer = setInterval(() => {
            if (this.currentLap < this.totalLaps) {
                this.currentLap++;
                this.$nextTick(() => {
                    const feed = document.getElementById('telemetry-radio-feed');
                    if (feed) {
                        feed.scrollTop = feed.scrollHeight;
                    }
                });
            }
            if (this.currentLap >= this.totalLaps) {
                this.isFinished = true;
                clearInterval(this.timer);
                this.timer = null;
            }
        }, intervalMs);
    },

    instantSkip() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
        this.currentLap = this.totalLaps;
        this.isFinished = true;
        this.$nextTick(() => {
            const feed = document.getElementById('telemetry-radio-feed');
            if (feed) {
                feed.scrollTop = feed.scrollHeight;
            }
        });
    }
}" class="space-y-6">
    <!-- Top Telemetry Header Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-600 via-orange-500 to-amber-400"></div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1 text-xs font-mono text-zinc-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-red-400 transition-colors">PADDOCK</a>
                    <span>/</span>
                    <a href="{{ route('races.index') }}" class="hover:text-red-400 transition-colors uppercase">CALENDAR</a>
                    <span>/</span>
                    <span class="text-red-400 font-bold uppercase">LIVE TELEMETRY // {{ $race->name }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full" :class="isFinished ? 'bg-purple-500' : 'bg-emerald-500 animate-pulse'"></span>
                    <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight">
                        {{ $race->name }} &bull; Grand Prix
                    </h1>
                </div>
                <p class="text-xs text-zinc-400 mt-1 font-mono">
                    Official race classification & lap-by-lap telemetry feed &bull; {{ $race->laps }} Laps &bull; {{ $race->location }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @php
                    $pCompound = $simulation['tactics']['tire_compound'] ?? ($simulation['tire_compound'] ?? 'medium');
                    $pMode = $simulation['tactics']['driving_mode'] ?? ($simulation['driving_mode'] ?? 'balanced');
                    $pRes1 = $simulation['player_result_1'] ?? ($simulation['player_result'] ?? ['position' => 10, 'driver_name' => 'Driver 1', 'car_name' => 'Car 1', 'total_time' => '--', 'gap' => 'LEADER']);
                    $pRes2 = $simulation['player_result_2'] ?? null;
                    $isTwoCar = !empty($simulation['is_two_car']) && $pRes2 !== null;
                    $pos1 = $pRes1['position'] ?? 10;
                    $pos2 = $pRes2['position'] ?? null;
                @endphp

                <!-- Simulation Speed & Instant Skip Controls -->
                <div class="flex items-center gap-1.5 bg-zinc-950/90 border border-zinc-800 rounded p-1.5 font-mono shadow-inner">
                    <span class="text-[10px] text-zinc-500 uppercase font-bold px-1.5 hidden sm:inline">Speed:</span>
                    <button type="button"
                        @click="setSpeed(1)"
                        :class="playbackSpeed === 1 ? 'bg-amber-500 text-black font-black shadow-sm' : 'bg-zinc-900 text-zinc-400 hover:text-white border border-zinc-800'"
                        class="px-2.5 py-1 rounded text-xs font-bold uppercase transition cursor-pointer">
                        1x
                    </button>
                    <button type="button"
                        @click="setSpeed(2)"
                        :class="playbackSpeed === 2 ? 'bg-amber-500 text-black font-black shadow-sm' : 'bg-zinc-900 text-zinc-400 hover:text-white border border-zinc-800'"
                        class="px-2.5 py-1 rounded text-xs font-bold uppercase transition cursor-pointer">
                        2x
                    </button>
                    <button type="button"
                        @click="setSpeed(5)"
                        :class="playbackSpeed === 5 ? 'bg-amber-500 text-black font-black shadow-sm' : 'bg-zinc-900 text-zinc-400 hover:text-white border border-zinc-800'"
                        class="px-2.5 py-1 rounded text-xs font-bold uppercase transition cursor-pointer">
                        5x
                    </button>
                    <button type="button"
                        @click="instantSkip()"
                        :disabled="isFinished"
                        :class="isFinished ? 'opacity-40 cursor-not-allowed bg-zinc-900 text-zinc-600 border border-zinc-800' : 'bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white font-black shadow-sm cursor-pointer border border-amber-500/50'"
                        class="ml-1 px-3 py-1 rounded text-xs uppercase transition flex items-center gap-1">
                        <span>⏩ Instant Skip</span>
                    </button>
                </div>

                <!-- Active Compound Badge -->
                <div class="bg-zinc-950/90 border border-zinc-800 rounded px-3 py-2 text-center font-mono">
                    <div class="text-[9px] text-zinc-500 uppercase">Tire Strategy</div>
                    <div class="text-xs font-black uppercase flex items-center justify-center gap-1.5 mt-0.5">
                        @if($pCompound === 'soft')
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block shadow-sm shadow-red-500/50"></span>
                            <span class="text-red-400">SOFT (C3)</span>
                        @elseif($pCompound === 'hard')
                            <span class="w-2.5 h-2.5 rounded-full bg-zinc-200 inline-block shadow-sm shadow-zinc-200/50"></span>
                            <span class="text-zinc-200">HARD (C1)</span>
                        @elseif($pCompound === 'wet')
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block shadow-sm shadow-blue-500/50"></span>
                            <span class="text-blue-400">WET RAIN</span>
                        @else
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block shadow-sm shadow-amber-400/50"></span>
                            <span class="text-amber-400">MEDIUM (C2)</span>
                        @endif
                    </div>
                </div>

                <!-- Active Driving Mode Badge -->
                <div class="bg-zinc-950/90 border border-zinc-800 rounded px-3 py-2 text-center font-mono">
                    <div class="text-[9px] text-zinc-500 uppercase">ECU Mapping</div>
                    <div class="text-xs font-black uppercase flex items-center justify-center gap-1.5 mt-0.5">
                        @if($pMode === 'push')
                            <span class="text-red-400">⚡ PUSH MODE</span>
                        @elseif($pMode === 'conserve')
                            <span class="text-emerald-400">🛡️ CONSERVE</span>
                        @else
                            <span class="text-zinc-300">⚙️ BALANCED</span>
                        @endif
                    </div>
                </div>

                <div class="bg-zinc-950/90 border border-zinc-800 rounded px-3 py-2 text-center font-mono">
                    <div class="text-[9px] text-zinc-500 uppercase">Track Surface</div>
                    <div class="text-xs font-bold text-white uppercase">{{ $race->weather === 'wet' ? '🌧️ Wet Rain' : '☀️ Dry Asphalt' }}</div>
                </div>

                <form method="POST" action="{{ route('races.run', $race) }}" class="inline">
                    @csrf
                    <input type="hidden" name="tire_compound" value="{{ $pCompound }}">
                    <input type="hidden" name="driving_mode" value="{{ $pMode }}">
                    <button type="submit" class="px-3.5 py-2.5 rounded bg-zinc-800 hover:bg-zinc-700 text-white border border-zinc-700 text-xs font-mono font-bold uppercase transition flex items-center gap-1.5 shadow-sm cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span>Re-simulate</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Dynamic Mini-Track Sector Map & Live Gaps Visualizer -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-5 shadow-xl font-mono relative overflow-hidden">
        <!-- Top Status & Sector Telemetry Row -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-zinc-800">
            <div class="flex items-center gap-3">
                <template x-if="!isFinished">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-emerald-950/90 border border-emerald-500/50 text-emerald-400 text-xs font-bold uppercase shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>LIVE SECTOR TELEMETRY &bull; LAP <span x-text="currentLap"></span> / {{ $race->laps }}</span>
                    </span>
                </template>
                <template x-if="isFinished">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-purple-950/90 border border-purple-500/50 text-purple-300 text-xs font-black uppercase shadow-sm">
                        <span>🏁 CHECKERED FLAG &bull; GRAND PRIX FINISHED</span>
                    </span>
                </template>

                <span class="text-xs text-zinc-400 hidden sm:inline">&bull;</span>
                <span class="text-xs text-zinc-300 font-bold hidden sm:inline">{{ $race->name }} ({{ strtoupper(str_replace('_', ' ', $race->track_type)) }})</span>
            </div>

            <!-- Active Sector & DRS Status Badges -->
            <div class="flex items-center gap-2 text-xs">
                <!-- Sector Active Indicator -->
                <div class="px-2.5 py-1 rounded bg-zinc-950 border border-zinc-800 text-[11px] text-zinc-400 flex items-center gap-1.5">
                    <span class="text-zinc-500 uppercase">Sector:</span>
                    <span class="text-amber-400 font-black" x-text="isFinished ? 'S3/FINISH' : ((currentLap % 3 === 1) ? 'S1 APEX' : ((currentLap % 3 === 2) ? 'S2 INFIELD' : 'S3 CHICANE'))"></span>
                </div>

                <!-- DRS Status -->
                <div class="px-2.5 py-1 rounded bg-zinc-950 border border-zinc-800 text-[11px] text-zinc-400 flex items-center gap-1.5">
                    <span class="text-zinc-500 uppercase">DRS:</span>
                    <span :class="isFinished ? 'text-zinc-500' : 'text-emerald-400 font-black animate-pulse'" x-text="isFinished ? 'CLOSED' : 'ENABLED'"></span>
                </div>

                <!-- Telemetry Progress -->
                <div class="px-2.5 py-1 rounded bg-zinc-950 border border-zinc-800 text-[11px] text-zinc-400 hidden md:flex items-center gap-1.5">
                    <span class="text-zinc-500 uppercase">Race Dist:</span>
                    <strong class="text-white" x-text="Math.round((currentLap / totalLaps) * 100) + '%'"></strong>
                </div>
            </div>
        </div>

        <!-- Interactive Sector Track Map Display -->
        <div class="space-y-2 py-1">
            <!-- Sector Header Labels -->
            <div class="grid grid-cols-4 text-[10px] uppercase font-bold text-zinc-500 px-1">
                <div class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span><span>SECTOR 1 (TURN 1-4)</span></div>
                <div class="flex items-center gap-1 text-center justify-center"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span><span>SECTOR 2 (INFIELD)</span></div>
                <div class="flex items-center gap-1 text-center justify-center"><span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span><span>SECTOR 3 (CHICANE)</span></div>
                <div class="flex items-center gap-1 text-right justify-end"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span><span>DRS FINISH LINE 🏁</span></div>
            </div>

            <!-- Dynamic Track Rail with Car Position Markers -->
            <div class="relative w-full h-10 bg-zinc-950 rounded-lg border border-zinc-800 flex items-center px-2 overflow-visible">
                <!-- Sector Grid Divider Lines -->
                <div class="absolute inset-0 grid grid-cols-4 pointer-events-none divide-x divide-zinc-800/80">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>

                <!-- Track Background Progress Line -->
                <div class="absolute left-2 right-2 h-1.5 bg-zinc-900 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-cyan-500 via-amber-400 to-emerald-400 rounded-full transition-all duration-300 ease-linear"
                         :style="'width: ' + ((currentLap / totalLaps) * 100) + '%'"></div>
                </div>

                <!-- Competitor Visual Dots (Rivals in pack) -->
                <div class="absolute transition-all duration-300 ease-out flex items-center"
                     :style="'left: ' + (isFinished ? 98 : Math.max(4, Math.min(94, ((currentLap / totalLaps) * 100) + 1.5))) + '%'">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 border border-white shadow-sm" title="P1 Leader"></span>
                </div>
                <div class="absolute transition-all duration-300 ease-out flex items-center"
                     :style="'left: ' + (isFinished ? 95 : Math.max(3, Math.min(92, ((currentLap / totalLaps) * 100) - 2.5))) + '%'">
                    <span class="w-2 h-2 rounded-full bg-zinc-500 border border-zinc-400" title="Competitor"></span>
                </div>
                <div class="absolute transition-all duration-300 ease-out flex items-center"
                     :style="'left: ' + (isFinished ? 92 : Math.max(2, Math.min(88, ((currentLap / totalLaps) * 100) - 5.0))) + '%'">
                    <span class="w-2 h-2 rounded-full bg-zinc-600 border border-zinc-500" title="Competitor"></span>
                </div>

                <!-- Car #1 (YOU) Indicator Marker -->
                <div class="absolute transition-all duration-300 ease-out z-20 -translate-x-1/2 flex flex-col items-center"
                     :style="'left: ' + (isFinished ? 97 : Math.max(3, Math.min(96, ((currentLap / totalLaps) * 100) - ({{ $pos1 }} * 0.4)))) + '%'">
                    <div class="px-1.5 py-0.5 rounded bg-cyan-500 text-black font-black text-[9px] shadow-lg shadow-cyan-500/50 flex items-center gap-0.5 border border-white">
                        <span>C1</span>
                    </div>
                </div>

                <!-- Car #2 (YOU) Indicator Marker (if 2-car entry) -->
                @if($isTwoCar)
                    <div class="absolute transition-all duration-300 ease-out z-10 -translate-x-1/2 flex flex-col items-center"
                         :style="'left: ' + (isFinished ? 94 : Math.max(2, Math.min(94, ((currentLap / totalLaps) * 100) - ({{ $pos2 ?? 8 }} * 0.45)))) + '%'">
                        <div class="px-1.5 py-0.5 rounded bg-blue-500 text-white font-black text-[9px] shadow-lg shadow-blue-500/50 flex items-center gap-0.5 border border-blue-200">
                            <span>C2</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Live Interval Gaps & Driver Status Pills -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mt-3 pt-3 border-t border-zinc-800/80 text-xs">
            <!-- Car 1 Live Delta -->
            <div class="bg-zinc-950/80 border border-cyan-500/40 rounded p-2.5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                    <span class="text-zinc-300 font-bold uppercase">Car #1 &bull; {{ $pRes1['driver_name'] }}</span>
                </div>
                <div class="text-right font-black text-cyan-300">
                    <span>P{{ $pos1 }}</span>
                    <span class="text-[10px] text-zinc-400 ml-1">({{ $pRes1['gap'] ?? 'LEADER' }})</span>
                </div>
            </div>

            <!-- Car 2 Live Delta (if 2-car) -->
            @if($isTwoCar)
                <div class="bg-zinc-950/80 border border-blue-500/40 rounded p-2.5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                        <span class="text-zinc-300 font-bold uppercase">Car #2 &bull; {{ $pRes2['driver_name'] }}</span>
                    </div>
                    <div class="text-right font-black text-blue-300">
                        <span>P{{ $pos2 }}</span>
                        <span class="text-[10px] text-zinc-400 ml-1">({{ $pRes2['gap'] ?? '+1.500s' }})</span>
                    </div>
                </div>
            @else
                <div class="bg-zinc-950/80 border border-zinc-800 rounded p-2.5 flex items-center justify-between text-zinc-500">
                    <span>Car #2 Slot</span>
                    <span class="text-[10px] font-bold">STANDBY / SINGLE CAR</span>
                </div>
            @endif

            <!-- Track Weather Condition -->
            <div class="bg-zinc-950/80 border border-zinc-800 rounded p-2.5 flex items-center justify-between">
                <span class="text-zinc-400 uppercase">Track Surface:</span>
                <span class="text-white font-bold">{{ $race->weather === 'wet' ? '🌧️ Wet Rain' : '☀️ Dry Asphalt' }}</span>
            </div>

            <!-- Fastest Lap Delta -->
            <div class="bg-zinc-950/80 border border-purple-500/30 rounded p-2.5 flex items-center justify-between">
                <span class="text-purple-400 font-bold uppercase">Fastest Lap:</span>
                <span class="text-purple-300 font-black">{{ $simulation['fastest_lap_overall']['time'] ?? '1:14.200' }}</span>
            </div>
        </div>
    </div>

    <!-- Player Outcome Banner -->
    @php
        $pRes1 = $simulation['player_result_1'] ?? $simulation['player_result'];
        $pRes2 = $simulation['player_result_2'] ?? null;
        $isTwoCar = !empty($simulation['is_two_car']) && $pRes2 !== null;
        $pos1 = $pRes1['position'] ?? 10;
        $pos2 = $pRes2['position'] ?? null;
    @endphp
    <div class="bg-gradient-to-r from-zinc-900 via-zinc-900/90 to-zinc-950 border border-cyan-500/40 rounded p-6 shadow-xl relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex flex-wrap items-center gap-5">
                <!-- Car #1 Card -->
                <div class="flex items-center gap-3 bg-zinc-950/80 border border-cyan-500/50 rounded-lg p-3.5 shadow-md">
                    <div class="w-12 h-12 rounded bg-cyan-950 border border-cyan-400 text-cyan-300 flex flex-col items-center justify-center font-mono font-black shrink-0">
                        <span class="text-xl leading-none">P{{ $pos1 }}</span>
                        <span class="text-[8px] uppercase tracking-wider text-cyan-400">CAR #1</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-mono font-bold uppercase tracking-widest text-cyan-400 block">
                            {{ $pos1 === 1 ? '🏆 VICTORY' : ($pos1 <= 3 ? '🏁 PODIUM' : 'CLASSIFIED') }}
                        </span>
                        <h3 class="text-sm font-black text-white font-mono uppercase">{{ $pRes1['driver_name'] }}</h3>
                        <p class="text-[11px] font-mono text-zinc-400">{{ $pRes1['car_name'] }} &bull; {{ $pRes1['total_time'] }}</p>
                    </div>
                </div>

                <!-- Car #2 Card (if 2-car entry) -->
                @if($isTwoCar)
                    <div class="flex items-center gap-3 bg-zinc-950/80 border border-blue-500/50 rounded-lg p-3.5 shadow-md">
                        <div class="w-12 h-12 rounded bg-blue-950 border border-blue-400 text-blue-300 flex flex-col items-center justify-center font-mono font-black shrink-0">
                            <span class="text-xl leading-none">P{{ $pos2 }}</span>
                            <span class="text-[8px] uppercase tracking-wider text-blue-400">CAR #2</span>
                        </div>
                        <div>
                            <span class="text-[9px] font-mono font-bold uppercase tracking-widest text-blue-400 block">
                                {{ $pos2 === 1 ? '🏆 VICTORY' : ($pos2 <= 3 ? '🏁 PODIUM' : 'CLASSIFIED') }}
                            </span>
                            <h3 class="text-sm font-black text-white font-mono uppercase">{{ $pRes2['driver_name'] }}</h3>
                            <p class="text-[11px] font-mono text-zinc-400">{{ $pRes2['car_name'] }} &bull; {{ $pRes2['total_time'] }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('races.results', $race) }}" class="px-5 py-3 rounded bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-mono font-bold tracking-wider uppercase transition shadow-md flex items-center gap-2">
                    <span>View Official Debrief & Payouts</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Telemetry Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Live Classification Leaderboard -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Leaderboard Table -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded bg-orange-500"></span>
                        <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Grand Prix Final Classification</h2>
                    </div>
                    <span class="text-xs font-mono text-zinc-400">{{ count($simulation['standings']) }} Competitors Classified</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs font-mono">
                        <thead class="text-[10px] text-zinc-500 uppercase border-b border-zinc-800">
                            <tr>
                                <th class="py-2.5 px-3">Pos</th>
                                <th class="py-2.5 px-3">Driver / Constructor</th>
                                <th class="py-2.5 px-3">Strategy</th>
                                <th class="py-2.5 px-3 text-right">Total Time</th>
                                <th class="py-2.5 px-3 text-right">Interval</th>
                                <th class="py-2.5 px-3 text-right">Best Lap</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/60">
                            @foreach($simulation['standings'] as $driver)
                                @php
                                    $isPlayer = !empty($driver['is_player']);
                                    $slot = $driver['car_slot'] ?? 1;
                                @endphp
                                <tr class="{{ $isPlayer ? ($slot === 1 ? 'bg-cyan-950/40 border-l-2 border-l-cyan-400 font-bold' : 'bg-blue-950/40 border-l-2 border-l-blue-400 font-bold') : 'hover:bg-zinc-800/30' }} transition-colors">
                                    <!-- Position -->
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

                                    <!-- Driver & Team -->
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-2">
                                            <div>
                                                <div class="text-white font-bold flex items-center gap-1.5">
                                                    <span>{{ $driver['driver_name'] }}</span>
                                                    @if($isPlayer)
                                                        @if($slot === 1)
                                                            <span class="text-[9px] bg-cyan-600 text-white px-1.5 py-0.2 rounded font-black tracking-tighter">CAR #1 (YOU)</span>
                                                        @else
                                                            <span class="text-[9px] bg-blue-600 text-white px-1.5 py-0.2 rounded font-black tracking-tighter">CAR #2 (YOU)</span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <div class="text-[10px] text-zinc-400 uppercase font-normal">{{ $driver['team_name'] }} &bull; {{ $driver['car_name'] }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Strategy (Tire & Mode) -->
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-1.5">
                                            @php
                                                $dCompound = $driver['tire_compound'] ?? 'medium';
                                                $dMode = $driver['driving_mode'] ?? 'balanced';
                                            @endphp
                                            <!-- Compound Pill -->
                                            @if($dCompound === 'soft')
                                                <span class="px-1.5 py-0.5 rounded bg-red-950 text-red-400 border border-red-800/80 text-[9px] font-black" title="Soft Compound">S</span>
                                            @elseif($dCompound === 'hard')
                                                <span class="px-1.5 py-0.5 rounded bg-zinc-800 text-zinc-200 border border-zinc-600 text-[9px] font-black" title="Hard Compound">H</span>
                                            @elseif($dCompound === 'wet')
                                                <span class="px-1.5 py-0.5 rounded bg-blue-950 text-blue-400 border border-blue-700 text-[9px] font-black" title="Wet Rain Compound">W</span>
                                            @else
                                                <span class="px-1.5 py-0.5 rounded bg-amber-950 text-amber-400 border border-amber-800/80 text-[9px] font-black" title="Medium Compound">M</span>
                                            @endif

                                            <!-- Mode Pill -->
                                            @if($dMode === 'push')
                                                <span class="text-[9px] text-red-400 font-bold">PUSH</span>
                                            @elseif($dMode === 'conserve')
                                                <span class="text-[9px] text-emerald-400 font-bold">ECO</span>
                                            @else
                                                <span class="text-[9px] text-zinc-400">STD</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Total Time -->
                                    <td class="py-3 px-3 text-right text-zinc-200">
                                        {{ $driver['total_time'] }}
                                    </td>

                                    <!-- Gap -->
                                    <td class="py-3 px-3 text-right font-bold {{ $driver['gap'] === 'LEADER' ? 'text-emerald-400' : 'text-zinc-400' }}">
                                        {{ $driver['gap'] }}
                                    </td>

                                    <!-- Best Lap -->
                                    <td class="py-3 px-3 text-right font-mono text-cyan-400">
                                        {{ $driver['fastest_lap'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Fastest Lap Honors Banner -->
            @if(isset($simulation['fastest_lap_overall']))
                <div class="bg-zinc-900 border border-purple-500/40 rounded p-4 flex items-center justify-between text-xs font-mono shadow-md">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded bg-purple-950 border border-purple-500/40 flex items-center justify-center text-purple-300 font-black">
                            ⏱️
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-purple-400 uppercase tracking-wider block">OFFICIAL FASTEST RACE LAP</span>
                            <span class="text-white font-bold">{{ $simulation['fastest_lap_overall']['driver'] }} ({{ $simulation['fastest_lap_overall']['team'] }}) &bull; Lap {{ $simulation['fastest_lap_overall']['lap'] }}</span>
                        </div>
                    </div>
                    <div class="text-base font-black text-purple-300">
                        {{ $simulation['fastest_lap_overall']['time'] }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Right 1 Col: Live Pit-Wall Radio & Event Commentary Feed -->
        <div class="space-y-6">
            <!-- Event Feed -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Pit-Wall Radio & Incident Log</h2>
                    </div>
                    <span class="text-[10px] font-mono text-zinc-500">LIVE TELEMETRY</span>
                </div>

                <div id="telemetry-radio-feed" class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
                    @foreach($simulation['lap_events'] as $event)
                        <div x-show="currentLap >= {{ $event['lap'] }}"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="p-3 rounded border text-xs font-mono {{ $event['type'] === 'overtake' ? 'bg-emerald-950/40 border-emerald-500/40 text-emerald-200' : ($event['type'] === 'defense_loss' ? 'bg-amber-950/40 border-amber-500/40 text-amber-200' : ($event['type'] === 'telemetry_alert' ? 'bg-red-950/40 border-red-500/40 text-red-200' : ($event['type'] === 'pit_radio' ? 'bg-blue-950/40 border-blue-500/40 text-blue-200' : ($event['type'] === 'finish' ? 'bg-purple-950/40 border-purple-500/40 text-purple-200' : 'bg-zinc-950/60 border-zinc-800 text-zinc-300')))) }}">
                            <div class="flex items-center justify-between text-[10px] font-bold uppercase mb-1 opacity-80">
                                <span>LAP {{ $event['lap'] }}</span>
                                <span class="tracking-wider">{{ strtoupper(str_replace('_', ' ', $event['type'])) }}</span>
                            </div>
                            <p class="leading-relaxed">{{ $event['message'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Action Navigation -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-5 shadow-lg space-y-2 text-xs font-mono">
                <div class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 mb-2">Post-Race Commands</div>
                <a href="{{ route('races.results', $race) }}" class="block p-2.5 rounded bg-emerald-950 hover:bg-emerald-900 border border-emerald-500/50 text-emerald-300 hover:text-white font-bold transition-colors">
                    &rarr; View Official Race Debrief & Ledger
                </a>
                <a href="{{ route('races.index') }}" class="block p-2.5 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white transition-colors">
                    &rarr; Return to Grand Prix Schedule
                </a>
                <a href="{{ route('dashboard') }}" class="block p-2.5 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white transition-colors">
                    &rarr; Return to Paddock Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
