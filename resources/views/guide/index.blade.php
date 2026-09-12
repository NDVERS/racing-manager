@extends('layouts.app')

@section('title', 'Team Principal Handbook & Mechanics Guide')

@section('content')
<div class="space-y-6">
    <!-- Header / Breadcrumb Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 via-amber-400 to-red-600"></div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1 text-xs font-telemetry text-zinc-400">
                    <a href="{{ route('dashboard') }}" class="hover:text-orange-400 transition-colors">PADDOCK</a>
                    <span>/</span>
                    <span class="text-orange-400 font-bold uppercase">TEAM HANDBOOK</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white uppercase tracking-tight">
                        Team Principal Handbook
                    </h1>
                    <span class="text-xs font-telemetry font-bold px-2.5 py-1 rounded bg-zinc-800 text-zinc-300 border border-zinc-700">
                        OFFICIAL FIA MANUAL
                    </span>
                </div>
                <p class="text-xs text-zinc-400 mt-1">
                    Complete reference for constructor operations, car engineering physics, weather strategy, and championship economics.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    onclick="openTutorialModal()"
                    class="px-4 py-2.5 rounded bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white text-xs font-bold uppercase transition flex items-center gap-2 shadow cursor-pointer"
                >
                    <span>Launch Interactive Tour</span>
                    <span>&rarr;</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Jump Anchor Navigation -->
    <div class="bg-zinc-900/90 border border-zinc-800 rounded p-4 flex flex-wrap items-center gap-2 text-xs font-semibold">
        <span class="text-zinc-500 font-telemetry text-[11px] uppercase mr-1">QUICK JUMP:</span>
        <a href="#finances" class="px-3 py-1 rounded bg-zinc-950 hover:bg-zinc-800 text-zinc-300 border border-zinc-800">1. Economics & Grants</a>
        <a href="#engineering" class="px-3 py-1 rounded bg-zinc-950 hover:bg-zinc-800 text-zinc-300 border border-zinc-800">2. R&D Engineering</a>
        <a href="#strategy" class="px-3 py-1 rounded bg-zinc-950 hover:bg-zinc-800 text-zinc-300 border border-zinc-800">3. Tactics & Weather</a>
        <a href="#points" class="px-3 py-1 rounded bg-zinc-950 hover:bg-zinc-800 text-zinc-300 border border-zinc-800">4. FIA Points & Prizes</a>
    </div>

    <!-- Section 1: Economics & Grants -->
    <div id="finances" class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-zinc-800">
            <span class="w-2.5 h-2.5 rounded bg-amber-500"></span>
            <h2 class="text-base font-bold text-white uppercase tracking-wide">01 // Constructor Economics & Treasury</h2>
        </div>

        <p class="text-xs text-zinc-300 leading-relaxed">
            Financial solvency is vital for survival. Every transaction is recorded transparently in your team's financial ledger:
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div class="bg-zinc-950/80 border border-zinc-800 rounded p-4 space-y-1.5">
                <div class="font-bold text-amber-400 font-telemetry text-sm">Income Streams</div>
                <ul class="text-zinc-400 space-y-1 list-disc list-inside">
                    <li>Grand Prix Prize Money (P1-P10 finish).</li>
                    <li>Fastest Lap Bonus (5% of race prize pool).</li>
                    <li>Onboarding Directives Grant (+5,000 CR).</li>
                </ul>
            </div>

            <div class="bg-zinc-950/80 border border-zinc-800 rounded p-4 space-y-1.5">
                <div class="font-bold text-red-400 font-telemetry text-sm">Expense Outflows</div>
                <ul class="text-zinc-400 space-y-1 list-disc list-inside">
                    <li>Grand Prix Entry Fees (deducted upon race start).</li>
                    <li>Driver Contract Signing Fees (Driver Market).</li>
                    <li>R&D Component Upgrade Fabrication costs.</li>
                </ul>
            </div>

            <div class="bg-zinc-950/80 border border-zinc-800 rounded p-4 space-y-1.5">
                <div class="font-bold text-cyan-400 font-telemetry text-sm">Reputation Points</div>
                <p class="text-zinc-400">
                    Earned by scoring podium finishes and high placements. Demonstrates constructor pedigree in the motorsport world.
                </p>
            </div>
        </div>
    </div>

    <!-- Section 2: R&D Engineering -->
    <div id="engineering" class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-zinc-800">
            <span class="w-2.5 h-2.5 rounded bg-orange-500"></span>
            <h2 class="text-base font-bold text-white uppercase tracking-wide">02 // R&D Component Physics & Upgrades</h2>
        </div>

        <p class="text-xs text-zinc-300 leading-relaxed">
            Each car chassis has 5 modular performance components. Every package can be upgraded from <strong class="text-white font-bold">Tier 1 to Tier 5</strong>. Every 3 cumulative component levels advance the car's <strong class="text-white font-bold">Chassis Tier</strong>.
        </p>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-950 text-zinc-400 uppercase font-telemetry text-[11px] border-b border-zinc-800">
                    <tr>
                        <th class="py-2.5 px-3">Component Package</th>
                        <th class="py-2.5 px-3">Target Stat</th>
                        <th class="py-2.5 px-3">Stat Boost / Tier</th>
                        <th class="py-2.5 px-3">Racing Impact</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 font-telemetry">
                    <tr class="hover:bg-zinc-800/30">
                        <td class="py-3 px-3 font-bold text-white">Powertrain & Turbo</td>
                        <td class="py-3 px-3 text-blue-400 font-bold">Top Speed</td>
                        <td class="py-3 px-3 text-emerald-400 font-bold">+3 SPD</td>
                        <td class="py-3 px-3 text-zinc-300 font-sans">Dominant on long straights (e.g. Monza, Silverstone).</td>
                    </tr>
                    <tr class="hover:bg-zinc-800/30">
                        <td class="py-3 px-3 font-bold text-white">Drivetrain & Gearbox</td>
                        <td class="py-3 px-3 text-cyan-400 font-bold">Acceleration</td>
                        <td class="py-3 px-3 text-emerald-400 font-bold">+3 ACC</td>
                        <td class="py-3 px-3 text-zinc-300 font-sans">Improves corner exits and off-the-line launch starts.</td>
                    </tr>
                    <tr class="hover:bg-zinc-800/30">
                        <td class="py-3 px-3 font-bold text-white">Aero Package & Downforce</td>
                        <td class="py-3 px-3 text-emerald-400 font-bold">Handling</td>
                        <td class="py-3 px-3 text-emerald-400 font-bold">+3 HND</td>
                        <td class="py-3 px-3 text-zinc-300 font-sans">Crucial for tight twists and technical chicanes (Monaco, Suzuka).</td>
                    </tr>
                    <tr class="hover:bg-zinc-800/30">
                        <td class="py-3 px-3 font-bold text-white">Braking System</td>
                        <td class="py-3 px-3 text-amber-400 font-bold">Braking</td>
                        <td class="py-3 px-3 text-emerald-400 font-bold">+3 BRK</td>
                        <td class="py-3 px-3 text-zinc-300 font-sans">Late braking dive-bombs into heavy overtaking zones.</td>
                    </tr>
                    <tr class="hover:bg-zinc-800/30">
                        <td class="py-3 px-3 font-bold text-white">Cooling & Durability</td>
                        <td class="py-3 px-3 text-purple-400 font-bold">Reliability</td>
                        <td class="py-3 px-3 text-emerald-400 font-bold">+4 REL</td>
                        <td class="py-3 px-3 text-zinc-300 font-sans">Minimizes chance of mechanical breakdown or terminal DNF.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Tactics & Weather -->
    <div id="strategy" class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-zinc-800">
            <span class="w-2.5 h-2.5 rounded bg-cyan-500"></span>
            <h2 class="text-base font-bold text-white uppercase tracking-wide">03 // Race Tactics & Weather Conditions</h2>
        </div>

        <p class="text-xs text-zinc-300 leading-relaxed">
            During pre-race briefing, you must instruct your lead driver on the racing mindset for the Grand Prix:
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div class="bg-zinc-950/80 border border-red-500/30 rounded p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-red-400 font-telemetry text-sm uppercase">Aggressive</span>
                    <span class="text-[10px] bg-red-950 text-red-300 px-1.5 py-0.5 rounded border border-red-800 font-telemetry">HIGH RISK</span>
                </div>
                <p class="text-zinc-300">
                    Driver pushes to 105% engine mapping and takes high-risk overtaking lines. Lap pace improves by <strong class="text-white font-bold">+5%</strong>, but spin and collision risk increase substantially.
                </p>
            </div>

            <div class="bg-zinc-950/80 border border-zinc-800 rounded p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-white font-telemetry text-sm uppercase">Balanced</span>
                    <span class="text-[10px] bg-zinc-800 text-zinc-300 px-1.5 py-0.5 rounded border border-zinc-700 font-telemetry">NEUTRAL</span>
                </div>
                <p class="text-zinc-300">
                    Standard baseline telemetry pace. Balances tire temperature, fuel consumption, and overtaking opportunities evenly.
                </p>
            </div>

            <div class="bg-zinc-950/80 border border-emerald-500/30 rounded p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-emerald-400 font-telemetry text-sm uppercase">Conserve</span>
                    <span class="text-[10px] bg-emerald-950 text-emerald-300 px-1.5 py-0.5 rounded border border-emerald-800 font-telemetry">SAFE</span>
                </div>
                <p class="text-zinc-300">
                    Defensive driving focusing on clean laps and zero lockups. Critical for surviving slippery wet track conditions (<strong class="text-cyan-300 font-semibold">Wet Weather</strong>).
                </p>
            </div>
        </div>
    </div>

    <!-- Section 4: FIA Points & Prizes -->
    <div id="points" class="bg-zinc-900 border border-zinc-800 rounded p-6 shadow-lg space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-zinc-800">
            <span class="w-2.5 h-2.5 rounded bg-purple-500"></span>
            <h2 class="text-base font-bold text-white uppercase tracking-wide">04 // Official FIA Championship Scoring & Payouts</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-telemetry">
                <thead class="bg-zinc-950 text-zinc-400 uppercase text-[11px] border-b border-zinc-800">
                    <tr>
                        <th class="py-2.5 px-3">Position</th>
                        <th class="py-2.5 px-3">Championship Points</th>
                        <th class="py-2.5 px-3">Prize Pool Share</th>
                        <th class="py-2.5 px-3">Base Reputation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    <tr class="hover:bg-zinc-800/30 bg-amber-950/20">
                        <td class="py-2.5 px-3 font-bold text-amber-400">&#127942; P1 (Winner)</td>
                        <td class="py-2.5 px-3 font-bold text-purple-400">25 PTS</td>
                        <td class="py-2.5 px-3 text-emerald-400 font-bold">40% of Prize Pool</td>
                        <td class="py-2.5 px-3 text-cyan-400 font-bold">+50 REP</td>
                    </tr>
                    <tr class="hover:bg-zinc-800/30 bg-slate-900/30">
                        <td class="py-2.5 px-3 font-bold text-slate-200">&#129352; P2 (Podium)</td>
                        <td class="py-2.5 px-3 font-bold text-purple-400">18 PTS</td>
                        <td class="py-2.5 px-3 text-emerald-400 font-bold">25% of Prize Pool</td>
                        <td class="py-2.5 px-3 text-cyan-400 font-bold">+30 REP</td>
                    </tr>
                    <tr class="hover:bg-zinc-800/30 bg-orange-950/20">
                        <td class="py-2.5 px-3 font-bold text-orange-300">&#129353; P3 (Podium)</td>
                        <td class="py-2.5 px-3 font-bold text-purple-400">15 PTS</td>
                        <td class="py-2.5 px-3 text-emerald-400 font-bold">15% of Prize Pool</td>
                        <td class="py-2.5 px-3 text-cyan-400 font-bold">+20 REP</td>
                    </tr>
                    <tr class="hover:bg-zinc-800/30">
                        <td class="py-2.5 px-3 font-semibold text-zinc-300">P4 - P10</td>
                        <td class="py-2.5 px-3 text-zinc-400">12, 10, 8, 6, 4, 2, 1 PTS</td>
                        <td class="py-2.5 px-3 text-zinc-400">8%, 5%, 3%, 2%, 1%, 0.5%</td>
                        <td class="py-2.5 px-3 text-zinc-400">+12 down to +1 REP</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
