<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Race;
use App\Models\Team;

class RaceSimulationService
{
    /**
     * AI Competitor constructors and driver names pool for grid generation.
     *
     * @var array<int, array{team: string, driver: string, car: string, base_ovr: int}>
     */
    /**
     * AI Competitor constructors and driver pairs pool for 2-car grid generation.
     *
     * @var array<int, array{team: string, driver1: string, car1: string, ovr1: int, driver2: string, car2: string, ovr2: int}>
     */
    protected array $aiGridPool = [
        [
            'team' => 'Scuderia Veloce',
            'driver1' => 'Marco Rossi',
            'car1' => 'Veloce C26',
            'ovr1' => 74,
            'driver2' => 'Matteo Ricci',
            'car2' => 'Veloce C26',
            'ovr2' => 73,
        ],
        [
            'team' => 'Silverstone Dynamics',
            'driver1' => 'Liam Vance',
            'car1' => 'SD-08 Arrow',
            'ovr1' => 72,
            'driver2' => 'Oliver Sterling',
            'car2' => 'SD-08 Arrow',
            'ovr2' => 70,
        ],
        [
            'team' => 'AeroTech Motorsport',
            'driver1' => 'Elena Rostova',
            'car1' => 'AT-Aero Pro',
            'ovr1' => 71,
            'driver2' => 'Viktor Weber',
            'car2' => 'AT-Aero Pro',
            'ovr2' => 69,
        ],
        [
            'team' => 'Nordic Speedworks',
            'driver1' => 'Lukas Lindqvist',
            'car1' => 'Valkyrie R',
            'ovr1' => 69,
            'driver2' => 'Astrid Holm',
            'car2' => 'Valkyrie R',
            'ovr2' => 67,
        ],
        [
            'team' => 'Kronos Racing GP',
            'driver1' => 'Marcus Chen',
            'car1' => 'Kronos K9',
            'ovr1' => 68,
            'driver2' => 'Daniel Cho',
            'car2' => 'Kronos K9',
            'ovr2' => 66,
        ],
        [
            'team' => 'Apex Performance',
            'driver1' => 'Sofia Bianchi',
            'car1' => 'Apex Apex-1',
            'ovr1' => 66,
            'driver2' => 'Carlos Mendez',
            'car2' => 'Apex Apex-1',
            'ovr2' => 64,
        ],
        [
            'team' => 'Hyperion Grand Prix',
            'driver1' => 'Tariq Mansoor',
            'car1' => 'Hyperion H7',
            'ovr1' => 65,
            'driver2' => 'Andre Dubois',
            'car2' => 'Hyperion H7',
            'ovr2' => 63,
        ],
        [
            'team' => 'Blackline Racing',
            'driver1' => 'Lucas Silva',
            'car1' => 'Shadow RS',
            'ovr1' => 63,
            'driver2' => 'Mason Vance',
            'car2' => 'Shadow RS',
            'ovr2' => 61,
        ],
        [
            'team' => 'Zenith Motorsport',
            'driver1' => 'Kenji Sato',
            'car1' => 'Zenith Type-R',
            'ovr1' => 75,
            'driver2' => 'Hiroshi Tanaka',
            'car2' => 'Zenith Type-R',
            'ovr2' => 73,
        ],
    ];

