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
     * Get drivers' championship standings table for the specified season.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDriversStandings(Team $team, ?int $season = null): array
    {
        $targetSeason = $season ?? max(1, (int) $team->current_season);
        $playerDriver = $team->primaryDriver();
        $playerDriverName = $playerDriver ? $playerDriver->name : 'Lead Driver';
        $playerCar = $team->activeCar();
        $playerCarName = $playerCar ? $playerCar->name : 'Race Chassis';

        // 1. Initialize AI drivers entries first
        $drivers = [];
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

        // Initialize / enforce Player driver entry with is_player = true
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

        // 2. Accumulate results from team's completed races in this season
        $raceResults = RaceResult::where('team_id', $team->id)
            ->where('season', $targetSeason)
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
                    $isPlayerEntry = ! empty($driverEntry['is_player'])
                        || ($dName === $playerDriverName)
                        || ($result->driver && $dName === $result->driver->name)
                        || (($driverEntry['team_name'] ?? '') === $team->name);

                    // If this was the player's driver in the race, map to the current player driver key
                    $targetKey = $isPlayerEntry ? $playerDriverName : $dName;

                    if (! isset($drivers[$targetKey])) {
                        $drivers[$targetKey] = [
                            'driver_name' => $targetKey,
                            'team_name' => $isPlayerEntry ? $team->name : ($driverEntry['team_name'] ?? 'Independent'),
                            'car_name' => $isPlayerEntry ? $playerCarName : ($driverEntry['car_name'] ?? 'Chassis'),
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
                    if ($pos <= 10 && ($fastestLapDriver === $dName || ($isPlayerEntry && $fastestLapDriver === $playerDriverName))) {
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
     * Get constructors' championship standings table for the specified season.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getConstructorsStandings(Team $team, ?int $season = null): array
    {
        $targetSeason = $season ?? max(1, (int) $team->current_season);
        $playerCar = $team->activeCar();
        $playerCarName = $playerCar ? $playerCar->name : 'Race Chassis';
        $playerDriver = $team->primaryDriver();
        $playerDriverName = $playerDriver ? $playerDriver->name : 'Lead Driver';

        // 1. Initialize AI constructors entries first
        $constructors = [];
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

        // Initialize / enforce Player team entry with is_player = true
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

        // 2. Accumulate results from team's completed races in this season
        $raceResults = RaceResult::where('team_id', $team->id)
            ->where('season', $targetSeason)
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
                    $isPlayerEntry = ! empty($driverEntry['is_player'])
                        || ($tName === $team->name)
                        || ($dName === $playerDriverName)
                        || ($result->driver && $dName === $result->driver->name);

                    // If this is the player's entry, always attribute to player team
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
                    if ($pos <= 10 && ($fastestLapDriver === $dName || ($isPlayerEntry && $fastestLapDriver === $playerDriverName))) {
                        $earnedPts += 1;
                    }

                    $constructors[$targetKey]['points'] += $earnedPts;
                    $constructors[$targetKey]['races_entered']++;
                    $constructors[$targetKey]['best_finish'] = min($constructors[$targetKey]['best_finish'], $pos);

                    if ($pos === 1) {
                        $constructors[$targetKey]['wins']++;
                    }
                    if ($pos <= 3) {
                        $constructors[$targetKey]['podiums']++;
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
