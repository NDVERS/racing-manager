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
                    <span class="w-2.5 h-2.5 rounded-full bg-zinc-500 border border-zinc-400" title="Competitor"></span>
                </div>
                <div class="absolute transition-all duration-300 ease-out flex items-center"
                     :style="'left: ' + (isFinished ? 92 : Math.max(2, Math.min(88, ((currentLap / totalLaps) * 100) - 5.0))) + '%'">
                    <span class="w-2.5 h-2.5 rounded-full bg-zinc-600 border border-zinc-500" title="Competitor"></span>
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
    <div class="bg-gradient-to-r from-zinc-900 via-zinc-900/90 to-zinc-950 border border-cyan-500/40 rounded p-6 shadow-xl relative overflow-hidden font-mono">
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
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Official F1 Pit-Wall Live Timing Tower (20 Cars) -->
        <div class="xl:col-span-2 space-y-6">
            @php
                // Pre-process Timing Tower Standings, Micro-Sectors, Intervals, and FL
                $towerStandings = [];
                $prevTotalSeconds = null;
                $fastestLapOverallTime = $simulation['fastest_lap_overall']['time'] ?? null;
                $fastestDriverName = $simulation['fastest_lap_overall']['driver'] ?? null;

                // Find lowest sector bounds for purple honors
                $minS1 = 999.0;
                $minS2 = 999.0;
                $minS3 = 999.0;

                foreach ($simulation['standings'] as $idx => $driver) {
                    $flSec = $driver['fastest_lap_seconds'] ?? (74.0 + $idx * 0.12);
                    $s1Val = round($flSec * 0.315 + (($idx % 4) * 0.05), 3);
                    $s2Val = round($flSec * 0.410 + (($idx % 3) * 0.06), 3);
                    $s3Val = round($flSec * 0.275 + (($idx % 5) * 0.04), 3);

                    if ($s1Val < $minS1) { $minS1 = $s1Val; }
                    if ($s2Val < $minS2) { $minS2 = $s2Val; }
                    if ($s3Val < $minS3) { $minS3 = $s3Val; }
                }

                foreach ($simulation['standings'] as $idx => $driver) {
                    $currSeconds = $driver['total_seconds'] ?? (1000 + $idx * 1.4);
                    if ($idx === 0) {
                        $intervalText = 'LEADER';
                        $intervalSec = 0.0;
                    } else {
                        $intervalSec = max(0.045, $currSeconds - $prevTotalSeconds);
                        $intervalText = '+' . number_format($intervalSec, 3) . 's';
                    }
                    $prevTotalSeconds = $currSeconds;

                    $flSec = $driver['fastest_lap_seconds'] ?? (74.0 + $idx * 0.12);
                    $s1 = round($flSec * 0.315 + (($idx % 4) * 0.05), 3);
                    $s2 = round($flSec * 0.410 + (($idx % 3) * 0.06), 3);
                    $s3 = round($flSec * 0.275 + (($idx % 5) * 0.04), 3);

                    // Position Delta Calculation
                    $startGridPos = (($idx * 3 + 7) % count($simulation['standings'])) + 1;
                    $gainLoss = $startGridPos - ($idx + 1); // positive = gained positions

                    $isPlayer = !empty($driver['is_player']);
                    $slot = $driver['car_slot'] ?? 1;
                    $isOverallFastest = ($driver['driver_name'] === $fastestDriverName || $driver['fastest_lap'] === $fastestLapOverallTime);

                    $towerStandings[] = array_merge($driver, [
                        'position' => $idx + 1,
                        'interval_text' => $intervalText,
                        'interval_sec' => $intervalSec,
                        'start_grid' => $startGridPos,
                        'gain_loss' => $gainLoss,
                        's1' => number_format($s1, 3),
                        's2' => number_format($s2, 3),
                        's3' => number_format($s3, 3),
                        'is_s1_fastest' => ($s1 <= $minS1 + 0.005),
                        'is_s2_fastest' => ($s2 <= $minS2 + 0.005),
                        'is_s3_fastest' => ($s3 <= $minS3 + 0.005),
                        'is_overall_fastest' => $isOverallFastest,
                        'is_player' => $isPlayer,
                        'slot' => $slot,
                    ]);
                }
            @endphp

            <!-- Timing Tower Table Container -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-5 shadow-2xl relative font-mono overflow-hidden">
                <!-- Pit-Wall HUD Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-zinc-800 gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded bg-red-600 animate-pulse flex items-center justify-center"></div>
                        <div>
                            <h2 class="text-sm font-black uppercase tracking-wider text-white flex items-center gap-2">
                                <span>Official F1 Pit-Wall Live Timing Tower</span>
                                <span class="text-[9px] bg-red-950 border border-red-500/50 text-red-400 px-1.5 py-0.5 rounded font-bold">
                                    FIA OFFICIAL
                                </span>
                            </h2>
                            <p class="text-[11px] text-zinc-400 mt-0.5">
                                Grand Prix Final Classification &bull; Sector telemetry, interval delta & tire life &bull; 20 Cars Grid
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-[10px] text-zinc-400 bg-zinc-950 border border-zinc-800 px-2 py-1 rounded">
                            LAP <span class="text-amber-400 font-bold" x-text="currentLap"></span> / {{ $race->laps }}
                        </span>
                        <span class="text-[10px] text-emerald-400 bg-emerald-950/80 border border-emerald-500/40 px-2 py-1 rounded font-bold">
                            SYNC: 100%
                        </span>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="text-[9px] text-zinc-400 uppercase bg-zinc-950/80 border-y border-zinc-800">
                            <tr>
                                <th class="py-2.5 px-2.5 text-center w-12">POS</th>
                                <th class="py-2.5 px-2 text-center w-10">GAIN</th>
                                <th class="py-2.5 px-3 min-w-[160px]">DRIVER / CONSTRUCTOR</th>
                                <th class="py-2.5 px-2.5 text-center w-16">TYRE</th>
                                <th class="py-2.5 px-2 text-right w-16">S1</th>
                                <th class="py-2.5 px-2 text-right w-16">S2</th>
                                <th class="py-2.5 px-2 text-right w-16">S3</th>
                                <th class="py-2.5 px-2.5 text-right w-20">GAP</th>
                                <th class="py-2.5 px-2.5 text-right min-w-[90px]">INTERVAL</th>
                                <th class="py-2.5 px-3 text-right min-w-[100px]">BEST LAP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/50">
                            @foreach($towerStandings as $row)
                                <tr class="transition-colors {{ $row['is_player'] ? ($row['slot'] === 1 ? 'bg-cyan-950/30 border-l-4 border-l-cyan-400' : 'bg-blue-950/30 border-l-4 border-l-blue-400') : 'hover:bg-zinc-800/30' }}">
                                    <!-- Pos -->
                                    <td class="py-2.5 px-2.5 text-center font-black">
                                        @if($row['position'] === 1)
                                            <span class="inline-flex w-6 h-6 rounded bg-amber-400 text-black font-black text-xs items-center justify-center shadow-md shadow-amber-400/30">1</span>
                                        @elseif($row['position'] === 2)
                                            <span class="inline-flex w-6 h-6 rounded bg-zinc-200 text-black font-black text-xs items-center justify-center shadow-sm">2</span>
                                        @elseif($row['position'] === 3)
                                            <span class="inline-flex w-6 h-6 rounded bg-amber-700 text-white font-black text-xs items-center justify-center shadow-sm">3</span>
                                        @else
                                            <span class="text-zinc-400 font-bold">{{ $row['position'] }}</span>
                                        @endif
                                    </td>

                                    <!-- Gain/Loss Delta -->
                                    <td class="py-2.5 px-2 text-center text-[10px] font-bold">
                                        @if($row['gain_loss'] > 0)
                                            <span class="text-emerald-400">▲+{{ $row['gain_loss'] }}</span>
                                        @elseif($row['gain_loss'] < 0)
                                            <span class="text-rose-400">▼{{ $row['gain_loss'] }}</span>
                                        @else
                                            <span class="text-zinc-500">-</span>
                                        @endif
                                    </td>

                                    <!-- Driver & Team Identity -->
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-2">
                                            <div>
                                                <div class="text-white font-bold flex items-center gap-1.5 leading-tight">
                                                    <span class="{{ $row['is_player'] ? 'text-white' : 'text-zinc-200' }}">{{ $row['driver_name'] }}</span>
                                                    @if($row['is_player'])
                                                        @if($row['slot'] === 1)
                                                            <span class="text-[8px] bg-cyan-500 text-black px-1.5 py-0.5 rounded font-black tracking-wider">CAR #1 (YOU)</span>
                                                        @else
                                                            <span class="text-[8px] bg-blue-500 text-white px-1.5 py-0.5 rounded font-black tracking-wider">CAR #2 (YOU)</span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <div class="text-[10px] text-zinc-400 uppercase font-normal mt-0.5">
                                                    {{ $row['team_name'] }} &bull; <span class="text-zinc-500">{{ $row['car_name'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Tire Status (Compound & Age) -->
                                    <td class="py-2.5 px-2.5 text-center">
                                        @php
                                            $comp = $row['tire_compound'] ?? 'medium';
                                        @endphp
                                        <div class="inline-flex items-center gap-1">
                                            @if($comp === 'soft')
                                                <span class="w-4 h-4 rounded-full bg-red-600 text-white border border-red-400 text-[9px] font-black inline-flex items-center justify-center shadow-sm" title="Soft (C3)">S</span>
                                            @elseif($comp === 'hard')
                                                <span class="w-4 h-4 rounded-full bg-zinc-100 text-black border border-zinc-300 text-[9px] font-black inline-flex items-center justify-center shadow-sm" title="Hard (C1)">H</span>
                                            @elseif($comp === 'wet')
                                                <span class="w-4 h-4 rounded-full bg-blue-600 text-white border border-blue-400 text-[9px] font-black inline-flex items-center justify-center shadow-sm" title="Wet Rain">W</span>
                                            @else
                                                <span class="w-4 h-4 rounded-full bg-amber-400 text-black border border-amber-300 text-[9px] font-black inline-flex items-center justify-center shadow-sm" title="Medium (C2)">M</span>
                                            @endif
                                            <span class="text-[9px] text-zinc-500 font-semibold">L<span x-text="currentLap"></span></span>
                                        </div>
                                    </td>

                                    <!-- Micro-Sector 1 -->
                                    <td class="py-2.5 px-2 text-right text-[11px] font-bold">
                                        @if($row['is_s1_fastest'])
                                            <span class="text-purple-300 bg-purple-950/80 border border-purple-500/40 px-1 py-0.5 rounded font-black shadow-sm" title="Overall Fastest S1">{{ $row['s1'] }}</span>
                                        @elseif($row['position'] <= 3)
                                            <span class="text-amber-300">{{ $row['s1'] }}</span>
                                        @else
                                            <span class="text-zinc-400">{{ $row['s1'] }}</span>
                                        @endif
                                    </td>

                                    <!-- Micro-Sector 2 -->
                                    <td class="py-2.5 px-2 text-right text-[11px] font-bold">
                                        @if($row['is_s2_fastest'])
                                            <span class="text-purple-300 bg-purple-950/80 border border-purple-500/40 px-1 py-0.5 rounded font-black shadow-sm" title="Overall Fastest S2">{{ $row['s2'] }}</span>
                                        @elseif($row['position'] <= 3)
                                            <span class="text-amber-300">{{ $row['s2'] }}</span>
                                        @else
                                            <span class="text-zinc-400">{{ $row['s2'] }}</span>
                                        @endif
                                    </td>

                                    <!-- Micro-Sector 3 -->
                                    <td class="py-2.5 px-2 text-right text-[11px] font-bold">
                                        @if($row['is_s3_fastest'])
                                            <span class="text-purple-300 bg-purple-950/80 border border-purple-500/40 px-1 py-0.5 rounded font-black shadow-sm" title="Overall Fastest S3">{{ $row['s3'] }}</span>
                                        @elseif($row['position'] <= 3)
                                            <span class="text-amber-300">{{ $row['s3'] }}</span>
                                        @else
                                            <span class="text-zinc-400">{{ $row['s3'] }}</span>
                                        @endif
                                    </td>

                                    <!-- Gap to Leader -->
                                    <td class="py-2.5 px-2.5 text-right font-bold {{ $row['position'] === 1 ? 'text-emerald-400' : 'text-zinc-300' }}">
                                        {{ $row['gap'] }}
                                    </td>

                                    <!-- Interval to Car Ahead -->
                                    <td class="py-2.5 px-2.5 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <span class="font-bold {{ $row['position'] === 1 ? 'text-emerald-400' : 'text-zinc-400' }}">
                                                {{ $row['interval_text'] }}
                                            </span>
                                            @if($row['position'] > 1 && $row['interval_sec'] < 1.0)
                                                <span class="text-[8px] px-1 py-0.2 rounded bg-emerald-950 text-emerald-400 border border-emerald-500/50 font-black animate-pulse" title="DRS Zone Active (< 1.0s)">
                                                    DRS
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Best Lap & FL Indicator -->
                                    <td class="py-2.5 px-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if($row['is_overall_fastest'])
                                                <span class="text-[9px] bg-purple-950 text-purple-300 border border-purple-500/60 px-1.5 py-0.5 rounded font-black flex items-center gap-1 shadow-sm shadow-purple-500/20">
                                                    <span>🟣 FL</span>
                                                    <span>{{ $row['fastest_lap'] }}</span>
                                                </span>
                                            @else
                                                <span class="text-zinc-400">{{ $row['fastest_lap'] }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Timing Tower Legend / Footer -->
                <div class="flex flex-wrap items-center justify-between text-[10px] text-zinc-500 pt-3 mt-3 border-t border-zinc-800/80 gap-2">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-purple-500"></span><span>Overall Fastest Sector</span></div>
                        <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400"></span><span>Podium Pace</span></div>
                        <div class="flex items-center gap-1"><span class="px-1 py-0.2 rounded bg-emerald-950 text-emerald-400 border border-emerald-500/40 text-[8px] font-bold">DRS</span><span>Interval &lt; 1.000s</span></div>
                    </div>
                    <div>
                        <span>Timing Engine v2.4 &bull; Official FIA Paddock Telemetry</span>
                    </div>
                </div>
            </div>

            <!-- Fastest Lap Honors Banner -->
            @if(isset($simulation['fastest_lap_overall']))
                <div class="bg-zinc-900 border border-purple-500/40 rounded-lg p-4 flex items-center justify-between text-xs font-mono shadow-md">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded bg-purple-950 border border-purple-500/40 flex items-center justify-center text-purple-300 font-black text-sm">
                            ⏱️
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-purple-400 uppercase tracking-wider block">OFFICIAL FASTEST RACE LAP</span>
                            <span class="text-white font-bold">{{ $simulation['fastest_lap_overall']['driver'] }} ({{ $simulation['fastest_lap_overall']['team'] }}) &bull; Lap {{ $simulation['fastest_lap_overall']['lap'] }}</span>
                        </div>
                    </div>
                    <div class="text-base font-black text-purple-300 flex items-center gap-1">
                        <span>🟣</span>
                        <span>{{ $simulation['fastest_lap_overall']['time'] }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right 1 Col: Live Pit-Wall Radio & Event Commentary Feed -->
        <div class="space-y-6 font-mono">
            <!-- Event Feed -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-5 shadow-lg">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-white">Pit-Wall Radio & Incident Log</h2>
                    </div>
                    <span class="text-[10px] text-zinc-500">LIVE FEED</span>
                </div>

                <div id="telemetry-radio-feed" class="space-y-3 max-h-[520px] overflow-y-auto pr-1">
                    @foreach($simulation['lap_events'] as $event)
                        <div x-show="currentLap >= {{ $event['lap'] }}"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="p-3 rounded border text-xs {{ $event['type'] === 'overtake' ? 'bg-emerald-950/40 border-emerald-500/40 text-emerald-200' : ($event['type'] === 'defense_loss' ? 'bg-amber-950/40 border-amber-500/40 text-amber-200' : ($event['type'] === 'telemetry_alert' ? 'bg-red-950/40 border-red-500/40 text-red-200' : ($event['type'] === 'pit_radio' ? 'bg-blue-950/40 border-blue-500/40 text-blue-200' : ($event['type'] === 'finish' ? 'bg-purple-950/40 border-purple-500/40 text-purple-200' : 'bg-zinc-950/60 border-zinc-800 text-zinc-300')))) }}">
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
            <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-5 shadow-lg space-y-2 text-xs">
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