    /**
     * Simulate a complete Grand Prix race with single-car or two-car entry and tactical strategy.
     *
     * @return array<string, mixed>
     */
    public function simulate(
        Race $race,
        Team $playerTeam,
        Car $playerCar,
        Driver $playerDriver,
        string $tireCompound = 'medium',
        string $drivingMode = 'balanced',
        ?Car $playerCar2 = null,
        ?Driver $playerDriver2 = null,
        string $tireCompound2 = 'medium',
        string $drivingMode2 = 'balanced'
    ): array {
        $validCompounds = ['soft', 'medium', 'hard', 'wet'];
        $validModes = ['push', 'balanced', 'conserve'];

        $tireCompound = in_array(strtolower($tireCompound), $validCompounds, true) ? strtolower($tireCompound) : 'medium';
        $drivingMode = in_array(strtolower($drivingMode), $validModes, true) ? strtolower($drivingMode) : 'balanced';

        $tireCompound2 = in_array(strtolower($tireCompound2), $validCompounds, true) ? strtolower($tireCompound2) : 'medium';
        $drivingMode2 = in_array(strtolower($drivingMode2), $validModes, true) ? strtolower($drivingMode2) : 'balanced';

        $isTwoCar = ($playerCar2 !== null && $playerDriver2 !== null);

        $totalLaps = max(5, $race->laps);
        $trackType = $race->track_type;
        $weather = $race->weather;

        // Base lap time in seconds (e.g. 75.0s - 78.0s)
        $baseLapTime = 75.0 + ($totalLaps <= 10 ? 3.0 : 0.0);
        if ($weather === 'wet') {
            $baseLapTime += 5.5; // Wet track is slower overall
        }

        // 1. Calculate Player Competitor Performance Index (0-100)
        $playerPerf1 = $this->calculatePerformanceIndex($playerCar, $playerDriver, $trackType, $weather);

        $competitors = [];

        // Player Car 1 entry
        $competitors[] = [
            'id' => 'player_1',
            'is_player' => true,
            'car_slot' => 1,
            'team_name' => $playerTeam->name,
            'driver_name' => $playerDriver->name,
            'car_name' => $playerCar->name,
            'car_id' => $playerCar->id,
            'driver_id' => $playerDriver->id,
            'perf_index' => $playerPerf1,
            'consistency' => $playerDriver->consistency,
            'reliability' => $playerCar->reliability,
            'racecraft' => $playerDriver->racecraft,
            'tire_compound' => $tireCompound,
            'driving_mode' => $drivingMode,
            'total_time' => 0.0,
            'lap_times' => [],
            'best_lap' => 999.0,
            'best_lap_num' => 1,
            'positions_by_lap' => [],
        ];

        // Player Car 2 entry (if two-car team)
        if ($isTwoCar) {
            $playerPerf2 = $this->calculatePerformanceIndex($playerCar2, $playerDriver2, $trackType, $weather);
            $competitors[] = [
                'id' => 'player_2',
                'is_player' => true,
                'car_slot' => 2,
                'team_name' => $playerTeam->name,
                'driver_name' => $playerDriver2->name,
                'car_name' => $playerCar2->name,
                'car_id' => $playerCar2->id,
                'driver_id' => $playerDriver2->id,
                'perf_index' => $playerPerf2,
                'consistency' => $playerDriver2->consistency,
                'reliability' => $playerCar2->reliability,
                'racecraft' => $playerDriver2->racecraft,
                'tire_compound' => $tireCompound2,
                'driving_mode' => $drivingMode2,
                'total_time' => 0.0,
                'lap_times' => [],
                'best_lap' => 999.0,
                'best_lap_num' => 1,
                'positions_by_lap' => [],
            ];
        }

        // AI entries: If two-car mode, add both drivers per team (18 AI cars). If legacy 1-car, add driver 1 (9 AI cars).
        foreach ($this->aiGridPool as $index => $ai) {
            // Driver 1 for AI Team
            $aiPerf1 = max(40, min(95, $ai['ovr1'] + random_int(-3, 3)));
            $aiCompound1 = $this->selectAiTire($weather);
            $aiMode1 = $this->selectAiMode();

            $competitors[] = [
                'id' => 'ai_'.($index + 1).'_1',
                'is_player' => false,
                'car_slot' => 1,
                'team_name' => $ai['team'],
                'driver_name' => $ai['driver1'],
                'car_name' => $ai['car1'],
                'car_id' => null,
                'driver_id' => null,
                'perf_index' => $aiPerf1,
                'consistency' => max(40, $aiPerf1 - random_int(0, 8)),
                'reliability' => max(50, $aiPerf1 + random_int(-5, 5)),
                'racecraft' => max(40, $aiPerf1 + random_int(-4, 4)),
                'tire_compound' => $aiCompound1,
                'driving_mode' => $aiMode1,
                'total_time' => 0.0,
                'lap_times' => [],
                'best_lap' => 999.0,
                'best_lap_num' => 1,
                'positions_by_lap' => [],
            ];

            if ($isTwoCar) {
                // Driver 2 for AI Team
                $aiPerf2 = max(40, min(95, $ai['ovr2'] + random_int(-3, 3)));
                $aiCompound2 = $this->selectAiTire($weather);
                $aiMode2 = $this->selectAiMode();

                $competitors[] = [
                    'id' => 'ai_'.($index + 1).'_2',
                    'is_player' => false,
                    'car_slot' => 2,
                    'team_name' => $ai['team'],
                    'driver_name' => $ai['driver2'],
                    'car_name' => $ai['car2'],
                    'car_id' => null,
                    'driver_id' => null,
                    'perf_index' => $aiPerf2,
                    'consistency' => max(40, $aiPerf2 - random_int(0, 8)),
                    'reliability' => max(50, $aiPerf2 + random_int(-5, 5)),
                    'racecraft' => max(40, $aiPerf2 + random_int(-4, 4)),
                    'tire_compound' => $aiCompound2,
                    'driving_mode' => $aiMode2,
                    'total_time' => 0.0,
                    'lap_times' => [],
                    'best_lap' => 999.0,
                    'best_lap_num' => 1,
                    'positions_by_lap' => [],
                ];
            }
        }

        // 3. Lap-by-Lap Simulation Loop
        $lapEvents = [];
        $totalGridCount = count($competitors);
        $strategySummary = $isTwoCar
            ? "[Car #1: {$playerDriver->name} - ".strtoupper($tireCompound).'/'.strtoupper($drivingMode)." | Car #2: {$playerDriver2->name} - ".strtoupper($tireCompound2).'/'.strtoupper($drivingMode2).']'
            : '[Strategy: '.strtoupper($tireCompound).' / '.strtoupper($drivingMode).']';

        $lapEvents[] = [
            'lap' => 1,
            'type' => 'start',
            'message' => "🟢 LIGHTS OUT! {$totalGridCount} cars roar into Turn 1 under {$weather} conditions at {$race->name}. {$strategySummary}",
        ];

        for ($lap = 1; $lap <= $totalLaps; $lap++) {
            foreach ($competitors as &$c) {
                $cCompound = $c['tire_compound'];
                $cMode = $c['driving_mode'];

                // --- A. Mode Modifiers ---
                $modePaceBonus = 0.0;
                $modeWearMultiplier = 1.0;
                $relBuffer = 0;

                if ($cMode === 'push') {
                    $modePaceBonus = -0.45; // 0.45s faster base lap time
                    $modeWearMultiplier = 1.35; // 35% faster tire wear
                    $relBuffer = -12; // Higher incident probability
                } elseif ($cMode === 'conserve') {
                    $modePaceBonus = 0.55; // 0.55s slower base lap time
                    $modeWearMultiplier = 0.70; // 30% lower tire wear
                    $relBuffer = 15; // Lower incident probability
                }

                // --- B. Compound Pace & Degradation Formula ---
                $compoundPaceDelta = 0.0;
                $degradationDelta = 0.0;
                $weatherMismatchPenalty = 0.0;

                if ($weather === 'wet') {
                    if ($cCompound !== 'wet') {
                        // Slicks on wet track: Heavy aquaplaning & zero grip
                        $weatherMismatchPenalty = 4.20;
                    }
                } else {
                    if ($cCompound === 'wet') {
                        // Wet tires on dry track: Severe overheating & rubber degradation
                        $weatherMismatchPenalty = 4.50;
                        $degradationDelta += ($lap * 0.20);
                    }
                }

                if ($cCompound === 'soft') {
                    $compoundPaceDelta = -0.85;
                    $wearThreshold = (int) floor($totalLaps * 0.35);
                    if ($lap > $wearThreshold) {
                        $excessLaps = $lap - $wearThreshold;
                        $degradationDelta = ($excessLaps * 0.18) * $modeWearMultiplier;
                    }
                } elseif ($cCompound === 'medium') {
                    $compoundPaceDelta = 0.0;
                    $wearThreshold = (int) floor($totalLaps * 0.60);
                    if ($lap > $wearThreshold) {
                        $excessLaps = $lap - $wearThreshold;
                        $degradationDelta = ($excessLaps * 0.08) * $modeWearMultiplier;
                    }
                } elseif ($cCompound === 'hard') {
                    $compoundPaceDelta = 0.50;
                    $degradationDelta = ($lap / $totalLaps) * 0.03 * $modeWearMultiplier;
                }

                // Driver consistency variance
                $varianceWindow = (100 - $c['consistency']) / 20.0;
                $variance = (random_int(-100, 100) / 100.0) * $varianceWindow;

                // Base performance index delta
                $perfBonus = ($c['perf_index'] - 50) * 0.08;

                // Reliability & lock-up check
                $effectiveReliability = max(20, min(98, $c['reliability'] + $relBuffer));
                if ($cCompound === 'soft' && $lap > ($totalLaps * 0.65)) {
                    $effectiveReliability -= 10;
                }
                if ($weatherMismatchPenalty > 0) {
                    $effectiveReliability -= 25;
                }

                $incidentDelta = 0.0;
                if (random_int(1, 100) > $effectiveReliability) {
                    $incidentDelta = random_int(8, 25) / 10.0; // 0.8s - 2.5s stumble
                    if ($c['is_player'] && $incidentDelta > 1.2) {
                        $carTag = isset($c['car_slot']) ? " [Car #{$c['car_slot']}]" : '';
                        if ($weatherMismatchPenalty > 0 && $weather === 'wet') {
                            $lapEvents[] = [
                                'lap' => $lap,
                                'type' => 'telemetry_alert',
                                'message' => "⚠️ Lap {$lap}:{$carTag} Severe aquaplaning on slick compound! {$c['driver_name']} slides off line, losing ".number_format($incidentDelta, 2).'s.',
                            ];
                        } elseif ($cMode === 'push') {
                            $lapEvents[] = [
                                'lap' => $lap,
                                'type' => 'telemetry_alert',
                                'message' => "⚠️ Lap {$lap}:{$carTag} [PUSH MODE] Aggressive braking causes front tire lock-up for {$c['driver_name']}, losing ".number_format($incidentDelta, 2).'s.',
                            ];
                        } elseif ($cCompound === 'soft' && $lap > ($totalLaps * 0.5)) {
                            $lapEvents[] = [
                                'lap' => $lap,
                                'type' => 'telemetry_alert',
                                'message' => "⚠️ Lap {$lap}:{$carTag} Soft tire degradation cliff! {$c['driver_name']} struggles with rear grip, losing ".number_format($incidentDelta, 2).'s.',
                            ];
                        } else {
                            $lapEvents[] = [
                                'lap' => $lap,
                                'type' => 'telemetry_alert',
                                'message' => "⚠️ Lap {$lap}:{$carTag} {$c['driver_name']} experiences vehicle instability, losing ".number_format($incidentDelta, 2).'s.',
                            ];
                        }
                    }
                }

                // Total calculated lap time
                $lapTime = max(55.0, $baseLapTime - $perfBonus + $compoundPaceDelta + $modePaceBonus + $degradationDelta + $weatherMismatchPenalty + $variance + $incidentDelta);

                $c['total_time'] += $lapTime;
                $c['lap_times'][] = $lapTime;

                if ($lapTime < $c['best_lap']) {
                    $c['best_lap'] = $lapTime;
                    $c['best_lap_num'] = $lap;
                }
            }
            unset($c);

            // Sort grid by cumulative race time to calculate current positions
            usort($competitors, fn ($a, $b) => $a['total_time'] <=> $b['total_time']);

            // Record lap positions & generate tactical overtake / defense events
            foreach ($competitors as $posIndex => &$c) {
                $currentPos = $posIndex + 1;
                $prevPos = $c['positions_by_lap'][$lap - 1] ?? $currentPos;
                $c['positions_by_lap'][$lap] = $currentPos;

                if ($c['is_player'] && $currentPos < $prevPos && $lap > 1) {
                    $rivalAhead = $competitors[$posIndex + 1]['driver_name'] ?? 'rival';
                    $modeTag = ($c['driving_mode'] === 'push') ? ' [PUSH ATTACK]' : '';
                    $carSlotTag = isset($c['car_slot']) ? " [Car #{$c['car_slot']}]" : '';
                    $lapEvents[] = [
                        'lap' => $lap,
                        'type' => 'overtake',
                        'message' => "⚡ Lap {$lap}:{$carSlotTag}{$modeTag} {$c['driver_name']} executes an overtake on {$rivalAhead} for P{$currentPos}!",
                    ];
                } elseif ($c['is_player'] && $currentPos > $prevPos && $lap > 1) {
                    $rivalBehind = $competitors[$posIndex - 1]['driver_name'] ?? 'rival';
                    $carSlotTag = isset($c['car_slot']) ? " [Car #{$c['car_slot']}]" : '';
                    $lapEvents[] = [
                        'lap' => $lap,
                        'type' => 'defense_loss',
                        'message' => "📉 Lap {$lap}:{$carSlotTag} {$c['driver_name']} yields P".($currentPos - 1)." to {$rivalBehind}.",
                    ];
                }
            }
            unset($c);

            // Mid-race pit-wall strategy radio event
            if ($lap === (int) floor($totalLaps / 2)) {
                $radioMsg = $isTwoCar
                    ? "📻 Lap {$lap} Pit-Wall Radio: Car #1 ({$playerDriver->name}) on ".strtoupper($tireCompound).', Car #2 ('.($playerDriver2->name ?? '').') on '.strtoupper($tireCompound2).'. Monitoring telemetry and tire thermals.'
                    : '📻 Lap {$lap} Pit-Wall Radio: [Strategy: '.strtoupper($tireCompound).' / '.strtoupper($drivingMode).'] Tire thermals and pace nominal.';

                $lapEvents[] = [
                    'lap' => $lap,
                    'type' => 'pit_radio',
                    'message' => $radioMsg,
                ];
            }
        }

        // Final Sort by Total Race Time
        usort($competitors, fn ($a, $b) => $a['total_time'] <=> $b['total_time']);

        $leaderTime = $competitors[0]['total_time'];
        $fastestOverall = null;
        $bestLapOverallTime = 999.0;

        $standings = [];
        $playerResult1 = null;
        $playerResult2 = null;

        foreach ($competitors as $rank => $c) {
            $pos = $rank + 1;
            $gap = ($rank === 0) ? 'LEADER' : '+'.number_format($c['total_time'] - $leaderTime, 3).'s';
            $formattedTotalTime = $this->formatTime($c['total_time']);
            $formattedBestLap = $this->formatLapTime($c['best_lap']);

            if ($c['best_lap'] < $bestLapOverallTime) {
                $bestLapOverallTime = $c['best_lap'];
                $fastestOverall = [
                    'driver' => $c['driver_name'],
                    'team' => $c['team_name'],
                    'time' => $formattedBestLap,
                    'lap' => $c['best_lap_num'],
                ];
            }

            $standingEntry = [
                'position' => $pos,
                'is_player' => $c['is_player'],
                'car_slot' => $c['car_slot'] ?? null,
                'driver_name' => $c['driver_name'],
                'team_name' => $c['team_name'],
                'car_name' => $c['car_name'],
                'car_id' => $c['car_id'] ?? null,
                'driver_id' => $c['driver_id'] ?? null,
                'tire_compound' => $c['tire_compound'],
                'driving_mode' => $c['driving_mode'],
                'total_time' => $formattedTotalTime,
                'total_seconds' => $c['total_time'],
                'gap' => $gap,
                'fastest_lap' => $formattedBestLap,
                'fastest_lap_seconds' => $c['best_lap'],
            ];

            $standings[] = $standingEntry;

            if ($c['is_player']) {
                if (($c['car_slot'] ?? 1) === 1) {
                    $playerResult1 = $standingEntry;
                } else {
                    $playerResult2 = $standingEntry;
                }
            }
        }

        $summaryMsg = $isTwoCar
            ? "🏁 CHECKERED FLAG! Grand Prix complete. {$standings[0]['driver_name']} takes victory. Car #1 ({$playerResult1['driver_name']}) finishes P{$playerResult1['position']} | Car #2 ({$playerResult2['driver_name']}) finishes P{$playerResult2['position']}."
            : "🏁 CHECKERED FLAG! Grand Prix complete. {$standings[0]['driver_name']} takes victory. {$playerResult1['driver_name']} finishes P{$playerResult1['position']}.";

        $lapEvents[] = [
            'lap' => $totalLaps,
            'type' => 'finish',
            'message' => $summaryMsg,
        ];

        return [
            'race' => [
                'id' => $race->id,
                'name' => $race->name,
                'location' => $race->location,
                'laps' => $totalLaps,
                'track_type' => $trackType,
                'weather' => $weather,
                'prize_pool' => $race->prize_pool,
                'entry_fee' => $race->entry_fee,
            ],
            'player_team' => [
                'id' => $playerTeam->id,
                'name' => $playerTeam->name,
                'car_name' => $playerCar->name,
                'driver_name' => $playerDriver->name,
            ],
            'is_two_car' => $isTwoCar,
            'car1' => [
                'car_id' => $playerCar->id,
                'driver_id' => $playerDriver->id,
                'car_name' => $playerCar->name,
                'driver_name' => $playerDriver->name,
                'tire_compound' => $tireCompound,
                'driving_mode' => $drivingMode,
            ],
            'car2' => $isTwoCar ? [
                'car_id' => $playerCar2->id,
                'driver_id' => $playerDriver2->id,
                'car_name' => $playerCar2->name,
                'driver_name' => $playerDriver2->name,
                'tire_compound' => $tireCompound2,
                'driving_mode' => $drivingMode2,
            ] : null,
            'tactics' => [
                'tire_compound' => $tireCompound,
                'driving_mode' => $drivingMode,
            ],
            'tire_compound' => $tireCompound,
            'driving_mode' => $drivingMode,
            'standings' => $standings,
            'player_result' => $playerResult1, // Backward-compatible single-car accessor
            'player_result_1' => $playerResult1,
            'player_result_2' => $playerResult2,
            'fastest_lap_overall' => $fastestOverall,
            'lap_events' => $lapEvents,
        ];
    }

