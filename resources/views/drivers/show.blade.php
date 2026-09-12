@extends('layouts.app')

@section('title', 'Driver Dossier - ' . $driver->name)

@section('content')
<div class="space-y-6">
    <!-- Header / Breadcrumb Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-cyan-500 via-blue-500 to-indigo-600"></div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1 text-xs font-mono text-zinc-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-cyan-400 transition-colors">PADDOCK</a>
                    <span>/</span>
                    <a href="{{ route('drivers.index') }}" class="hover:text-cyan-400 transition-colors uppercase">DRIVER LINEUP</a>
                    <span>/</span>
                    <span class="text-cyan-400 font-bold uppercase">{{ $driver->name }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-white uppercase font-mono tracking-tight">
                        {{ $driver->name }}
                    </h1>
                    <span class="text-xs font-mono font-black px-2.5 py-1 rounded bg-zinc-800 text-cyan-300 border border-zinc-700">
                        {{ $overallRating }} OVR
                    </span>
                    @if($driver->is_lead)
                        <span class="text-xs font-mono font-black uppercase tracking-wider bg-cyan-950 border border-cyan-500/50 text-cyan-400 px-2.5 py-1 rounded flex items-center gap-1.5 shadow-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                            LEAD RACE DRIVER
                        </span>
                    @else
                        <span class="text-xs font-mono font-bold uppercase tracking-wider bg-zinc-950 border border-zinc-800 text-zinc-500 px-2.5 py-1 rounded">
                            RESERVE SEAT
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('drivers.index') }}" class="px-4 py-2 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 text-xs font-mono font-bold uppercase transition flex items-center gap-1.5">
                    <span>&larr; Back to Lineup</span>
                </a>

                @if(!$driver->is_lead)
                    <form method="POST" action="{{ route('drivers.set-lead', $driver) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-mono font-bold tracking-wider uppercase transition shadow-md cursor-pointer">
                            Promote to Lead Driver
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Telemetry Dossier & Biometrics Matrix -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: 7 Attribute Meters -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Attribute Gauges -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg">
                <div class="flex items-center justify-between pb-4 mb-6 border-b border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded bg-cyan-500"></span>
                        <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Driver Skill & Telemetry Profile</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono text-zinc-400 uppercase">Composite OVR:</span>
                        <span class="text-base font-mono font-black text-cyan-400">{{ $overallRating }} / 100</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Pace -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-xs font-mono font-bold uppercase text-cyan-400">01 // RAW PACE & QUALIFYING SPEED</span>
                                <span class="text-[10px] font-mono text-zinc-500 block">Single-lap speed and sector timing efficiency</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $driver->pace }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-2.5 overflow-hidden border border-zinc-800">
                            <div class="bg-cyan-500 h-full rounded" style="width: {{ min(100, $driver->pace) }}%"></div>
                        </div>
                    </div>

                    <!-- Cornering -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-xs font-mono font-bold uppercase text-blue-400">02 // APEX CORNERING & BRAKING</span>
                                <span class="text-[10px] font-mono text-zinc-500 block">Apex velocity and trail-braking control</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $driver->cornering }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-2.5 overflow-hidden border border-zinc-800">
                            <div class="bg-blue-500 h-full rounded" style="width: {{ min(100, $driver->cornering) }}%"></div>
                        </div>
                    </div>

                    <!-- Consistency -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-xs font-mono font-bold uppercase text-emerald-400">03 // STINT CONSISTENCY</span>
                                <span class="text-[10px] font-mono text-zinc-500 block">Lap time stability and mistake minimization</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $driver->consistency }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-2.5 overflow-hidden border border-zinc-800">
                            <div class="bg-emerald-500 h-full rounded" style="width: {{ min(100, $driver->consistency) }}%"></div>
                        </div>
                    </div>

                    <!-- Overtaking -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-xs font-mono font-bold uppercase text-orange-400">04 // OVERTAKING AGGRESSION</span>
                                <span class="text-[10px] font-mono text-zinc-500 block">Late braking maneuvers and position gains</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $driver->overtaking }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-2.5 overflow-hidden border border-zinc-800">
                            <div class="bg-orange-500 h-full rounded" style="width: {{ min(100, $driver->overtaking) }}%"></div>
                        </div>
                    </div>

                    <!-- Defensive -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-xs font-mono font-bold uppercase text-amber-400">05 // DEFENSIVE POSITIONING</span>
                                <span class="text-[10px] font-mono text-zinc-500 block">Apex placement and blocking overtaking attempts</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $driver->defensive }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-2.5 overflow-hidden border border-zinc-800">
                            <div class="bg-amber-500 h-full rounded" style="width: {{ min(100, $driver->defensive) }}%"></div>
                        </div>
                    </div>

                    <!-- Racecraft -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-xs font-mono font-bold uppercase text-purple-400">06 // RACECRAFT & STRATEGY</span>
                                <span class="text-[10px] font-mono text-zinc-500 block">Tire management, weather adaptation and battle IQ</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $driver->racecraft }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-2.5 overflow-hidden border border-zinc-800">
                            <div class="bg-purple-500 h-full rounded" style="width: {{ min(100, $driver->racecraft) }}%"></div>
                        </div>
                    </div>

                    <!-- Experience -->
                    <div class="bg-zinc-950/70 border border-zinc-800/80 rounded p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-xs font-mono font-bold uppercase text-teal-400">07 // CAREER EXPERIENCE</span>
                                <span class="text-[10px] font-mono text-zinc-500 block">Track familiarity, composure under pressure</span>
                            </div>
                            <span class="text-sm font-mono font-black text-white">{{ $driver->experience }} <span class="text-xs text-zinc-500 font-normal">/ 100</span></span>
                        </div>
                        <div class="w-full bg-zinc-900 rounded h-2.5 overflow-hidden border border-zinc-800">
                            <div class="bg-teal-500 h-full rounded" style="width: {{ min(100, $driver->experience) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Career Track Record / Race Results -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded bg-indigo-500"></span>
                        <h2 class="text-sm font-mono font-bold uppercase tracking-wider text-white">Championship Race History</h2>
                    </div>
                    <span class="text-xs font-mono text-zinc-400">{{ $raceResults->count() }} Grand Prix Start(s)</span>
                </div>

                @if($raceResults->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs font-mono">
                            <thead class="text-[10px] text-zinc-500 uppercase border-b border-zinc-800">
                                <tr>
                                    <th class="py-2">Race</th>
                                    <th class="py-2">Finish</th>
                                    <th class="py-2">Prize</th>
                                    <th class="py-2">Reputation</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/60">
                                @foreach($raceResults as $result)
                                    <tr>
                                        <td class="py-2.5 font-bold text-white">{{ $result->race->name ?? 'Grand Prix' }}</td>
                                        <td class="py-2.5 font-bold text-cyan-400">P{{ $result->position }}</td>
                                        <td class="py-2.5 text-amber-400">+{{ number_format($result->prize_money) }} CR</td>
                                        <td class="py-2.5 text-cyan-300">+{{ $result->reputation_earned }} PTS</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="bg-zinc-950/60 border border-zinc-800/80 rounded p-6 text-center">
                        <p class="text-xs font-mono text-zinc-400">No official Grand Prix race starts recorded yet.</p>
                        <p class="text-[11px] font-mono text-zinc-500 mt-1">Race entries and championship simulation will unlock in Task 5 (Race Preparation & Simulation).</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right 1 Col: Contract Details & Quick Actions -->
        <div class="space-y-6">
            <!-- Contract Card -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg">
                <div class="text-xs font-mono font-bold uppercase tracking-wider text-zinc-400 mb-4 pb-2 border-b border-zinc-800">
                    Contract & Employment Details
                </div>

                <div class="space-y-3 text-xs font-mono">
                    <div class="flex justify-between py-1.5 border-b border-zinc-800/60">
                        <span class="text-zinc-500">Constructor</span>
                        <span class="text-white font-bold">{{ $team->name }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-zinc-800/60">
                        <span class="text-zinc-500">Contract Salary</span>
                        <span class="text-amber-400 font-bold">{{ number_format($driver->salary) }} CR <span class="text-[10px] text-zinc-500 font-normal">/ race</span></span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-zinc-800/60">
                        <span class="text-zinc-500">Roster Role</span>
                        @if($driver->is_lead)
                            <span class="text-cyan-400 font-bold uppercase">Lead Driver (#1)</span>
                        @else
                            <span class="text-zinc-400 uppercase">Reserve Driver</span>
                        @endif
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-zinc-800/60">
                        <span class="text-zinc-500">Driver License UID</span>
                        <span class="text-zinc-400">#FIA-{{ str_pad($driver->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-zinc-500">Signing Date</span>
                        <span class="text-zinc-400">{{ $driver->created_at->format('M d, Y') }}</span>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="mt-6 pt-4 border-t border-zinc-800">
                    @if(!$driver->is_lead)
                        <form method="POST" action="{{ route('drivers.set-lead', $driver) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full text-center py-2.5 px-4 rounded bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-mono font-bold tracking-wider uppercase transition shadow cursor-pointer">
                                Promote to Lead Driver
                            </button>
                        </form>
                    @else
                        <div class="w-full py-2 px-3 rounded bg-cyan-950/80 border border-cyan-500/40 text-cyan-300 text-center text-xs font-mono font-bold flex items-center justify-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                            <span>Currently Assigned Lead Driver</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Navigation Box -->
            <div class="bg-zinc-900 border border-zinc-800 rounded p-5 shadow-lg">
                <div class="text-xs font-mono font-bold uppercase tracking-wider text-zinc-400 mb-3">
                    Pit-Wall Quick Nav
                </div>
                <div class="space-y-2 text-xs font-mono">
                    <a href="{{ route('drivers.index') }}" class="block p-2.5 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white transition-colors">
                        &rarr; Return to Driver Lineup
                    </a>
                    <a href="{{ route('drivers.market') }}" class="block p-2.5 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-cyan-400 hover:text-cyan-300 transition-colors">
                        &rarr; Scout Free Agent Market
                    </a>
                    <a href="{{ route('dashboard') }}" class="block p-2.5 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white transition-colors">
                        &rarr; Paddock Command Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
