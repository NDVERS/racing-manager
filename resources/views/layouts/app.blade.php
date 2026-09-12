<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Racing Manager') }} - @yield('title', 'Paddock')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|jetbrains-mono:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen font-sans antialiased selection:bg-orange-500 selection:text-black">
    <div class="relative min-h-screen flex flex-col bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-zinc-900 via-zinc-950 to-black">
        <!-- Top Navigation -->
        <header class="border-b border-zinc-800/80 backdrop-blur-md bg-zinc-950/95 sticky top-0 z-50">
            <div class="max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3 sm:gap-4">
                <!-- Left: Logo & Navigation Tabs -->
                <div class="flex items-center gap-4 xl:gap-6 min-w-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 group shrink-0">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center font-black text-white text-sm shadow-md shadow-orange-600/20 group-hover:scale-105 transition-transform font-mono">
                            RM
                        </span>
                        <div class="hidden sm:flex flex-col leading-none">
                            <span class="font-black tracking-wider text-xs text-white uppercase font-mono">Racing Manager</span>
                            <span class="text-[9px] text-zinc-400 font-telemetry tracking-widest uppercase">Paddock OS</span>
                        </div>
                    </a>

                    @auth
                        @if(auth()->user()->team)
                            <nav class="hidden lg:flex items-center gap-1 xl:gap-1.5 text-xs font-mono font-bold tracking-wider shrink-0">
                                <a
                                    href="{{ route('dashboard') }}"
                                    class="px-2.5 py-1.5 rounded transition-all whitespace-nowrap {{ request()->routeIs('dashboard') ? 'bg-zinc-800 text-orange-400 font-black border border-zinc-700/80 shadow-sm shadow-orange-950/20' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900/80' }}"
                                >
                                    DASHBOARD
                                </a>
                                <a
                                    href="{{ route('garage.index') }}"
                                    class="px-2.5 py-1.5 rounded transition-all whitespace-nowrap {{ request()->routeIs('garage.*') ? 'bg-zinc-800 text-orange-400 font-black border border-zinc-700/80 shadow-sm shadow-orange-950/20' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900/80' }}"
                                >
                                    GARAGE
                                </a>
                                <a
                                    href="{{ route('drivers.index') }}"
                                    class="px-2.5 py-1.5 rounded transition-all whitespace-nowrap {{ request()->routeIs('drivers.*') ? 'bg-zinc-800 text-orange-400 font-black border border-zinc-700/80 shadow-sm shadow-orange-950/20' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900/80' }}"
                                >
                                    DRIVERS
                                </a>
                                <a
                                    href="{{ route('sponsors.index') }}"
                                    class="px-2.5 py-1.5 rounded transition-all whitespace-nowrap {{ request()->routeIs('sponsors.*') ? 'bg-zinc-800 text-orange-400 font-black border border-zinc-700/80 shadow-sm shadow-orange-950/20' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900/80' }}"
                                >
                                    SPONSORS
                                </a>
                                <a
                                    href="{{ route('races.index') }}"
                                    class="px-2.5 py-1.5 rounded transition-all whitespace-nowrap {{ request()->routeIs('races.*') && !request()->routeIs('races.history') ? 'bg-zinc-800 text-orange-400 font-black border border-zinc-700/80 shadow-sm shadow-orange-950/20' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900/80' }}"
                                >
                                    RACES
                                </a>
                                <a
                                    href="{{ route('standings') }}"
                                    class="px-2.5 py-1.5 rounded transition-all whitespace-nowrap {{ request()->routeIs('standings') || request()->routeIs('championship') ? 'bg-zinc-800 text-orange-400 font-black border border-zinc-700/80 shadow-sm shadow-orange-950/20' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900/80' }}"
                                >
                                    STANDINGS
                                </a>
                                <a
                                    href="{{ route('races.history') }}"
                                    class="px-2.5 py-1.5 rounded transition-all whitespace-nowrap {{ request()->routeIs('races.history') || request()->routeIs('history') || request()->routeIs('race-results.*') ? 'bg-zinc-800 text-orange-400 font-black border border-zinc-700/80 shadow-sm shadow-orange-950/20' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900/80' }}"
                                >
                                    HISTORY
                                </a>
                                <a
                                    href="{{ route('guide') }}"
                                    class="px-2.5 py-1.5 rounded transition-all whitespace-nowrap {{ request()->routeIs('guide') ? 'bg-zinc-800 text-orange-400 font-black border border-zinc-700/80 shadow-sm shadow-orange-950/20' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900/80' }}"
                                >
                                    HANDBOOK
                                </a>
                            </nav>
                        @endif
                    @endauth
                </div>

                <!-- Right: Team Financial Status & User Menu -->
                <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
                    @auth
                        @if(auth()->user()->team)
                            <!-- Unified Team Telemetry HUD Pill -->
                            <div class="hidden xl:flex items-center gap-2.5 2xl:gap-3 bg-zinc-900/90 border border-zinc-800 rounded-full px-3.5 py-1.5 shadow-inner text-xs">
                                <div class="flex items-center gap-1.5 font-semibold text-zinc-200 whitespace-nowrap">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="font-mono">{{ auth()->user()->team->name }}</span>
                                </div>
                                <div class="h-3 w-px bg-zinc-800"></div>
                                <div class="font-telemetry font-bold text-amber-400 whitespace-nowrap">
                                    {{ number_format(auth()->user()->team->money) }} <span class="text-[10px] text-zinc-500 font-normal">CR</span>
                                </div>
                                <div class="h-3 w-px bg-zinc-800"></div>
                                <div class="font-telemetry font-bold text-cyan-400 flex items-center gap-1 whitespace-nowrap">
                                    <span class="text-[10px] text-zinc-500 font-normal">REP</span> {{ auth()->user()->team->reputation }}
                                </div>
                            </div>

                            <!-- Interactive Tutorial Quick Trigger -->
                            <button
                                type="button"
                                onclick="openTutorialModal()"
                                class="flex items-center gap-1.5 text-xs font-mono font-bold text-orange-400 hover:text-orange-300 bg-orange-950/40 hover:bg-orange-950/70 border border-orange-500/40 rounded px-2.5 py-1.5 transition-colors cursor-pointer whitespace-nowrap shadow-sm"
                                title="Open Pit-Wall Operations Manual"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-400 animate-ping"></span>
                                <span>GUIDE</span>
                            </button>
                        @endif

                        <!-- User Profile & Log Out Group -->
                        <div class="flex items-center gap-2 pl-1 border-l border-zinc-800/80">
                            <div class="hidden 2xl:flex items-center gap-1.5 px-2 py-1 rounded bg-zinc-900/60 border border-zinc-800/60">
                                <span class="w-5 h-5 rounded-full bg-zinc-800 text-zinc-300 flex items-center justify-center text-[10px] font-bold font-telemetry">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                                <span class="text-xs text-zinc-300 font-medium whitespace-nowrap font-mono">{{ auth()->user()->name }}</span>
                            </div>

                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button
                                    type="submit"
                                    class="text-xs font-mono font-bold text-zinc-400 hover:text-red-400 hover:bg-red-500/10 border border-transparent hover:border-red-500/30 rounded px-2.5 py-1.5 transition-colors cursor-pointer whitespace-nowrap"
                                    title="Sign Out of Session"
                                >
                                    LOG OUT
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex items-center gap-2 font-mono">
                            <a href="{{ route('login') }}" class="text-xs font-bold text-zinc-300 hover:text-white px-3 py-1.5 rounded hover:bg-zinc-900 transition-colors">Sign In</a>
                            <a href="{{ route('register') }}" class="text-xs font-bold text-white bg-orange-600 hover:bg-orange-500 px-3.5 py-1.5 rounded shadow transition-all">Register</a>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Mobile Sub-Navigation -->
            @auth
                @if(auth()->user()->team)
                    <div class="lg:hidden border-t border-zinc-800/80 px-4 py-2 flex items-center gap-1.5 overflow-x-auto text-xs font-mono font-bold scrollbar-none bg-zinc-950">
                        <a
                            href="{{ route('dashboard') }}"
                            class="px-2.5 py-1 rounded whitespace-nowrap {{ request()->routeIs('dashboard') ? 'bg-zinc-800 text-orange-400 border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200' }}"
                        >
                            DASHBOARD
                        </a>
                        <a
                            href="{{ route('garage.index') }}"
                            class="px-2.5 py-1 rounded whitespace-nowrap {{ request()->routeIs('garage.*') ? 'bg-zinc-800 text-orange-400 border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200' }}"
                        >
                            GARAGE
                        </a>
                        <a
                            href="{{ route('drivers.index') }}"
                            class="px-2.5 py-1 rounded whitespace-nowrap {{ request()->routeIs('drivers.*') ? 'bg-zinc-800 text-orange-400 border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200' }}"
                        >
                            DRIVERS
                        </a>
                        <a
                            href="{{ route('sponsors.index') }}"
                            class="px-2.5 py-1 rounded whitespace-nowrap {{ request()->routeIs('sponsors.*') ? 'bg-zinc-800 text-orange-400 border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200' }}"
                        >
                            SPONSORS
                        </a>
                        <a
                            href="{{ route('races.index') }}"
                            class="px-2.5 py-1 rounded whitespace-nowrap {{ request()->routeIs('races.*') && !request()->routeIs('races.history') ? 'bg-zinc-800 text-orange-400 border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200' }}"
                        >
                            RACES
                        </a>
                        <a
                            href="{{ route('standings') }}"
                            class="px-2.5 py-1 rounded whitespace-nowrap {{ request()->routeIs('standings') || request()->routeIs('championship') ? 'bg-zinc-800 text-orange-400 border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200' }}"
                        >
                            STANDINGS
                        </a>
                        <a
                            href="{{ route('races.history') }}"
                            class="px-2.5 py-1 rounded whitespace-nowrap {{ request()->routeIs('races.history') || request()->routeIs('history') || request()->routeIs('race-results.*') ? 'bg-zinc-800 text-orange-400 border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200' }}"
                        >
                            HISTORY
                        </a>
                        <a
                            href="{{ route('guide') }}"
                            class="px-2.5 py-1 rounded whitespace-nowrap {{ request()->routeIs('guide') ? 'bg-zinc-800 text-orange-400 border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200' }}"
                        >
                            HANDBOOK
                        </a>
                    </div>
                @endif
            @endauth
        </header>

        <!-- Flash Messages -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
            @if(session('success'))
                <div class="bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 px-4 py-3 rounded shadow-lg flex items-center gap-3">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="text-xs font-mono font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-amber-950/80 border border-amber-500/40 text-amber-200 px-4 py-3 rounded shadow-lg flex items-center gap-3">
                    <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="text-xs font-mono font-medium">{{ session('warning') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="bg-cyan-950/80 border border-cyan-500/40 text-cyan-200 px-4 py-3 rounded shadow-lg flex items-center gap-3">
                    <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-xs font-mono font-medium">{{ session('info') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-950/80 border border-red-500/40 text-red-200 px-4 py-3 rounded shadow-lg">
                    <div class="flex items-center gap-2 font-bold text-xs mb-1 text-red-300 font-mono">
                        <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Errors Detected:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5 text-red-300/90 pl-1 font-mono">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Main Content -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="border-t border-zinc-900 py-6 text-center text-[11px] text-zinc-500 font-mono">
            <p>&copy; {{ date('Y') }} Racing Manager Simulation &bull; Paddock Command System</p>
        </footer>
    </div>

    <!-- Pit-Wall Operations Manual Modal -->
    @include('components.tutorial-modal')
</body>
</html>