    /**
     * Helper to select tactical AI tire compound.
     */
    protected function selectAiTire(string $weather): string
    {
        if ($weather === 'wet') {
            return (random_int(1, 100) <= 85) ? 'wet' : (random_int(1, 2) === 1 ? 'medium' : 'soft');
        }

        $roll = random_int(1, 100);

        return $roll <= 45 ? 'medium' : ($roll <= 80 ? 'soft' : 'hard');
    }

    /**
     * Helper to select tactical AI driving mode.
     */
    protected function selectAiMode(): string
    {
        $modeRoll = random_int(1, 100);

        return $modeRoll <= 60 ? 'balanced' : ($modeRoll <= 85 ? 'push' : 'conserve');
    }

    /**
     * Calculate performance score based on car, driver, track type, and weather.
     */
    public function calculatePerformanceIndex(Car $car, Driver $driver, string $trackType, string $weather): int
    {
        if ($trackType === 'high_speed') {
            // Speed and acceleration dominant
            $carScore = ($car->speed * 0.40) + ($car->acceleration * 0.30) + ($car->handling * 0.15) + ($car->braking * 0.15);
            $driverScore = ($driver->pace * 0.40) + ($driver->racecraft * 0.30) + ($driver->consistency * 0.15) + ($driver->experience * 0.15);
        } elseif ($trackType === 'technical') {
            // Handling and cornering dominant
            $carScore = ($car->handling * 0.35) + ($car->braking * 0.30) + ($car->acceleration * 0.20) + ($car->speed * 0.15);
            $driverScore = ($driver->cornering * 0.35) + ($driver->consistency * 0.25) + ($driver->racecraft * 0.25) + ($driver->pace * 0.15);
        } else {
            // Balanced
            $carScore = ($car->speed * 0.25) + ($car->acceleration * 0.25) + ($car->handling * 0.25) + ($car->braking * 0.25);
            $driverScore = ($driver->pace * 0.30) + ($driver->cornering * 0.25) + ($driver->consistency * 0.25) + ($driver->racecraft * 0.20);
        }

        // Wet weather shifts focus to reliability, consistency, and handling
        if ($weather === 'wet') {
            $carScore = ($carScore * 0.70) + ($car->reliability * 0.30);
            $driverScore = ($driverScore * 0.65) + ($driver->consistency * 0.20) + ($driver->racecraft * 0.15);
        }

        // 55% Car weight, 45% Driver weight
        $composite = ($carScore * 0.55) + ($driverScore * 0.45);

        return (int) round(max(10, min(99, $composite)));
    }

