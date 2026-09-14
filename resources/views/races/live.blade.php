@extends('layouts.app')

@section('title', 'Live Pit-Wall Telemetry - ' . $race->name)

@section('content')
@php
    $pCompound = $simulation['tactics']['tire_compound'] ?? ($simulation['tire_compound'] ?? 'medium');
    $pMode = $simulation['tactics']['driving_mode'] ?? ($simulation['driving_mode'] ?? 'balanced');
    $pRes1 = $simulation['player_result_1'] ?? ($simulation['player_result'] ?? ['position' => 10, 'driver_name' => 'Driver 1', 'car_name' => 'Car 1', 'total_time' => '--', 'gap' => 'LEADER']);
    $pRes2 = $simulation['player_result_2'] ?? null;
    $isTwoCar = !empty($simulation['is_two_car']) && $pRes2 !== null;
    $pos1 = $pRes1['position'] ?? 10;
    $pos2 = $pRes2['position'] ?? null;

    // Pre-process Timing Tower Standings, Micro-Sectors, Intervals, and FL
    $towerStandings = [];
    $prevTotalSeconds = null;
    $leaderSeconds = $simulation['standings'][0]['total_seconds'] ?? 1000.0;
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
        $gapSeconds = max(0.0, $currSeconds - $leaderSeconds);

        if ($idx === 0) {
            $intervalText = 'LEADER';
            $intervalSec = 0.0;
            $gapSeconds = 0.0;
        } elseif ($idx === 1) {
            $intervalSec = $gapSeconds;
            $intervalText = '+' . number_format($intervalSec, 3) . 's';
        } else {
            $intervalSec = max(0.001, $currSeconds - $prevTotalSeconds);
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
        $slot = $driver['car_slot'] ?? ($isPlayer ? ($driver['driver_name'] === ($pRes2['driver_name'] ?? '') ? 2 : 1) : 1);
        $isOverallFastest = ($driver['driver_name'] === $fastestDriverName || $driver['fastest_lap'] === $fastestLapOverallTime);

        $towerStandings[] = array_merge($driver, [
            'position' => $idx + 1,
            'gap_seconds' => round($gapSeconds, 3),
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

    // Determine Circuit SVG Layout Path based on track type / location (5 Distinct Layouts)
    $trackType = $race->track_type ?? 'balanced';
    $location = strtolower($race->location ?? '');
    $raceName = strtolower($race->name ?? '');

    // 1. Sentul Speed Circuit (Bogor) - Classic Permanent High-Speed
    if (str_contains($location, 'sentul') || str_contains($raceName, 'sentul')) {
        $circuitLayoutName = 'Sentul International Circuit (Permanent High-Speed Layout)';
        $circuitDesc = 'Classic permanent high-speed circuit with twin parallel straights, heavy braking Turn 1, and wide aerodynamic sweepers.';
        $circuitSvgPath = 'M 120 280 L 660 280 C 730 280 750 240 750 185 C 750 130 730 90 660 90 L 460 90 C 400 90 360 140 310 140 L 220 140 C 140 140 70 180 70 230 C 70 270 90 280 120 280 Z';
        $drsStart = 0.03;
        $drsLength = 0.32;
        $s1End = 0.35;
        $s2End = 0.68;
    }
    // 2. Jakarta Night Prix (Ancol) - Urban Street Circuit
    elseif (str_contains($location, 'jakarta') || str_contains($raceName, 'jakarta')) {
        $circuitLayoutName = 'Jakarta International E-Prix (Urban Street Layout)';
        $circuitDesc = 'Ancol-inspired urban street circuit characterized by 90-degree corners, narrow concrete chicanes, and high-speed boulevard straights.';
        $circuitSvgPath = 'M 100 290 L 640 290 C 670 290 690 270 690 240 L 690 190 C 690 160 670 140 640 140 L 530 140 C 500 140 490 100 460 90 L 360 90 C 330 90 310 120 310 150 L 310 200 C 310 230 270 240 240 240 L 160 240 C 110 240 70 250 70 270 C 70 290 80 290 100 290 Z';
        $drsStart = 0.02;
        $drsLength = 0.28;
        $s1End = 0.30;
        $s2End = 0.65;
    }
    // 3. Mandalika Coastal Challenge (Lombok) - Modern Oceanfront Flowing
    elseif (str_contains($location, 'mandalika') || str_contains($raceName, 'mandalika')) {
        $circuitLayoutName = 'Pertamina Mandalika Circuit (Oceanfront Flowing Layout)';
        $circuitDesc = 'Modern coastal circuit with fast sweeping S-curves, oceanfront loop, and smooth rhythmic medium-to-high speed corners.';
        $circuitSvgPath = 'M 110 270 L 620 270 C 675 270 735 235 735 185 C 735 135 685 105 630 105 L 485 105 C 445 105 425 65 385 65 C 345 65 335 115 295 115 L 215 115 C 175 115 155 75 115 75 C 65 75 55 135 55 185 C 55 245 75 270 110 270 Z';
        $drsStart = 0.04;
        $drsLength = 0.28;
        $s1End = 0.34;
        $s2End = 0.67;
    }
    // 4. Cimahi GP (Brigif 15) - Compact Technical Autodrome
    elseif (str_contains($location, 'cimahi') || str_contains($raceName, 'cimahi')) {
        $circuitLayoutName = 'Brigif 15 Cimahi Autodrome (Compact Technical Layout)';
        $circuitDesc = 'Compact autodrome featuring double-hairpin complexes, rapid switchbacks, and high downforce technical sectors.';
        $circuitSvgPath = 'M 110 280 L 480 280 C 520 280 550 250 540 220 C 530 190 490 190 460 160 C 430 130 450 90 510 90 L 670 90 C 730 90 750 140 730 180 C 710 220 660 230 600 230 L 450 230 C 410 230 400 150 340 150 L 220 150 C 160 150 120 180 120 220 C 120 260 80 280 110 280 Z';
        $drsStart = 0.02;
        $drsLength = 0.22;
        $s1End = 0.32;
        $s2End = 0.68;
    }
    // 5. Bandung Hill Climb GP (Parahyangan) - Elevation Mountain Circuit
    elseif (str_contains($location, 'bandung') || str_contains($raceName, 'bandung')) {
        $circuitLayoutName = 'Bandung Hillside Mountain Circuit (Elevation Mountain Layout)';
        $circuitDesc = 'High elevation mountain circuit through Parahyangan hills with steep staged hairpins, long parabolic curves, and valley descent straight.';
        $circuitSvgPath = 'M 90 260 L 380 260 C 420 260 450 295 500 295 L 680 295 C 730 295 750 260 740 220 C 730 180 670 175 620 175 L 440 175 C 380 175 350 120 400 80 C 450 40 580 40 640 60 C 700 80 730 50 670 40 L 300 40 C 210 40 140 75 140 130 C 140 190 190 210 160 240 C 130 270 70 260 90 260 Z';
        $drsStart = 0.03;
        $drsLength = 0.22;
        $s1End = 0.36;
        $s2End = 0.72;
    }
    // Default Fallback
    else {
        $circuitLayoutName = 'Championship Grand Circuit (' . ucfirst($trackType) . ')';
        $circuitDesc = 'Official FIA standard championship circuit layout with mixed technical and aerodynamic sectors.';
        $circuitSvgPath = 'M 110 270 L 620 270 C 675 270 735 235 735 185 C 735 135 685 105 630 105 L 485 105 C 445 105 425 65 385 65 C 345 65 335 115 295 115 L 215 115 C 175 115 155 75 115 75 C 65 75 55 135 55 185 C 55 245 75 270 110 270 Z';
        $drsStart = 0.04;
        $drsLength = 0.28;
        $s1End = 0.34;
        $s2End = 0.67;
    }
@endphp

<div x-data="{
    playbackSpeed: 1,
    currentLap: 1,
    totalLaps: {{ max(1, (int)$race->laps) }},
    isFinished: false,
    timer: null,
    subLapProgress: 0.0,
    selectedDriver: null,
    trackLength: 0,
    drivers: {{ Js::from($towerStandings) }},
    lapEvents: {{ Js::from($simulation['lap_events']) }},

    init() {
        this.$nextTick(() => {
            const path = document.getElementById('race-circuit-master-path');
            if (path && typeof path.getTotalLength === 'function') {
                this.trackLength = path.getTotalLength();
            }
            this.startTicker();
        });
    },

    setSpeed(speed) {
        this.playbackSpeed = speed;
        if (!this.isFinished) {
            this.startTicker();
        }
    },

    startTicker() {
        if (this.timer) {
            cancelAnimationFrame(this.timer);
            this.timer = null;
        }
        if (this.currentLap >= this.totalLaps) {
            this.isFinished = true;
            this.subLapProgress = 1.0;
            return;
        }

        const fps = 24;
        const interval = 1000 / fps;
        let lastTime = performance.now();
        let lapAccumulator = this.subLapProgress || 0.0;

        const animate = (currentTime) => {
            if (this.isFinished) return;

            this.timer = requestAnimationFrame(animate);

            const delta = currentTime - lastTime;
            if (delta < interval) return;
            lastTime = currentTime - (delta % interval);

            const baseLapDurationMs = 6000;
            const lapDurationMs = baseLapDurationMs / this.playbackSpeed; // ms per lap (6.0s on 1X, 3.0s on 2X, 1.2s on 5X)
            lapAccumulator += delta / lapDurationMs;

            while (lapAccumulator >= 1.0 && this.currentLap < this.totalLaps) {
                lapAccumulator -= 1.0;
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
                this.subLapProgress = 1.0;
                if (this.timer) {
                    cancelAnimationFrame(this.timer);
                    this.timer = null;
                }
                return;
            }

            this.subLapProgress = lapAccumulator;
        };

        this.timer = requestAnimationFrame(animate);
    },

    instantSkip() {
        if (this.timer) {
            cancelAnimationFrame(this.timer);
            this.timer = null;
        }
        this.currentLap = this.totalLaps;
        this.subLapProgress = 1.0;
        this.isFinished = true;
        this.$nextTick(() => {
            const feed = document.getElementById('telemetry-radio-feed');
            if (feed) {
                feed.scrollTop = feed.scrollHeight;
            }
        });
    },

    getCarCoordinates(carIndex, car) {
        const defaultPos = { x: 120, y: 280 };
        const path = document.getElementById('race-circuit-master-path');
        if (!path || typeof path.getTotalLength !== 'function') {
            return defaultPos;
        }
        const totalLength = path.getTotalLength();
        if (!totalLength || totalLength <= 0 || isNaN(totalLength)) {
            return defaultPos;
        }

        const pos = (car && typeof car.position === 'number') ? car.position : (carIndex + 1);
        const avgLapTime = 75.0; // Reference lap time in seconds
        let distance;
        let gapSec = 0;

        if (car && typeof car.gap_seconds === 'number') {
            gapSec = Math.max(0, car.gap_seconds);
        } else if (car && car.gap && car.gap !== 'LEADER') {
            const parsed = parseFloat(String(car.gap).replace(/[^0-9.]/g, ''));
            gapSec = !isNaN(parsed) ? Math.max(0, parsed) : (pos - 1) * 1.8;
        } else {
            gapSec = (pos - 1) * 1.8;
        }

        if (this.isFinished) {
            // Stack cars cleanly across finish line in finishing order
            const finishOffset = (pos - 1) * (totalLength * 0.015);
            distance = Math.max(0, (totalLength * 0.985) - finishOffset);
        } else {
            const avgLapTime = 75.0;
            const fullGapOffset = gapSec / avgLapTime;
            const currentLapNum = Math.max(1, this.currentLap);
            const leaderProgress = this.subLapProgress || 0.0;

            // Progres kumulatif absolut
            const leaderAbsLap = (currentLapNum - 1) + leaderProgress;

            // Pada Lap 1 & Lap 2, mobil tertahan dalam pack dinamis di belakang mobil depannya
            let effectiveGap;
            if (currentLapNum === 1) {
                // Batasi jarak maksimal pack di Lap 1 agar mobil belakang tidak pernah melompat ke sektor depan
                const packRatio = Math.min(1.0, 0.15 + (leaderProgress * 0.85));
                const maxSpread = Math.min(0.65, leaderProgress * 0.85);
                const rawGap = (pos - 1) * (maxSpread / 20);
                effectiveGap = Math.min(fullGapOffset * packRatio, rawGap);
            } else {
                effectiveGap = fullGapOffset;
            }

            let carAbsLap = leaderAbsLap - effectiveGap;

            // Pada Lap 1, mobil yang belum start tidak boleh bernilai negatif atau wrap
            if (currentLapNum === 1 && carAbsLap < 0) {
                const gridSpacing = (pos - 1) * 0.002;
                carAbsLap = Math.max(0.001, 0.015 - gridSpacing);
            }

            let trackFraction = ((carAbsLap % 1.0) + 1.0) % 1.0;

            // Strict Ordering Guard: Di Lap 1 & Lap 2, mobil di belakang P1 (pos > 1) 
            // harus selalu berada di belakang P1 sepanjang lintasan
            if (currentLapNum <= 2 && pos > 1) {
                const minTrailingGap = (pos - 1) * 0.015;
                if (leaderProgress >= minTrailingGap) {
                    // P1 sudah cukup jauh, mobil mengikuti di belakang P1
                    trackFraction = Math.max(0.001, Math.min(trackFraction, leaderProgress - minTrailingGap));
                } else {
                    // P1 masih di awal lap, mobil tertahan di akhir lap sebelumnya beriringan
                    const wrapAnchor = 1.0 + leaderProgress - minTrailingGap;
                    trackFraction = wrapAnchor % 1.0;
                }
            }

            if (isNaN(trackFraction) || trackFraction < 0) {
                trackFraction = 0;
            }
            distance = trackFraction * totalLength;
        }

        distance = Math.max(0, Math.min(totalLength, distance));

        try {
            const pt = path.getPointAtLength(distance);
            if (!pt || isNaN(pt.x) || isNaN(pt.y)) {
                return defaultPos;
            }

            // Strictly locked to centerline of the 20px asphalt track (0px lateral shift)
            return {
                x: Math.round(pt.x * 10) / 10,
                y: Math.round(pt.y * 10) / 10
            };
        } catch (e) {
            return defaultPos;
        }
    },

    getDriverEvents(driverName) {
        if (!driverName) return [];
        return this.lapEvents.filter(e => e.message && (e.message.toLowerCase().includes(driverName.toLowerCase())));
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

    <!-- Official SVG 2D Circuit Map Visualizer (20 Cars GPS Tracking) -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-5 shadow-2xl font-mono relative overflow-hidden">
        <!-- Top Status & Sector Telemetry Row -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-zinc-800">
            <div class="flex items-center gap-3">
                <template x-if="!isFinished">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-emerald-950/90 border border-emerald-500/50 text-emerald-400 text-xs font-bold uppercase shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>LIVE 2D CIRCUIT GPS &bull; LAP <span x-text="currentLap"></span> / {{ $race->laps }}</span>
                    </span>
                </template>
                <template x-if="isFinished">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-purple-950/90 border border-purple-500/50 text-purple-300 text-xs font-black uppercase shadow-sm">
                        <span>🏁 CHECKERED FLAG &bull; GRAND PRIX FINISHED</span>
                    </span>
                </template>

                <span class="text-xs text-zinc-400 hidden sm:inline">&bull;</span>
                <span class="text-xs text-zinc-300 font-bold hidden sm:inline">{{ $circuitLayoutName }}</span>
            </div>

            <!-- Active Sector & DRS Status Badges -->
            <div class="flex items-center gap-2 text-xs">
                <!-- Sector Active Indicator -->
                <div class="px-2.5 py-1 rounded bg-zinc-950 border border-zinc-800 text-[11px] text-zinc-400 flex items-center gap-1.5">
                    <span class="text-zinc-500 uppercase">Sector:</span>
                    <span class="text-amber-400 font-black" x-text="isFinished ? 'S3/FINISH' : (subLapProgress < 0.35 ? 'S1 SPEED TRAP' : (subLapProgress < 0.70 ? 'S2 INFIELD APEX' : 'S3 CHICANE'))"></span>
                </div>

                <!-- DRS Status -->
                <div class="px-2.5 py-1 rounded bg-zinc-950 border border-zinc-800 text-[11px] text-zinc-400 flex items-center gap-1.5">
                    <span class="text-zinc-500 uppercase">DRS:</span>
                    <span :class="isFinished ? 'text-zinc-500' : 'text-emerald-400 font-black animate-pulse'" x-text="isFinished ? 'CLOSED' : 'ENABLED'"></span>
                </div>

                <!-- Telemetry Progress -->
                <div class="px-2.5 py-1 rounded bg-zinc-950 border border-zinc-800 text-[11px] text-zinc-400 hidden md:flex items-center gap-1.5">
                    <span class="text-zinc-500 uppercase">Race Dist:</span>
                    <strong class="text-white" x-text="isFinished ? '100%' : (Math.min(100, Math.round(((currentLap - 1 + subLapProgress) / totalLaps) * 100)) + '%')"></strong>
                </div>
            </div>
        </div>

        <!-- 2D SVG Circuit Map Canvas -->
        <div class="relative w-full h-72 sm:h-80 md:h-96 bg-zinc-950/90 rounded-lg border border-zinc-800/90 overflow-hidden shadow-inner flex items-center justify-center p-2">
            <!-- Background Circuit Grid -->
            <div class="absolute inset-0 bg-[radial-gradient(#27272a_1px,transparent_1px)] [background-size:24px_24px] opacity-25 pointer-events-none"></div>

            <!-- Weather Indicator Watermark -->
            <div class="absolute top-3 right-3 text-[10px] text-zinc-500 font-mono flex items-center gap-1.5 pointer-events-none z-0">
                <span>SURFACE:</span>
                <span class="font-bold text-zinc-300">{{ $race->weather === 'wet' ? '🌧️ WET RAIN' : '☀️ DRY ASPHALT' }}</span>
            </div>

            <!-- SVG Layout -->
            <svg viewBox="0 0 800 350" class="w-full h-full relative z-10 select-none">
                <defs>
                    <!-- Wet Asphalt Filter -->
                    <filter id="wetGlow" x="-20%" y="-20%" width="140%" height="140%">
                        <feGaussianBlur stdDeviation="3" result="blur" />
                        <feComposite in="SourceGraphic" in2="blur" operator="over" />
                    </filter>

                    @if($race->weather === 'wet')
                        <!-- Ambient Slanted Rain Streaks Pattern -->
                        <pattern id="rainPattern" width="32" height="32" patternUnits="userSpaceOnUse" patternTransform="rotate(-25)">
                            <line x1="4" y1="2" x2="4" y2="20" stroke="#38bdf8" stroke-width="1.4" stroke-linecap="round" opacity="0.5" />
                            <line x1="20" y1="12" x2="20" y2="28" stroke="#7dd3fc" stroke-width="1.1" stroke-linecap="round" opacity="0.38" />
                        </pattern>
                    @endif
                </defs>

                <!-- 1. Track Base Surface (Dark Asphalt) -->
                <path d="{{ $circuitSvgPath }}"
                      fill="none"
                      stroke="#18181b"
                      stroke-width="26"
                      stroke-linecap="round"
                      stroke-linejoin="round" />
                
                <path d="{{ $circuitSvgPath }}"
                      fill="none"
                      stroke="#27272a"
                      stroke-width="20"
                      stroke-linecap="round"
                      stroke-linejoin="round" />

                <path d="{{ $circuitSvgPath }}"
                      fill="none"
                      stroke="#3f3f46"
                      stroke-width="1.5"
                      stroke-dasharray="6,8"
                      opacity="0.5" />

                <!-- 2. Sector 1 Outline (Cyan) -->
                <path d="{{ $circuitSvgPath }}"
                      fill="none"
                      stroke="#06b6d4"
                      stroke-width="4"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      :stroke-dasharray="(trackLength > 0 ? (trackLength * {{ $s1End }}) : 300) + ' ' + (trackLength > 0 ? trackLength : 1000)"
                      stroke-dashoffset="0"
                      opacity="0.85" />

                <!-- 3. Sector 2 Outline (Amber) -->
                <path d="{{ $circuitSvgPath }}"
                      fill="none"
                      stroke="#f59e0b"
                      stroke-width="4"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      :stroke-dasharray="(trackLength > 0 ? (trackLength * {{ $s2End - $s1End }}) : 300) + ' ' + (trackLength > 0 ? trackLength : 1000)"
                      :stroke-dashoffset="-(trackLength * {{ $s1End }})"
                      opacity="0.85" />

                <!-- 4. Sector 3 Outline (Purple) -->
                <path d="{{ $circuitSvgPath }}"
                      fill="none"
                      stroke="#a855f7"
                      stroke-width="4"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      :stroke-dasharray="(trackLength > 0 ? (trackLength * {{ 1.0 - $s2End }}) : 300) + ' ' + (trackLength > 0 ? trackLength : 1000)"
                      :stroke-dashoffset="-(trackLength * {{ $s2End }})"
                      opacity="0.85" />

                <!-- 5. DRS Activation Zone (Neon Green Glow) -->
                <path d="{{ $circuitSvgPath }}"
                      fill="none"
                      stroke="#10b981"
                      stroke-width="5"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      :stroke-dasharray="(trackLength > 0 ? (trackLength * {{ $drsLength }}) : 200) + ' ' + (trackLength > 0 ? trackLength : 1000)"
                      :stroke-dashoffset="-(trackLength * {{ $drsStart }})"
                      class="animate-pulse" />

                <!-- 6. Single Master Reference Path for Alpine.js coordinate calculation -->
                <path id="race-circuit-master-path"
                      d="{{ $circuitSvgPath }}"
                      fill="none"
                      stroke="transparent"
                      stroke-width="1" />

                <!-- 7. Start / Finish Line Banner -->
                <g transform="translate(120, 280)">
                    <line x1="0" y1="-14" x2="0" y2="14" stroke="#ffffff" stroke-width="3" stroke-dasharray="3,3" />
                    <text x="6" y="26" fill="#a1a1aa" font-size="9" font-family="monospace" font-weight="bold">START / FINISH 🏁</text>
                </g>

                <!-- Wet Weather Ambient Rain Overlay (if wet) -->
                @if($race->weather === 'wet')
                    <rect width="800" height="350" fill="url(#rainPattern)" class="pointer-events-none" opacity="0.75" />
                @endif

                <!-- 8. GPS Car Markers (20 Cars Native SVG Elements with Physics Spread & Lateral Racing Line) -->
                <g id="car-markers">
                    @foreach($towerStandings as $idx => $driver)
                        <g :transform="'translate(' + getCarCoordinates({{ $idx }}, {{ Js::from($driver) }}).x + ',' + getCarCoordinates({{ $idx }}, {{ Js::from($driver) }}).y + ')'"
                           class="cursor-pointer"
                           @click="selectedDriver = {{ Js::from($driver) }}">
                            @if($driver['is_player'] && $driver['slot'] === 1)
                                <!-- Car #1 (Player) -->
                                <circle cx="0" cy="0" r="14" fill="#06b6d4" opacity="0.35" class="animate-ping" />
                                <circle cx="0" cy="0" r="9" fill="#083344" stroke="#06b6d4" stroke-width="2.5" />
                                <circle cx="0" cy="0" r="4.5" fill="#22d3ee" />
                                <rect x="-11" y="-23" width="22" height="13" rx="3" fill="#06b6d4" stroke="#ffffff" stroke-width="1" />
                                <text x="0" y="-14" fill="#000000" font-size="8.5" font-family="monospace" font-weight="900" text-anchor="middle">C1</text>
                            @elseif($driver['is_player'] && $driver['slot'] === 2)
                                <!-- Car #2 (Player) -->
                                <circle cx="0" cy="0" r="14" fill="#3b82f6" opacity="0.35" class="animate-ping" />
                                <circle cx="0" cy="0" r="9" fill="#172554" stroke="#3b82f6" stroke-width="2.5" />
                                <circle cx="0" cy="0" r="4.5" fill="#60a5fa" />
                                <rect x="-11" y="-23" width="22" height="13" rx="3" fill="#3b82f6" stroke="#ffffff" stroke-width="1" />
                                <text x="0" y="-14" fill="#ffffff" font-size="8.5" font-family="monospace" font-weight="900" text-anchor="middle">C2</text>
                            @elseif($driver['position'] === 1)
                                <!-- P1 Leader (if AI) -->
                                <circle cx="0" cy="0" r="12" fill="#eab308" opacity="0.25" class="animate-pulse" />
                                <circle cx="0" cy="0" r="8" fill="#422006" stroke="#eab308" stroke-width="2" />
                                <circle cx="0" cy="0" r="4" fill="#fde047" />
                                <rect x="-11" y="-21" width="22" height="12" rx="3" fill="#eab308" stroke="#000000" stroke-width="0.5" />
                                <text x="0" y="-12" fill="#000000" font-size="8" font-family="monospace" font-weight="900" text-anchor="middle">P1</text>
                            @else
                                <!-- Other AI Competitors -->
                                <circle cx="0" cy="0" r="5.5" fill="#27272a" stroke="#71717a" stroke-width="1.5" class="hover:stroke-amber-400 hover:fill-amber-950 transition-colors" />
                                <circle cx="0" cy="0" r="2.5" fill="#a1a1aa" />
                                <title>{{ 'P' . $driver['position'] . ' ' . $driver['driver_name'] . ' (' . $driver['team_name'] . ')' }}</title>
                            @endif
                        </g>
                    @endforeach
                </g>
            </svg>
        </div>

        <!-- Sector Map Legend & Car Identifier Footer -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3 pt-3 border-t border-zinc-800 text-[11px] text-zinc-400">
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                <span>SECTOR 1 (SPEED TRAP)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                <span>SECTOR 2 (INFIELD APEX)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-400"></span>
                <span>SECTOR 3 (CHICANE)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                <span>DRS SPEED ZONE ⚡</span>
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
                                Grand Prix Final Classification &bull; Click any driver row to inspect live telemetry &bull; 20 Cars Grid
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-[10px] text-zinc-400 bg-zinc-950 border border-zinc-800 px-2 py-1 rounded">
                            LAP <span class="text-amber-400 font-bold" x-text="currentLap"></span> / {{ $race->laps }}
                        </span>
                        <span class="text-[10px] text-emerald-400 bg-emerald-950/80 border border-emerald-500/40 px-2 py-1 rounded font-bold">
                            GPS SYNC: LIVE
                        </span>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="text-[9px] text-zinc-400 uppercase bg-zinc-950/80 border-y border-zinc-800 font-mono">
                            <tr>
                                <th class="py-2.5 px-2.5 text-center w-12">POS</th>
                                <th class="py-2.5 px-2 text-center w-10">GAIN</th>
                                <th class="py-2.5 px-3 min-w-[220px]">DRIVER / CONSTRUCTOR</th>
                                <th class="py-2.5 px-2.5 text-center w-16">TYRE</th>
                                <th class="py-2.5 px-2 text-right w-16">S1</th>
                                <th class="py-2.5 px-2 text-right w-16">S2</th>
                                <th class="py-2.5 px-2 text-right w-16">S3</th>
                                <th class="py-2.5 px-2.5 text-right w-20">GAP</th>
                                <th class="py-2.5 px-2.5 text-right min-w-[95px]">INTERVAL</th>
                                <th class="py-2.5 px-3 text-right min-w-[105px]">BEST LAP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/50">
                            @foreach($towerStandings as $row)
                                <tr @click="selectedDriver = {{ Js::from($row) }}"
                                    class="transition-colors cursor-pointer {{ $row['is_player'] ? ($row['slot'] === 1 ? 'bg-cyan-950/30 hover:bg-cyan-950/50 border-l-4 border-l-cyan-400' : 'bg-blue-950/30 hover:bg-blue-950/50 border-l-4 border-l-blue-400') : 'hover:bg-zinc-800/50' }}">
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
                                    <td class="py-2.5 px-2 text-center text-[10px] font-bold font-mono tabular-nums">
                                        @if($row['gain_loss'] > 0)
                                            <span class="text-emerald-400">▲+{{ $row['gain_loss'] }}</span>
                                        @elseif($row['gain_loss'] < 0)
                                            <span class="text-rose-400">▼{{ $row['gain_loss'] }}</span>
                                        @else
                                            <span class="text-zinc-500">-</span>
                                        @endif
                                    </td>

                                    <!-- Driver & Team Identity with Battle Alert Badge -->
                                    <td class="py-2.5 px-3 min-w-[220px]">
                                        <div class="flex flex-col min-w-0">
                                            <div class="flex items-center gap-1.5 min-w-0 whitespace-nowrap">
                                                <span class="font-bold tracking-tight truncate {{ $row['is_player'] ? 'text-white' : 'text-zinc-200' }}">{{ $row['driver_name'] }}</span>
                                                @if($row['is_player'])
                                                    @if($row['slot'] === 1)
                                                        <span class="px-1.5 py-0.5 text-[9px] font-black leading-none uppercase tracking-wider rounded bg-cyan-500 text-black shrink-0">CAR #1 (YOU)</span>
                                                    @else
                                                        <span class="px-1.5 py-0.5 text-[9px] font-black leading-none uppercase tracking-wider rounded bg-blue-500 text-white shrink-0">CAR #2 (YOU)</span>
                                                    @endif
                                                @endif
                                                @if($row['position'] > 1 && $row['interval_sec'] < 0.8)
                                                    <span class="px-1.5 py-0.5 text-[9px] font-black leading-none uppercase tracking-wider rounded bg-rose-950/90 text-rose-300 border border-rose-500/60 animate-pulse shrink-0 shadow-sm shadow-rose-900/40" title="Wheel-to-Wheel Battle (< 0.8s)">
                                                        ⚔️ BATTLE
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-[10px] text-zinc-400 uppercase font-normal mt-0.5 truncate whitespace-nowrap">
                                                {{ $row['team_name'] }} <span class="text-zinc-500">&bull; {{ $row['car_name'] }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Tire Status (Compound & Age) -->
                                    <td class="py-2.5 px-2.5 text-center font-mono">
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
                                            <span class="text-[9px] text-zinc-500 font-semibold tabular-nums">L<span x-text="currentLap"></span></span>
                                        </div>
                                    </td>

                                    <!-- Micro-Sector 1 -->
                                    <td class="py-2.5 px-2 text-right font-mono tabular-nums tracking-tight text-xs">
                                        @if($row['is_s1_fastest'])
                                            <span class="text-purple-300 bg-purple-950/80 border border-purple-500/40 px-1 py-0.5 rounded font-black shadow-sm" title="Overall Fastest S1">{{ $row['s1'] }}</span>
                                        @elseif($row['position'] <= 3)
                                            <span class="text-amber-300 font-bold">{{ $row['s1'] }}</span>
                                        @else
                                            <span class="text-zinc-400 font-medium">{{ $row['s1'] }}</span>
                                        @endif
                                    </td>

                                    <!-- Micro-Sector 2 -->
                                    <td class="py-2.5 px-2 text-right font-mono tabular-nums tracking-tight text-xs">
                                        @if($row['is_s2_fastest'])
                                            <span class="text-purple-300 bg-purple-950/80 border border-purple-500/40 px-1 py-0.5 rounded font-black shadow-sm" title="Overall Fastest S2">{{ $row['s2'] }}</span>
                                        @elseif($row['position'] <= 3)
                                            <span class="text-amber-300 font-bold">{{ $row['s2'] }}</span>
                                        @else
                                            <span class="text-zinc-400 font-medium">{{ $row['s2'] }}</span>
                                        @endif
                                    </td>

                                    <!-- Micro-Sector 3 -->
                                    <td class="py-2.5 px-2 text-right font-mono tabular-nums tracking-tight text-xs">
                                        @if($row['is_s3_fastest'])
                                            <span class="text-purple-300 bg-purple-950/80 border border-purple-500/40 px-1 py-0.5 rounded font-black shadow-sm" title="Overall Fastest S3">{{ $row['s3'] }}</span>
                                        @elseif($row['position'] <= 3)
                                            <span class="text-amber-300 font-bold">{{ $row['s3'] }}</span>
                                        @else
                                            <span class="text-zinc-400 font-medium">{{ $row['s3'] }}</span>
                                        @endif
                                    </td>

                                    <!-- Gap to Leader -->
                                    <td class="py-2.5 px-2.5 text-right font-mono tabular-nums tracking-tight text-xs font-bold {{ $row['position'] === 1 ? 'text-emerald-400' : 'text-zinc-300' }}">
                                        {{ $row['gap'] }}
                                    </td>

                                    <!-- Interval to Car Ahead -->
                                    <td class="py-2.5 px-2.5 text-right font-mono tabular-nums tracking-tight text-xs">
                                        <div class="inline-flex items-center justify-end gap-1.5 whitespace-nowrap">
                                            <span class="font-bold {{ $row['position'] === 1 ? 'text-emerald-400' : 'text-zinc-300' }}">
                                                {{ $row['interval_text'] }}
                                            </span>
                                            @if($row['position'] > 1 && $row['interval_sec'] < 1.0)
                                                <span class="px-1 py-0.5 text-[8px] rounded bg-emerald-950 text-emerald-400 border border-emerald-500/50 font-black animate-pulse leading-none" title="DRS Zone Active (< 1.0s)">
                                                    DRS
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Best Lap & FL Indicator -->
                                    <td class="py-2.5 px-3 text-right font-mono tabular-nums tracking-tight text-xs">
                                        <div class="inline-flex items-center justify-end gap-1.5 whitespace-nowrap">
                                            @if($row['is_overall_fastest'])
                                                <span class="text-[9px] bg-purple-950 text-purple-300 border border-purple-500/60 px-1.5 py-0.5 rounded font-black inline-flex items-center gap-1 shadow-sm shadow-purple-500/20 leading-none">
                                                    <span>🟣 FL</span>
                                                    <span>{{ $row['fastest_lap'] }}</span>
                                                </span>
                                            @else
                                                <span class="text-zinc-400 font-medium">{{ $row['fastest_lap'] }}</span>
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
                        <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-purple-500"></span><span>Fastest Sector</span></div>
                        <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400"></span><span>Personal/Podium Best</span></div>
                        <div class="flex items-center gap-1"><span class="px-1 py-0.2 rounded bg-rose-950 text-rose-300 border border-rose-500/40 text-[8px] font-bold">⚔️ BATTLE</span><span>Gap &lt; 0.8s</span></div>
                        <div class="flex items-center gap-1"><span class="px-1 py-0.2 rounded bg-emerald-950 text-emerald-400 border border-emerald-500/40 text-[8px] font-bold">DRS</span><span>Interval &lt; 1.0s</span></div>
                    </div>
                    <div>
                        <span>Click row to open Telemetry Inspector &bull; FIA Timing Engine v2.5</span>
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

    <!-- Driver Telemetry Inspection Drawer / Slide-Over Modal -->
    <div x-show="selectedDriver"
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden font-mono"
         aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="selectedDriver"
             x-transition:enter="ease-in-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in-out duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="selectedDriver = null"
             class="fixed inset-0 bg-black/75 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div x-show="selectedDriver"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="w-screen max-w-md bg-zinc-950 border-l border-zinc-800 shadow-2xl flex flex-col">
                
                <!-- Drawer Header -->
                <div class="p-6 bg-zinc-900 border-b border-zinc-800 relative">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center font-black text-xl border shadow-inner"
                                 :class="selectedDriver?.is_player ? (selectedDriver?.slot === 1 ? 'bg-cyan-950 text-cyan-300 border-cyan-500/50' : 'bg-blue-950 text-blue-300 border-blue-500/50') : 'bg-zinc-900 text-amber-400 border-zinc-700'">
                                <span x-text="'P' + selectedDriver?.position"></span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-black text-white uppercase tracking-tight" x-text="selectedDriver?.driver_name"></h3>
                                    <template x-if="selectedDriver?.is_player">
                                        <span class="text-[8px] px-1.5 py-0.5 rounded font-black uppercase"
                                              :class="selectedDriver?.slot === 1 ? 'bg-cyan-500 text-black' : 'bg-blue-500 text-white'"
                                              x-text="selectedDriver?.slot === 1 ? 'CAR #1 (YOU)' : 'CAR #2 (YOU)'"></span>
                                    </template>
                                </div>
                                <p class="text-xs text-zinc-400 uppercase mt-0.5" x-text="selectedDriver?.team_name + ' • ' + selectedDriver?.car_name"></p>
                            </div>
                        </div>
                        <button type="button" @click="selectedDriver = null" class="text-zinc-400 hover:text-white p-1.5 rounded-lg hover:bg-zinc-800 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Drawer Body -->
                <div class="p-6 space-y-6 flex-1 overflow-y-auto">
                    <!-- Telemetry Status Grid -->
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="bg-zinc-900 border border-zinc-800 rounded p-3">
                            <span class="text-[9px] text-zinc-500 uppercase block font-bold">Gap to Leader</span>
                            <span class="text-sm font-black text-white mt-1 block" x-text="selectedDriver?.gap"></span>
                        </div>
                        <div class="bg-zinc-900 border border-zinc-800 rounded p-3">
                            <span class="text-[9px] text-zinc-500 uppercase block font-bold">Interval to Ahead</span>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="text-sm font-black text-white" x-text="selectedDriver?.interval_text"></span>
                                <template x-if="selectedDriver?.position > 1 && selectedDriver?.interval_sec < 0.8">
                                    <span class="text-[8px] bg-rose-950 text-rose-300 border border-rose-500/50 px-1 py-0.5 rounded font-bold">⚔️ BATTLE</span>
                                </template>
                            </div>
                        </div>
                        <div class="bg-zinc-900 border border-zinc-800 rounded p-3">
                            <span class="text-[9px] text-zinc-500 uppercase block font-bold">Tire Compound & Age</span>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="w-2.5 h-2.5 rounded-full"
                                      :class="{
                                          'bg-red-500': selectedDriver?.tire_compound === 'soft',
                                          'bg-amber-400': selectedDriver?.tire_compound === 'medium',
                                          'bg-zinc-200': selectedDriver?.tire_compound === 'hard',
                                          'bg-blue-500': selectedDriver?.tire_compound === 'wet'
                                      }"></span>
                                <span class="text-xs font-bold uppercase text-white" x-text="(selectedDriver?.tire_compound || 'medium') + ' (Lap ' + currentLap + ')'"></span>
                            </div>
                        </div>
                        <div class="bg-zinc-900 border border-zinc-800 rounded p-3">
                            <span class="text-[9px] text-zinc-500 uppercase block font-bold">ECU / Driving Mode</span>
                            <span class="text-xs font-bold uppercase mt-1 block"
                                  :class="selectedDriver?.driving_mode === 'push' ? 'text-red-400' : (selectedDriver?.driving_mode === 'conserve' ? 'text-emerald-400' : 'text-zinc-300')"
                                  x-text="selectedDriver?.driving_mode ? (selectedDriver.driving_mode === 'push' ? '⚡ PUSH' : (selectedDriver.driving_mode === 'conserve' ? '🛡️ CONSERVE' : '⚙️ BALANCED')) : '⚙️ BALANCED'"></span>
                        </div>
                    </div>

                    <!-- Micro-Sectors Breakdown -->
                    <div class="bg-zinc-900 border border-zinc-800 rounded p-4">
                        <h4 class="text-xs font-black uppercase text-zinc-300 mb-3 flex items-center justify-between">
                            <span>Micro-Sector Telemetry</span>
                            <span class="text-[9px] text-zinc-500 font-normal">SPEED SPLITS</span>
                        </h4>
                        <div class="grid grid-cols-3 gap-2 text-center text-xs">
                            <div class="bg-zinc-950 border rounded p-2.5" :class="selectedDriver?.is_s1_fastest ? 'border-purple-500/50 bg-purple-950/40 text-purple-300' : 'border-zinc-800 text-zinc-300'">
                                <span class="text-[8px] text-zinc-500 block uppercase font-bold">Sector 1</span>
                                <span class="font-black text-sm" x-text="selectedDriver?.s1 + 's'"></span>
                            </div>
                            <div class="bg-zinc-950 border rounded p-2.5" :class="selectedDriver?.is_s2_fastest ? 'border-purple-500/50 bg-purple-950/40 text-purple-300' : 'border-zinc-800 text-zinc-300'">
                                <span class="text-[8px] text-zinc-500 block uppercase font-bold">Sector 2</span>
                                <span class="font-black text-sm" x-text="selectedDriver?.s2 + 's'"></span>
                            </div>
                            <div class="bg-zinc-950 border rounded p-2.5" :class="selectedDriver?.is_s3_fastest ? 'border-purple-500/50 bg-purple-950/40 text-purple-300' : 'border-zinc-800 text-zinc-300'">
                                <span class="text-[8px] text-zinc-500 block uppercase font-bold">Sector 3</span>
                                <span class="font-black text-sm" x-text="selectedDriver?.s3 + 's'"></span>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-zinc-800/80 flex items-center justify-between text-xs">
                            <span class="text-zinc-400">Personal Best Lap:</span>
                            <span class="font-black text-amber-400" x-text="selectedDriver?.fastest_lap"></span>
                        </div>
                    </div>

                    <!-- Driver Radio & Incident Log Filter -->
                    <div class="bg-zinc-900 border border-zinc-800 rounded p-4">
                        <h4 class="text-xs font-black uppercase text-zinc-300 mb-3 flex items-center justify-between">
                            <span>Driver Radio & Log</span>
                            <span class="text-[9px] text-zinc-500 font-normal">FILTERED FEED</span>
                        </h4>
                        <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                            <template x-for="(ev, eIdx) in getDriverEvents(selectedDriver?.driver_name)" :key="eIdx">
                                <div class="p-2.5 rounded bg-zinc-950 border border-zinc-800 text-xs">
                                    <div class="flex items-center justify-between text-[9px] font-bold text-zinc-500 mb-1">
                                        <span x-text="'LAP ' + ev.lap"></span>
                                        <span class="uppercase" x-text="ev.type"></span>
                                    </div>
                                    <p class="text-zinc-300 text-[11px]" x-text="ev.message"></p>
                                </div>
                            </template>
                            <template x-if="getDriverEvents(selectedDriver?.driver_name).length === 0">
                                <p class="text-xs text-zinc-500 italic text-center py-4">No specific incidents or radio transcripts recorded for this driver.</p>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Drawer Footer -->
                <div class="p-4 bg-zinc-900 border-t border-zinc-800">
                    <button type="button" @click="selectedDriver = null" class="w-full py-2.5 rounded bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-bold uppercase transition cursor-pointer">
                        Close Inspector
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
