<?php

namespace App\Services;

use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Team;

class ChampionshipService
{
    /**
     * Official FIA championship points allocation for top 10 positions.
     *
     * @var array<int, int>
     */
    protected array $pointsMap = [
        1 => 25,
        2 => 18,
        3 => 15,
        4 => 12,
        5 => 10,
        6 => 8,
        7 => 6,
        8 => 4,
        9 => 2,
        10 => 1,
    ];

    /**
     * AI Competitor grid pool definitions matching RaceSimulationService.
     *
     * @var array<int, array{team: string, driver: string, car: string, base_ovr: int}>
     */
    /**
     * AI Competitor grid pool definitions matching RaceSimulationService.
     *
     * @var array<int, array{team: string, driver1: string, car1: string, ovr1: int, driver2: string, car2: string, ovr2: int}>
     */
    protected array $aiGridPool = [
        ['team' => 'Scuderia Veloce', 'driver1' => 'Marco Rossi', 'car1' => 'Veloce C26', 'ovr1' => 74, 'driver2' => 'Matteo Ricci', 'car2' => 'Veloce C26', 'ovr2' => 73],
        ['team' => 'Silverstone Dynamics', 'driver1' => 'Liam Vance', 'car1' => 'SD-08 Arrow', 'ovr1' => 72, 'driver2' => 'Oliver Sterling', 'car2' => 'SD-08 Arrow', 'ovr2' => 70],
        ['team' => 'AeroTech Motorsport', 'driver1' => 'Elena Rostova', 'car1' => 'AT-Aero Pro', 'ovr1' => 71, 'driver2' => 'Viktor Weber', 'car2' => 'AT-Aero Pro', 'ovr2' => 69],
        ['team' => 'Nordic Speedworks', 'driver1' => 'Lukas Lindqvist', 'car1' => 'Valkyrie R', 'ovr1' => 69, 'driver2' => 'Astrid Holm', 'car2' => 'Valkyrie R', 'ovr2' => 67],
        ['team' => 'Kronos Racing GP', 'driver1' => 'Marcus Chen', 'car1' => 'Kronos K9', 'ovr1' => 68, 'driver2' => 'Daniel Cho', 'car2' => 'Kronos K9', 'ovr2' => 66],
        ['team' => 'Apex Performance', 'driver1' => 'Sofia Bianchi', 'car1' => 'Apex Apex-1', 'ovr1' => 66, 'driver2' => 'Carlos Mendez', 'car2' => 'Apex Apex-1', 'ovr2' => 64],
        ['team' => 'Hyperion Grand Prix', 'driver1' => 'Tariq Mansoor', 'car1' => 'Hyperion H7', 'ovr1' => 65, 'driver2' => 'Andre Dubois', 'car2' => 'Hyperion H7', 'ovr2' => 63],
        ['team' => 'Blackline Racing', 'driver1' => 'Lucas Silva', 'car1' => 'Shadow RS', 'ovr1' => 63, 'driver2' => 'Mason Vance', 'car2' => 'Shadow RS', 'ovr2' => 61],
        ['team' => 'Zenith Motorsport', 'driver1' => 'Kenji Sato', 'car1' => 'Zenith Type-R', 'ovr1' => 75, 'driver2' => 'Hiroshi Tanaka', 'car2' => 'Zenith Type-R', 'ovr2' => 73],
    ];

