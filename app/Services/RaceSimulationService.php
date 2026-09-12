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
    protected array $aiGridPool = [
        ['team' => 'Scuderia Veloce', 'driver' => 'Marco Rossi', 'car' => 'Veloce C26', 'base_ovr' => 74],
        ['team' => 'Silverstone Dynamics', 'driver' => 'Liam Vance', 'car' => 'SD-08 Arrow', 'base_ovr' => 72],
        ['team' => 'AeroTech Motorsport', 'driver' => 'Elena Rostova', 'car' => 'AT-Aero Pro', 'base_ovr' => 71],
        ['team' => 'Nordic Speedworks', 'driver' => 'Lukas Lindqvist', 'car' => 'Valkyrie R', 'base_ovr' => 69],
        ['team' => 'Kronos Racing GP', 'driver' => 'Marcus Chen', 'car' => 'Kronos K9', 'base_ovr' => 68],
        ['team' => 'Apex Performance', 'driver' => 'Sofia Bianchi', 'car' => 'Apex Apex-1', 'base_ovr' => 66],
        ['team' => 'Hyperion Grand Prix', 'driver' => 'Tariq Mansoor', 'car' => 'Hyperion H7', 'base_ovr' => 65],
        ['team' => 'Blackline Racing', 'driver' => 'Lucas Silva', 'car' => 'Shadow RS', 'base_ovr' => 63],
        ['team' => 'Zenith Motorsport', 'driver' => 'Kenji Sato', 'car' => 'Zenith Type-R', 'base_ovr' => 75],
    ];

    /**
     * Simulate a complete Grand Prix race.
     *
     * @return array<string, mixed>
     */
    public function simulate(Race $race, Team $playerTeam, Car $playerCar, Driver $playerDriver): array
    {
        $totalLaps = max(5, $race->laps);
        $trackType = $race->track_type;
        $weather = $race->weather;

        // Base lap time in seconds (e.g. 78.0s)
        $baseLapTime = 75.0 + ($totalLaps <= 10 ? 3.0 : 0.0);
        if ($weather === 'wet') {
            $baseLapTime += 5.5; // Wet track is slower
        }

        // 1. Calculate Player Competitor Performance Index (0-100)
        $playerPerf = $this->calculatePerformanceIndex($playerCar, $playerDriver, $trackType, $weather);

        // 2. Build Grid: 1 Player + 9 AI Competitors
        $competitors = [];

        // Player entry
        $competitors[] = [
            'id' => 'player_'.$playerTeam->id,
            'is_player' => true,
            'team_name' => $playerTeam->name,
            'driver_name' => $playerDriver->name,
            'car_name' => $playerCar->name,
            'perf_index' => $playerPerf,
            'consistency' => $playerDriver->consistency,
            'reliability' => $playerCar->reliability,
            'racecraft' => $playerDriver->racecraft,
            'total_time' => 0.0,
            'lap_times' => [],
            'best_lap' => 999.0,
            'best_lap_num' => 1,
            'positions_by_lap' => [],
        ];

        // AI entries
        foreach ($this->aiGridPool as $index => $ai) {
            // Apply slight random noise +/- 3 to AI base OVR
            $aiPerf = max(40, min(95, $ai['base_ovr'] + random_int(-3, 3)));
            $competitors[] = [
                'id' => 'ai_'.($index + 1),
                'is_player' => false,
                'team_name' => $ai['team'],
                'driver_name' => $ai['driver'],
                'car_name' => $ai['car'],
                'perf_index' => $aiPerf,
                'consistency' => max(40, $aiPerf - random_int(0, 8)),
                'reliability' => max(50, $aiPerf + random_int(-5, 5)),
                'racecraft' => max(40, $aiPerf + random_int(-4, 4)),
                'total_time' => 0.0,
                'lap_times' => [],
                'best_lap' => 999.0,
                'best_lap_num' => 1,
                'positions_by_lap' => [],
            ];
        }

        // 3. Lap-by-Lap Simulation Loop
        $lapEvents = [];
        $lapEvents[] = [
            'lap' => 1,
            'type' => 'start',
            'message' => "🟢 LIGHTS OUT! The grid roars into Turn 1 under {$weather} conditions at {$race->name}.",
        ];

        for ($lap = 1; $lap <= $totalLaps; $lap++) {
            foreach ($competitors as &$c) {
                // Time variance based on driver consistency (lower consistency = higher variance)
                $varianceWindow = (100 - $c['consistency']) / 20.0; // e.g. 0.5s - 2.5s
                $variance = (random_int(-100, 100) / 100.0) * $varianceWindow;

                // Speed delta from performance index (higher perf = faster lap)
                $paceBonus = ($c['perf_index'] - 50) * 0.08; // +/- 2.5s

                // Reliability incident check
                $incidentDelta = 0.0;
                if (random_int(1, 100) > $c['reliability']) {
                    $incidentDelta = random_int(8, 25) / 10.0; // 0.8s - 2.5s stumble
                    if ($c['is_player'] && $incidentDelta > 1.5) {
                        $lapEvents[] = [
                            'lap' => $lap,
                            'type' => 'telemetry_alert',
                            'message' => "⚠️ Lap {$lap}: {$c['driver_name']} reports sudden tire lock-up into the chicane, losing ".number_format($incidentDelta, 2).'s.',
                        ];
                    }
                }

                $lapTime = max(60.0, $baseLapTime - $paceBonus + $variance + $incidentDelta);

                $c['total_time'] += $lapTime;
                $c['lap_times'][] = $lapTime;

                if ($lapTime < $c['best_lap']) {
                    $c['best_lap'] = $lapTime;
                    $c['best_lap_num'] = $lap;
                }
            }
            unset($c);

            // Sort grid by total time at current lap to determine current lap standing
            usort($competitors, fn ($a, $b) => $a['total_time'] <=> $b['total_time']);

            // Record lap positions & generate overtake events
            foreach ($competitors as $posIndex => &$c) {
                $currentPos = $posIndex + 1;
                $prevPos = $c['positions_by_lap'][$lap - 1] ?? $currentPos;
                $c['positions_by_lap'][$lap] = $currentPos;

                // If player gained positions
                if ($c['is_player'] && $currentPos < $prevPos && $lap > 1) {
                    $gained = $prevPos - $currentPos;
                    $rivalAhead = $competitors[$posIndex + 1]['driver_name'] ?? 'competitor';
                    $lapEvents[] = [
                        'lap' => $lap,
                        'type' => 'overtake',
                        'message' => "⚡ Lap {$lap}: {$c['driver_name']} executes a brilliant maneuver to pass {$rivalAhead} for P{$currentPos}!",
                    ];
                } elseif ($c['is_player'] && $currentPos > $prevPos && $lap > 1) {
                    $rivalBehind = $competitors[$posIndex - 1]['driver_name'] ?? 'rival';
                    $lapEvents[] = [
                        'lap' => $lap,
                        'type' => 'defense_loss',
                        'message' => "📉 Lap {$lap}: {$c['driver_name']} comes under pressure and yields P".($currentPos - 1)." to {$rivalBehind}.",
                    ];
                }
            }
            unset($c);

            // Mid-race pit / weather radio event
            if ($lap === (int) floor($totalLaps / 2)) {
                $lapEvents[] = [
                    'lap' => $lap,
                    'type' => 'pit_radio',
                    'message' => "📻 Lap {$lap} Pit-Wall Radio: Halfway mark reached. Fuel flow nominal, tire degradation tracking within forecast window.",
                ];
            }
        }

        // Final Sort by Total Race Time
        usort($competitors, fn ($a, $b) => $a['total_time'] <=> $b['total_time']);

        $leaderTime = $competitors[0]['total_time'];
        $fastestOverall = null;
        $bestLapOverallTime = 999.0;

        $standings = [];
        $playerResult = null;

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
                'driver_name' => $c['driver_name'],
                'team_name' => $c['team_name'],
                'car_name' => $c['car_name'],
                'total_time' => $formattedTotalTime,
                'total_seconds' => $c['total_time'],
                'gap' => $gap,
                'fastest_lap' => $formattedBestLap,
                'fastest_lap_seconds' => $c['best_lap'],
            ];

            $standings[] = $standingEntry;

            if ($c['is_player']) {
                $playerResult = $standingEntry;
            }
        }

        $lapEvents[] = [
            'lap' => $totalLaps,
            'type' => 'finish',
            'message' => "🏁 CHECKERED FLAG! Grand Prix complete. {$standings[0]['driver_name']} takes victory. {$playerResult['driver_name']} finishes P{$playerResult['position']}.",
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
            'standings' => $standings,
            'player_result' => $playerResult,
            'fastest_lap_overall' => $fastestOverall,
            'lap_events' => $lapEvents,
        ];
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
