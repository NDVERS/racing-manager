@extends('layouts.app')

@section('title', 'Establish Your Racing Team')

@section('content')
<div class="max-w-4xl mx-auto my-6">
    <!-- Header Banner -->
    <div class="text-center mb-8">
        <span class="text-xs font-mono font-bold tracking-widest text-amber-400 bg-amber-500/10 border border-amber-500/20 px-3.5 py-1 rounded-full uppercase">
            Initial Team Onboarding
        </span>
        <h1 class="text-3xl font-black text-white uppercase tracking-tight mt-3">
            Found Your Racing Team
        </h1>
        <p class="text-sm text-slate-400 max-w-lg mx-auto mt-2">
            Every manager starts with a team identity, starter capital, an initial race car, and an eager young driver ready for testing.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Team Name Form (Left / Main) -->
        <div class="lg:col-span-7">
            <div class="bg-slate-900/80 border border-slate-800 backdrop-blur-xl rounded-2xl p-6 sm:p-8 shadow-xl">
                <h2 class="text-base font-bold text-white uppercase tracking-wider mb-6 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    Team Registration
                </h2>

                <form method="POST" action="{{ route('team.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                            Official Team Name
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', 'Kuro Racing') }}"
                            required
                            autofocus
                            minlength="3"
                            maxlength="50"
                            placeholder="e.g. Apex Motorsport"
                            class="w-full bg-slate-950 border border-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 outline-none transition-all font-semibold"
                        >
                        <p class="text-[11px] text-slate-500 mt-2">
                            This name will represent your team in races, standings, and financial reports.
                        </p>
                    </div>

                    <!-- Starting Capital Badge -->
                    <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 font-bold text-sm">
                                CR
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-slate-400 uppercase">Starting Budget</div>
                                <div class="text-xs text-slate-500">Ready for garage and race entry</div>
                            </div>
                        </div>
                        <div class="text-lg font-mono font-black text-amber-400">
                            {{ number_format($startingCredits) }} <span class="text-xs font-sans text-slate-400">CR</span>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-4 px-6 rounded-xl font-bold text-sm text-black bg-gradient-to-r from-amber-400 via-orange-500 to-red-500 hover:from-amber-300 hover:via-orange-400 hover:to-red-400 shadow-xl shadow-orange-500/20 active:scale-[0.98] transition-all cursor-pointer uppercase tracking-wider flex items-center justify-center gap-2"
                    >
                        <span>Confirm Team & Receive Starter Assets</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Starter Assets Preview (Right / Sidebar) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Starter Car Card -->
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-amber-400 uppercase tracking-wider font-mono">Starter Car</span>
                    </div>
                    <span class="text-[10px] uppercase font-bold tracking-wider bg-slate-800 text-slate-300 px-2 py-0.5 rounded">Tier 1</span>
                </div>

                <div class="text-xl font-extrabold text-white mb-3">{{ $starterCar['name'] }}</div>

                <div class="space-y-2 text-xs">
                    <div>
                        <div class="flex justify-between text-slate-400 mb-1">
                            <span>Top Speed</span>
                            <span class="font-mono text-white">{{ $starterCar['speed'] }}/100</span>
                        </div>
                        <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-amber-500 h-full rounded-full" style="width: {{ $starterCar['speed'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-slate-400 mb-1">
                            <span>Acceleration</span>
                            <span class="font-mono text-white">{{ $starterCar['acceleration'] }}/100</span>
                        </div>
                        <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-orange-500 h-full rounded-full" style="width: {{ $starterCar['acceleration'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-slate-400 mb-1">
                            <span>Handling</span>
                            <span class="font-mono text-white">{{ $starterCar['handling'] }}/100</span>
                        </div>
                        <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-cyan-500 h-full rounded-full" style="width: {{ $starterCar['handling'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-slate-400 mb-1">
                            <span>Reliability</span>
                            <span class="font-mono text-white">{{ $starterCar['reliability'] }}/100</span>
                        </div>
                        <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $starterCar['reliability'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Starter Driver Card -->
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-cyan-400 uppercase tracking-wider font-mono">Lead Driver</span>
                    </div>
                    <span class="text-[10px] uppercase font-bold tracking-wider bg-slate-800 text-slate-300 px-2 py-0.5 rounded">Rookie</span>
                </div>

                <div class="text-xl font-extrabold text-white mb-3">{{ $starterDriver['name'] }}</div>

                <div class="grid grid-cols-2 gap-3 text-xs mb-3">
                    <div class="bg-slate-950/80 p-2.5 rounded-xl border border-slate-800/60">
                        <div class="text-[10px] text-slate-500 uppercase font-mono">Pace</div>
                        <div class="text-sm font-bold font-mono text-white">{{ $starterDriver['pace'] }}</div>
                    </div>
                    <div class="bg-slate-950/80 p-2.5 rounded-xl border border-slate-800/60">
                        <div class="text-[10px] text-slate-500 uppercase font-mono">Cornering</div>
                        <div class="text-sm font-bold font-mono text-white">{{ $starterDriver['cornering'] }}</div>
                    </div>
                    <div class="bg-slate-950/80 p-2.5 rounded-xl border border-slate-800/60">
                        <div class="text-[10px] text-slate-500 uppercase font-mono">Consistency</div>
                        <div class="text-sm font-bold font-mono text-white">{{ $starterDriver['consistency'] }}</div>
                    </div>
                    <div class="bg-slate-950/80 p-2.5 rounded-xl border border-slate-800/60">
                        <div class="text-[10px] text-slate-500 uppercase font-mono">Experience</div>
                        <div class="text-sm font-bold font-mono text-white">{{ $starterDriver['experience'] }}</div>
                    </div>
                </div>

                <div class="flex justify-between items-center text-xs text-slate-400 pt-2 border-t border-slate-800/80">
                    <span>Base Salary:</span>
                    <span class="font-mono text-amber-400 font-bold">{{ number_format($starterDriver['salary']) }} CR / race</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
