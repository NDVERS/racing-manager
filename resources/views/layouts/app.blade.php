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
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans antialiased selection:bg-amber-500 selection:text-black">
    <div class="relative min-h-screen flex flex-col bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-900 via-slate-950 to-black">
        <!-- Top Navigation -->
        <header class="border-b border-slate-800/80 backdrop-blur-md bg-slate-950/60 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-tr from-amber-500 to-red-600 flex items-center justify-center font-black text-black tracking-tighter text-lg shadow-lg shadow-amber-500/20 group-hover:scale-105 transition-transform">
                            RM
                        </span>
                        <div class="flex flex-col leading-none">
                            <span class="font-extrabold tracking-wider text-sm text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-orange-300 to-red-500 uppercase">Racing Manager</span>
                            <span class="text-[10px] text-slate-400 font-mono tracking-widest uppercase">Paddock Edition</span>
                        </div>
                    </a>
                </div>

                <!-- Auth / Team Info -->
                <div class="flex items-center gap-4">
                    @auth
                        @if(auth()->user()->team)
                            <div class="hidden sm:flex items-center gap-3 bg-slate-900/90 border border-slate-800 rounded-full px-4 py-1.5 shadow-inner">
                                <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-300">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>{{ auth()->user()->team->name }}</span>
                                </div>
                                <div class="h-3 w-px bg-slate-700"></div>
                                <div class="text-xs font-mono font-bold text-amber-400">
                                    {{ number_format(auth()->user()->team->money) }} <span class="text-[10px] text-slate-400">CR</span>
                                </div>
                                <div class="h-3 w-px bg-slate-700"></div>
                                <div class="text-xs font-mono font-bold text-cyan-400 flex items-center gap-1">
                                    <span class="text-slate-500">REP</span> {{ auth()->user()->team->reputation }}
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-400 hidden md:inline font-medium">{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-red-400 bg-slate-900/80 hover:bg-red-500/10 border border-slate-800 hover:border-red-500/30 rounded-lg px-3 py-1.5 transition-colors">
                                    Log Out
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-300 hover:text-white px-3 py-1.5 rounded-lg hover:bg-slate-900 transition-colors">Log In</a>
                            <a href="{{ route('register') }}" class="text-xs font-bold text-black bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-300 hover:to-orange-400 px-3.5 py-1.5 rounded-lg shadow-md shadow-amber-500/20 transition-all hover:scale-105">Register</a>
                        </div>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
            @if(session('success'))
                <div class="bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 backdrop-blur-md">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-amber-950/80 border border-amber-500/40 text-amber-200 px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 backdrop-blur-md">
                    <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="text-sm font-medium">{{ session('warning') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="bg-cyan-950/80 border border-cyan-500/40 text-cyan-200 px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 backdrop-blur-md">
                    <svg class="w-5 h-5 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-medium">{{ session('info') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-950/80 border border-red-500/40 text-red-200 px-4 py-3 rounded-xl shadow-lg backdrop-blur-md">
                    <div class="flex items-center gap-2 font-semibold text-sm mb-1 text-red-300">
                        <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Please correct the errors below:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 text-red-300/90 pl-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Main Content -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-900 py-6 text-center text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} Racing Manager. Web-based Racing Simulation Game.</p>
        </footer>
    </div>
</body>
</html>
