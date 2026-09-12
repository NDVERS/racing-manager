<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Racing Manager') }} - @yield('title', 'Paddock')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen font-sans antialiased selection:bg-orange-500 selection:text-black">
    <div class="relative min-h-screen flex flex-col bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-zinc-900 via-zinc-950 to-black">
        <!-- Top Navigation -->
        <header class="border-b border-zinc-800/80 backdrop-blur-md bg-zinc-950/80 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Left: Logo & Navigation Tabs -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                        <span class="w-8 h-8 rounded bg-orange-600 flex items-center justify-center font-black text-white tracking-tighter text-sm shadow-md group-hover:scale-105 transition-transform">
                            RM
                        </span>
                        <div class="flex flex-col leading-none">
                            <span class="font-extrabold tracking-wider text-xs text-white uppercase font-mono">Racing Manager</span>
                            <span class="text-[9px] text-zinc-400 font-mono tracking-widest uppercase">Paddock OS</span>
                        </div>
                    </a>

                    @auth
                        @if(auth()->user()->team)
                            <nav class="hidden md:flex items-center gap-1 text-xs font-mono">
                                <a
                                    href="{{ route('dashboard') }}"
                                    class="px-3 py-1.5 rounded transition-colors {{ request()->routeIs('dashboard') ? 'bg-zinc-800 text-orange-400 font-bold border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900' }}"
                                >
                                    DASHBOARD
                                </a>
                                <a
                                    href="{{ route('garage.index') }}"
                                    class="px-3 py-1.5 rounded transition-colors {{ request()->routeIs('garage.*') ? 'bg-zinc-800 text-orange-400 font-bold border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900' }}"
                                >
                                    GARAGE
                                </a>
                                <a
                                    href="{{ route('drivers.index') }}"
                                    class="px-3 py-1.5 rounded transition-colors {{ request()->routeIs('drivers.*') ? 'bg-zinc-800 text-orange-400 font-bold border border-zinc-700' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900' }}"
                                >
                                    DRIVERS
                                </a>
                                <span class="px-3 py-1.5 rounded text-zinc-600 cursor-not-allowed flex items-center gap-1" title="Race Hub unlocks in Task 5">
                                    <span>RACES</span>
                                    <span class="text-[9px] bg-zinc-900 text-zinc-600 px-1.5 py-0.5 rounded border border-zinc-800">T5</span>
                                </span>
                            </nav>
                        @endif
                    @endauth
                </div>

                <!-- Right: Team Financial Status & User Menu -->
                <div class="flex items-center gap-4">
                    @auth
                        @if(auth()->user()->team)
                            <div class="hidden sm:flex items-center gap-3 bg-zinc-900/90 border border-zinc-800 rounded px-3.5 py-1.5 shadow-inner">
                                <div class="flex items-center gap-1.5 text-xs font-semibold text-zinc-300">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>{{ auth()->user()->team->name }}</span>
                                </div>
                                <div class="h-3 w-px bg-zinc-700"></div>
                                <div class="text-xs font-mono font-bold text-amber-400">
                                    {{ number_format(auth()->user()->team->money) }} <span class="text-[10px] text-zinc-400">CR</span>
                                </div>
                                <div class="h-3 w-px bg-zinc-700"></div>
                                <div class="text-xs font-mono font-bold text-cyan-400 flex items-center gap-1">
                                    <span class="text-zinc-500">REP</span> {{ auth()->user()->team->reputation }}
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-3">
                            <span class="text-xs text-zinc-400 hidden lg:inline font-mono">{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs font-mono font-semibold text-zinc-400 hover:text-red-400 bg-zinc-900 hover:bg-red-500/10 border border-zinc-800 hover:border-red-500/30 rounded px-3 py-1.5 transition-colors cursor-pointer">
                                    LOG OUT
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <a href="{{ route('login') }}" class="text-xs font-semibold text-zinc-300 hover:text-white px-3 py-1.5 rounded hover:bg-zinc-900 transition-colors">Sign In</a>
                            <a href="{{ route('register') }}" class="text-xs font-bold text-white bg-orange-600 hover:bg-orange-500 px-3.5 py-1.5 rounded shadow transition-all">Register</a>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Mobile Sub-Navigation -->
            @auth
                @if(auth()->user()->team)
                    <div class="md:hidden border-t border-zinc-800/80 px-4 py-2 flex items-center gap-2 overflow-x-auto text-xs font-mono">
                        <a
                            href="{{ route('dashboard') }}"
                            class="px-3 py-1 rounded {{ request()->routeIs('dashboard') ? 'bg-zinc-800 text-orange-400 font-bold' : 'text-zinc-400' }}"
                        >
                            DASHBOARD
                        </a>
                        <a
                            href="{{ route('garage.index') }}"
                            class="px-3 py-1 rounded {{ request()->routeIs('garage.*') ? 'bg-zinc-800 text-orange-400 font-bold' : 'text-zinc-400' }}"
                        >
                            GARAGE
                        </a>
                        <a
                            href="{{ route('drivers.index') }}"
                            class="px-3 py-1 rounded {{ request()->routeIs('drivers.*') ? 'bg-zinc-800 text-orange-400 font-bold' : 'text-zinc-400' }}"
                        >
                            DRIVERS
                        </a>
                        <span class="px-2 py-1 text-zinc-600">RACES (T5)</span>
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
</body>
</html>