    /**
     * Format seconds into minutes:seconds.milliseconds.
     */
    public function formatTime(float $seconds): string
    {
        $minutes = (int) floor($seconds / 60);
        $remainingSeconds = $seconds - ($minutes * 60);

        return sprintf('%d:%06.3f', $minutes, $remainingSeconds);
    }

    /**
     * Format a single lap time in seconds into M:SS.mmm.
     */
    public function formatLapTime(float $seconds): string
    {
        $minutes = (int) floor($seconds / 60);
        $remainingSeconds = $seconds - ($minutes * 60);

        return sprintf('%d:%06.3f', $minutes, $remainingSeconds);
    }

    /**
     * Calculate championship prize money and reputation earned based on finish position and fastest lap bonus.
     *
     * @return array{base_prize: int, fastest_lap_bonus: int, prize_money: int, reputation_earned: int}
     */
    public function calculatePrizeAndReputation(Race $race, int $position, bool $hasFastestLap = false): array
    {
        $prizePercentages = [
            1 => 0.40,
            2 => 0.25,
            3 => 0.15,
            4 => 0.08,
            5 => 0.05,
            6 => 0.03,
            7 => 0.02,
            8 => 0.01,
            9 => 0.005,
            10 => 0.005,
        ];

        $repDistribution = [
            1 => 50,
            2 => 30,
            3 => 20,
            4 => 12,
            5 => 8,
            6 => 5,
            7 => 4,
            8 => 3,
            9 => 2,
            10 => 1,
        ];

        $basePercentage = $prizePercentages[$position] ?? 0.005;
        $basePrize = (int) round($race->prize_pool * $basePercentage);
        $baseRep = $repDistribution[$position] ?? 1;

        $fastestLapBonus = 0;
        $fastestLapRep = 0;
        if ($hasFastestLap) {
            $fastestLapBonus = (int) round($race->prize_pool * 0.05);
            $fastestLapRep = 5;
        }

        $totalPrize = $basePrize + $fastestLapBonus;
        $totalRep = $baseRep + $fastestLapRep;

        return [
            'base_prize' => $basePrize,
            'fastest_lap_bonus' => $fastestLapBonus,
            'prize_money' => $totalPrize,
            'reputation_earned' => $totalRep,
        ];
    }
}
