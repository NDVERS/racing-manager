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
     * Get drivers' championship standings table for the team's season.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDriversStandings(Team $team): array
    {
        $playerDriver = $team->primaryDriver();
        $playerDriverName = $playerDriver ? $playerDriver->name : 'Lead Driver';
        $playerCar = $team->activeCar();
        $playerCarName = $playerCar ? $playerCar->name : 'Race Chassis';

        // 1. Initialize drivers table
        $drivers = [];

        // Player driver entry
        $drivers[$playerDriverName] = [
            'driver_name' => $playerDriverName,
            'team_name' => $team->name,
            'car_name' => $playerCarName,
            'is_player' => true,
            'points' => 0,
            'wins' => 0,
            'podiums' => 0,
            'fastest_laps' => 0,
            'races_entered' => 0,
            'best_finish' => 99,
            'recent_finishes' => [],
        ];

        // AI drivers entries
        foreach ($this->aiGridPool as $ai) {
            $drivers[$ai['driver']] = [
                'driver_name' => $ai['driver'],
                'team_name' => $ai['team'],
                'car_name' => $ai['car'],
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

        // 2. Accumulate results from team's completed races
        $raceResults = RaceResult::where('team_id', $team->id)
            ->with(['race', 'car', 'driver'])
            ->orderBy('created_at')
            ->get();

        foreach ($raceResults as $result) {
            $sim = $result->simulation_log;
            $fastestLapDriver = $sim['fastest_lap_overall']['driver'] ?? null;

            if (isset($sim['standings']) && is_array($sim['standings'])) {
                foreach ($sim['standings'] as $driverEntry) {
                    $dName = $driverEntry['driver_name'];
                    $pos = (int) $driverEntry['position'];

                    if (! isset($drivers[$dName])) {
                        $drivers[$dName] = [
                            'driver_name' => $dName,
                            'team_name' => $driverEntry['team_name'] ?? 'Independent',
                            'car_name' => $driverEntry['car_name'] ?? 'Chassis',
                            'is_player' => (bool) ($driverEntry['is_player'] ?? false),
                            'points' => 0,
                            'wins' => 0,
                            'podiums' => 0,
                            'fastest_laps' => 0,
                            'races_entered' => 0,
                            'best_finish' => 99,
                            'recent_finishes' => [],
                        ];
                    }

                    $earnedPts = $this->pointsMap[$pos] ?? 0;
                    if ($pos <= 10 && $fastestLapDriver === $dName) {
                        $earnedPts += 1;
                        $drivers[$dName]['fastest_laps']++;
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
            } else {
                // Fallback if simulation_log is minimal
                $pos = $result->position;
                $earnedPts = $this->pointsMap[$pos] ?? 0;
                $drivers[$playerDriverName]['points'] += $earnedPts;
                $drivers[$playerDriverName]['races_entered']++;
                $drivers[$playerDriverName]['best_finish'] = min($drivers[$playerDriverName]['best_finish'], $pos);
                $drivers[$playerDriverName]['recent_finishes'][] = $pos;
                if ($pos === 1) {
                    $drivers[$playerDriverName]['wins']++;
                }
                if ($pos <= 3) {
                    $drivers[$playerDriverName]['podiums']++;
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
     * Get constructors' championship standings table for the team's season.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getConstructorsStandings(Team $team): array
    {
        $playerCar = $team->activeCar();
        $playerCarName = $playerCar ? $playerCar->name : 'Race Chassis';
        $playerDriver = $team->primaryDriver();
        $playerDriverName = $playerDriver ? $playerDriver->name : 'Lead Driver';

        // 1. Initialize constructors table
        $constructors = [];

        // Player team entry
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

        // AI constructors entries
        foreach ($this->aiGridPool as $ai) {
            $constructors[$ai['team']] = [
                'team_name' => $ai['team'],
                'car_name' => $ai['car'],
                'lead_driver' => $ai['driver'],
                'is_player' => false,
                'points' => 0,
                'wins' => 0,
                'podiums' => 0,
                'races_entered' => 0,
                'best_finish' => 99,
            ];
        }

        // 2. Accumulate results from team's completed races
        $raceResults = RaceResult::where('team_id', $team->id)
            ->with(['race', 'car', 'driver'])
            ->orderBy('created_at')
            ->get();

        foreach ($raceResults as $result) {
            $sim = $result->simulation_log;
            $fastestLapDriver = $sim['fastest_lap_overall']['driver'] ?? null;

            if (isset($sim['standings']) && is_array($sim['standings'])) {
                foreach ($sim['standings'] as $driverEntry) {
                    $tName = $driverEntry['team_name'] ?? 'Independent';
                    $dName = $driverEntry['driver_name'];
                    $pos = (int) $driverEntry['position'];

                    if (! isset($constructors[$tName])) {
                        $constructors[$tName] = [
                            'team_name' => $tName,
                            'car_name' => $driverEntry['car_name'] ?? 'Chassis',
                            'lead_driver' => $dName,
                            'is_player' => (bool) ($driverEntry['is_player'] ?? false),
                            'points' => 0,
                            'wins' => 0,
                            'podiums' => 0,
                            'races_entered' => 0,
                            'best_finish' => 99,
                        ];
                    }

                    $earnedPts = $this->pointsMap[$pos] ?? 0;
                    if ($pos <= 10 && $fastestLapDriver === $dName) {
                        $earnedPts += 1;
                    }

                    $constructors[$tName]['points'] += $earnedPts;
                    $constructors[$tName]['races_entered']++;
                    $constructors[$tName]['best_finish'] = min($constructors[$tName]['best_finish'], $pos);

                    if ($pos === 1) {
                        $constructors[$tName]['wins']++;
                    }
                    if ($pos <= 3) {
                        $constructors[$tName]['podiums']++;
                    }
                }
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
    public function getSeasonOverview(Team $team): array
    {
        $totalRounds = Race::count();
        $completedRounds = RaceResult::where('team_id', $team->id)->count();

        $driversStandings = $this->getDriversStandings($team);
        $constructorsStandings = $this->getConstructorsStandings($team);

        // Find player constructor & driver ranks
        $playerConstructorRank = 10;
        $playerConstructorPoints = 0;
        $constructorLeaderGap = '0 PTS';

        foreach ($constructorsStandings as $c) {
            if ($c['is_player']) {
                $playerConstructorRank = $c['rank'];
                $playerConstructorPoints = $c['points'];
                $constructorLeaderGap = $c['gap'];
                break;
            }
        }

        $playerDriverRank = 10;
        $playerDriverPoints = 0;
        $driverLeaderGap = '0 PTS';

        foreach ($driversStandings as $d) {
            if ($d['is_player']) {
                $playerDriverRank = $d['rank'];
                $playerDriverPoints = $d['points'];
                $driverLeaderGap = $d['gap'];
                break;
            }
        }

        return [
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