    /**
     * Get drivers' championship standings table for the specified season.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDriversStandings(Team $team, ?int $season = null): array
    {
        $targetSeason = $season ?? max(1, (int) $team->current_season);
        $playerDriver1 = $team->driver1();
        $playerDriver1Name = $playerDriver1 ? $playerDriver1->name : 'Lead Driver';
        $playerCar1 = $team->car1();
        $playerCar1Name = $playerCar1 ? $playerCar1->name : 'Race Chassis';

        $playerDriver2 = $team->driver2();
        $playerDriver2Name = $playerDriver2 ? $playerDriver2->name : null;
        $playerCar2 = $team->car2();
        $playerCar2Name = $playerCar2 ? $playerCar2->name : null;

        // 1. Initialize AI drivers entries first (both driver 1 and driver 2)
        $drivers = [];
        foreach ($this->aiGridPool as $ai) {
            $drivers[$ai['driver1']] = [
                'driver_name' => $ai['driver1'],
                'team_name' => $ai['team'],
                'car_name' => $ai['car1'],
                'is_player' => false,
                'points' => 0,
                'wins' => 0,
                'podiums' => 0,
                'fastest_laps' => 0,
                'races_entered' => 0,
                'best_finish' => 99,
                'recent_finishes' => [],
            ];
            $drivers[$ai['driver2']] = [
                'driver_name' => $ai['driver2'],
                'team_name' => $ai['team'],
                'car_name' => $ai['car2'],
                'is_player' => false,
                'points' => 0,
                'wins' => 0,
                'podiums' => 0,
                'fastest_laps' => 0,
                'races_entered' => 0,
                'best_finish' => 99,
                'recent_finishes' => [],
            ];
        }

        // Initialize Player Driver 1
        $drivers[$playerDriver1Name] = [
            'driver_name' => $playerDriver1Name,
            'team_name' => $team->name,
            'car_name' => $playerCar1Name,
            'is_player' => true,
            'points' => 0,
            'wins' => 0,
            'podiums' => 0,
            'fastest_laps' => 0,
            'races_entered' => 0,
            'best_finish' => 99,
            'recent_finishes' => [],
        ];

        // Initialize Player Driver 2 if present
        if ($playerDriver2Name) {
            $drivers[$playerDriver2Name] = [
                'driver_name' => $playerDriver2Name,
                'team_name' => $team->name,
                'car_name' => $playerCar2Name ?? $playerCar1Name,
                'is_player' => true,
                'points' => 0,
                'wins' => 0,
                'podiums' => 0,
                'fastest_laps' => 0,
                'races_entered' => 0,
                'best_finish' => 99,
                'recent_finishes' => [],
            ];
        }

        // 2. Accumulate results from team's completed races in this season (unique per race to prevent dual-count)
        $raceResults = RaceResult::where('team_id', $team->id)
            ->where('season', $targetSeason)
            ->with(['race', 'car', 'driver'])
            ->orderBy('created_at')
            ->get();

        $uniqueRaces = $raceResults->unique('race_id');

        foreach ($uniqueRaces as $result) {
            $sim = $result->simulation_log;
            $fastestLapDriver = $sim['fastest_lap_overall']['driver'] ?? null;

            if (isset($sim['standings']) && is_array($sim['standings'])) {
                foreach ($sim['standings'] as $driverEntry) {
                    $dName = $driverEntry['driver_name'];
                    $pos = (int) $driverEntry['position'];
                    $isPlayerEntry = ! empty($driverEntry['is_player']) || (($driverEntry['team_name'] ?? '') === $team->name);

                    $targetKey = $dName;

                    if (! isset($drivers[$targetKey])) {
                        $drivers[$targetKey] = [
                            'driver_name' => $targetKey,
                            'team_name' => $isPlayerEntry ? $team->name : ($driverEntry['team_name'] ?? 'Independent'),
                            'car_name' => $driverEntry['car_name'] ?? 'Chassis',
                            'is_player' => $isPlayerEntry,
                            'points' => 0,
                            'wins' => 0,
                            'podiums' => 0,
                            'fastest_laps' => 0,
                            'races_entered' => 0,
                            'best_finish' => 99,
                            'recent_finishes' => [],
                        ];
                    }

                    if ($isPlayerEntry) {
                        $drivers[$targetKey]['is_player'] = true;
                        $drivers[$targetKey]['team_name'] = $team->name;
                    }

                    $earnedPts = $this->pointsMap[$pos] ?? 0;
                    if ($pos <= 10 && ($fastestLapDriver === $dName)) {
                        $earnedPts += 1;
                        $drivers[$targetKey]['fastest_laps']++;
                    }

                    $drivers[$targetKey]['points'] += $earnedPts;
                    $drivers[$targetKey]['races_entered']++;
                    $drivers[$targetKey]['best_finish'] = min($drivers[$targetKey]['best_finish'], $pos);
                    $drivers[$targetKey]['recent_finishes'][] = $pos;

                    if ($pos === 1) {
                        $drivers[$targetKey]['wins']++;
                    }
                    if ($pos <= 3) {
                        $drivers[$targetKey]['podiums']++;
                    }
                }
            } else {
                // Fallback for individual race result
                $dName = $result->driver ? $result->driver->name : $playerDriver1Name;
                $pos = $result->position;
                $earnedPts = $this->pointsMap[$pos] ?? 0;
                if (! isset($drivers[$dName])) {
                    $drivers[$dName] = [
                        'driver_name' => $dName,
                        'team_name' => $team->name,
                        'car_name' => $result->car ? $result->car->name : 'Race Car',
                        'is_player' => true,
                        'points' => 0,
                        'wins' => 0,
                        'podiums' => 0,
                        'fastest_laps' => 0,
                        'races_entered' => 0,
                        'best_finish' => 99,
                        'recent_finishes' => [],
                    ];
                }
                $drivers[$dName]['points'] += $earnedPts;
                $drivers[$dName]['races_entered']++;
                $drivers[$dName]['best_finish'] = min($drivers[$dName]['best_finish'], $pos);
                $drivers[$dName]['recent_finishes'][] = $pos;
                if ($pos === 1) {
                    $drivers[$dName]['wins']++;
                }
                if ($pos <= 3) {
                    $drivers[$dName]['podiums']++;
                }
            }
        }

        // 3. Sort drivers by Points DESC, Wins DESC, Podiums DESC, Best Finish ASC
        usort($drivers, function ($a, $b) {
            if ($a['points'] !== $b['points']) {
                return $b['points'] <=> $a['points'];
            }
            if ($a['wins'] !== $b['wins']) {
                return $b['wins'] <=> $a['wins'];
            }
            if ($a['podiums'] !== $b['podiums']) {
                return $b['podiums'] <=> $a['podiums'];
            }

            return $a['best_finish'] <=> $b['best_finish'];
        });

        // 4. Calculate gaps and rank positions
        $leaderPoints = $drivers[0]['points'] ?? 0;
        foreach ($drivers as $index => &$driver) {
            $driver['rank'] = $index + 1;
            $driver['gap'] = ($index === 0) ? 'LEADER' : '-'.($leaderPoints - $driver['points']).' PTS';
            if ($driver['best_finish'] === 99) {
                $driver['best_finish'] = '-';
            } else {
                $driver['best_finish'] = 'P'.$driver['best_finish'];
            }
        }
        unset($driver);

        return array_values($drivers);
    }

    /**
     * Get constructors' championship standings table for the specified season.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getConstructorsStandings(Team $team, ?int $season = null): array
    {
        $targetSeason = $season ?? max(1, (int) $team->current_season);
        $playerCar = $team->car1();
        $playerCarName = $playerCar ? $playerCar->name : 'Race Chassis';
        $playerDriver = $team->driver1();
        $playerDriverName = $playerDriver ? $playerDriver->name : 'Lead Driver';

        // 1. Initialize AI constructors entries first
        $constructors = [];
        foreach ($this->aiGridPool as $ai) {
            $constructors[$ai['team']] = [
                'team_name' => $ai['team'],
                'car_name' => $ai['car1'],
                'lead_driver' => $ai['driver1'],
                'is_player' => false,
                'points' => 0,
                'wins' => 0,
                'podiums' => 0,
                'races_entered' => 0,
                'best_finish' => 99,
            ];
        }

        // Initialize Player team entry
        $constructors[$team->name] = [
            'team_name' => $team->name,
            'car_name' => $playerCarName,
            'lead_driver' => $playerDriverName,
            'is_player' => true,
            'points' => 0,
            'wins' => 0,
            'podiums' => 0,
            'races_entered' => 0,
            'best_finish' => 99,
        ];

        // 2. Accumulate results from team's completed races in this season (unique per race to prevent dual-count)
        $raceResults = RaceResult::where('team_id', $team->id)
            ->where('season', $targetSeason)
            ->with(['race', 'car', 'driver'])
            ->orderBy('created_at')
            ->get();

        $uniqueRaces = $raceResults->unique('race_id');

        foreach ($uniqueRaces as $result) {
            $sim = $result->simulation_log;
            $fastestLapDriver = $sim['fastest_lap_overall']['driver'] ?? null;

            if (isset($sim['standings']) && is_array($sim['standings'])) {
                foreach ($sim['standings'] as $driverEntry) {
                    $tName = $driverEntry['team_name'] ?? 'Independent';
                    $dName = $driverEntry['driver_name'];
                    $pos = (int) $driverEntry['position'];
                    $isPlayerEntry = ! empty($driverEntry['is_player'])
                        || ($tName === $team->name);

                    $targetKey = $isPlayerEntry ? $team->name : $tName;

                    if (! isset($constructors[$targetKey])) {
                        $constructors[$targetKey] = [
                            'team_name' => $targetKey,
                            'car_name' => $driverEntry['car_name'] ?? 'Chassis',
                            'lead_driver' => $dName,
                            'is_player' => $isPlayerEntry,
                            'points' => 0,
                            'wins' => 0,
                            'podiums' => 0,
                            'races_entered' => 0,
                            'best_finish' => 99,
                        ];
                    }

                    if ($isPlayerEntry) {
                        $constructors[$targetKey]['is_player'] = true;
                    }

                    $earnedPts = $this->pointsMap[$pos] ?? 0;
                    if ($pos <= 10 && ($fastestLapDriver === $dName)) {
                        $earnedPts += 1;
                    }

                    $constructors[$targetKey]['points'] += $earnedPts;
                    $constructors[$targetKey]['best_finish'] = min($constructors[$targetKey]['best_finish'], $pos);

                    if ($pos === 1) {
                        $constructors[$targetKey]['wins']++;
                    }
                    if ($pos <= 3) {
                        $constructors[$targetKey]['podiums']++;
                    }
                }

                // Increment races_entered once per constructor per Grand Prix
                foreach ($constructors as $key => &$cRef) {
                    $cRef['races_entered']++;
                }
                unset($cRef);
            } else {
                $pos = $result->position;
                $earnedPts = $this->pointsMap[$pos] ?? 0;
                $constructors[$team->name]['points'] += $earnedPts;
                $constructors[$team->name]['races_entered']++;
                $constructors[$team->name]['best_finish'] = min($constructors[$team->name]['best_finish'], $pos);
                if ($pos === 1) {
                    $constructors[$team->name]['wins']++;
                }
                if ($pos <= 3) {
                    $constructors[$team->name]['podiums']++;
                }
            }
        }

        // 3. Sort constructors by Points DESC, Wins DESC, Podiums DESC, Best Finish ASC
        usort($constructors, function ($a, $b) {
            if ($a['points'] !== $b['points']) {
                return $b['points'] <=> $a['points'];
            }
            if ($a['wins'] !== $b['wins']) {
                return $b['wins'] <=> $a['wins'];
            }
            if ($a['podiums'] !== $b['podiums']) {
                return $b['podiums'] <=> $a['podiums'];
            }

            return $a['best_finish'] <=> $b['best_finish'];
        });

        // 4. Calculate gaps and rank positions
        $leaderPoints = $constructors[0]['points'] ?? 0;
        foreach ($constructors as $index => &$constructor) {
            $constructor['rank'] = $index + 1;
            $constructor['gap'] = ($index === 0) ? 'LEADER' : '-'.($leaderPoints - $constructor['points']).' PTS';
            if ($constructor['best_finish'] === 99) {
                $constructor['best_finish'] = '-';
            } else {
                $constructor['best_finish'] = 'P'.$constructor['best_finish'];
            }
        }
        unset($constructor);

        return array_values($constructors);
    }

    /**
     * Get a comprehensive season overview and player championship status metrics.
     *
     * @return array<string, mixed>
     */
    public function getSeasonOverview(Team $team, ?int $season = null): array
    {
        $targetSeason = $season ?? max(1, (int) $team->current_season);
        $totalRounds = Race::count();
        $completedRounds = RaceResult::where('team_id', $team->id)
            ->where('season', $targetSeason)
            ->distinct('race_id')
            ->count('race_id');

        $driversStandings = $this->getDriversStandings($team, $targetSeason);
        $constructorsStandings = $this->getConstructorsStandings($team, $targetSeason);
        $playerDriver = $team->primaryDriver();
        $playerDriverName = $playerDriver ? $playerDriver->name : 'Lead Driver';

        // Find player constructor rank
        $playerConstructorRank = count($constructorsStandings);
        $playerConstructorPoints = 0;
        $constructorLeaderGap = '0 PTS';

        foreach ($constructorsStandings as $c) {
            if ($c['is_player'] || $c['team_name'] === $team->name) {
                $playerConstructorRank = $c['rank'];
                $playerConstructorPoints = $c['points'];
                $constructorLeaderGap = $c['gap'];
                break;
            }
        }

        // Find player driver rank
        $playerDriverRank = count($driversStandings);
        $playerDriverPoints = 0;
        $driverLeaderGap = '0 PTS';

        foreach ($driversStandings as $d) {
            if ($d['is_player'] || $d['driver_name'] === $playerDriverName || $d['team_name'] === $team->name) {
                $playerDriverRank = $d['rank'];
                $playerDriverPoints = $d['points'];
                $driverLeaderGap = $d['gap'];
                break;
            }
        }

        return [
            'season_number' => $targetSeason,
            'is_current_season' => ($targetSeason === (int) $team->current_season),
            'is_completed' => ($totalRounds > 0 && $completedRounds >= $totalRounds),
            'total_rounds' => $totalRounds,
            'completed_rounds' => $completedRounds,
            'progress_percent' => $totalRounds > 0 ? (int) round(($completedRounds / $totalRounds) * 100) : 0,
            'player_constructor_rank' => $playerConstructorRank,
            'player_constructor_points' => $playerConstructorPoints,
            'constructor_leader_gap' => $constructorLeaderGap,
            'player_driver_rank' => $playerDriverRank,
            'player_driver_points' => $playerDriverPoints,
            'driver_leader_gap' => $driverLeaderGap,
            'leader_constructor_name' => $constructorsStandings[0]['team_name'] ?? 'N/A',
            'leader_constructor_points' => $constructorsStandings[0]['points'] ?? 0,
            'leader_driver_name' => $driversStandings[0]['driver_name'] ?? 'N/A',
            'leader_driver_points' => $driversStandings[0]['points'] ?? 0,
        ];
    }
}
