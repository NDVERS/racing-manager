<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Racing Manager') }} - @yield('title', 'Pit Wall Access')</title>

    <!-- Favicon & App Branding Icons -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#09090b">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|jetbrains-mono:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes scanline {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(1200%); }
        }
        .telemetry-scanline {
            animation: scanline 7s linear infinite;
        }
        @keyframes bar-pulse-1 { 0%, 100% { height: 40%; } 50% { height: 95%; } }
        @keyframes bar-pulse-2 { 0%, 100% { height: 75%; } 50% { height: 30%; } }
        @keyframes bar-pulse-3 { 0%, 100% { height: 60%; } 50% { height: 100%; } }
        @keyframes bar-pulse-4 { 0%, 100% { height: 90%; } 50% { height: 50%; } }
        @keyframes bar-pulse-5 { 0%, 100% { height: 50%; } 50% { height: 85%; } }
        .bar-anim-1 { animation: bar-pulse-1 1.4s ease-in-out infinite; }
        .bar-anim-2 { animation: bar-pulse-2 1.1s ease-in-out infinite; }
        .bar-anim-3 { animation: bar-pulse-3 1.6s ease-in-out infinite; }
        .bar-anim-4 { animation: bar-pulse-4 1.2s ease-in-out infinite; }
        .bar-anim-5 { animation: bar-pulse-5 1.5s ease-in-out infinite; }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 min-h-screen font-sans antialiased selection:bg-orange-500 selection:text-black">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Left Side: Motorsport Atmosphere & Telemetry HUD (Desktop 58%) -->
        <div class="relative hidden lg:flex lg:w-7/12 bg-zinc-900 border-r border-zinc-800 flex-col justify-between p-12 overflow-hidden select-none">
            <!-- Background Grid & Subtle Asphalt Texture -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#27272a_1px,transparent_1px),linear-gradient(to_bottom,#27272a_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_40%,#000_70%,transparent_100%)] opacity-30 pointer-events-none"></div>

            <!-- Subtle CRT Telemetry Scanline Beam -->
            <div class="absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-transparent via-cyan-500/10 to-transparent pointer-events-none telemetry-scanline opacity-60"></div>

            <!-- Racing Livery Vertical Stripe -->
            <div class="absolute top-0 bottom-0 left-0 w-1.5 bg-gradient-to-b from-orange-500 via-amber-400 to-red-600"></div>

            <!-- Top Header / Brand & Live Time -->
            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <x-application-logo class="w-9 h-9 rounded-lg shadow-md shrink-0" />
                    <div>
                        <div class="text-xs font-black tracking-widest text-zinc-100 uppercase font-mono">Racing Manager</div>
                        <div class="text-[10px] text-zinc-400 font-mono tracking-wider uppercase">Paddock Telemetry OS</div>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 bg-zinc-950/80 border border-zinc-800 px-3 py-1.5 rounded text-[11px] font-mono text-zinc-400 shadow-inner">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-emerald-400 font-bold">ONLINE</span>
                    <span class="text-zinc-600">|</span>
                    <span id="telemetry-clock" class="text-orange-400 font-bold">--:--:-- UTC</span>
                </div>
            </div>

            <!-- Central Content: Hero & Telemetry Data HUD -->
            <div class="relative z-10 my-auto py-8 max-w-xl">
                <div class="inline-flex items-center gap-2 bg-orange-950/60 border border-orange-500/30 text-orange-400 text-[11px] font-mono uppercase tracking-widest px-2.5 py-1 rounded mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-ping"></span>
                    <span>PIT WALL TELEMETRY TERMINAL</span>
                </div>

                <h1 class="text-4xl font-black tracking-tight text-white uppercase leading-none mb-4">
                    Precision.<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-amber-300">Strategy.</span><br>
                    Championship.
                </h1>

                <p class="text-sm text-zinc-400 leading-relaxed mb-8">
                    Take the pit wall as Team Manager. Direct race strategies, optimize aerodynamics and engine upgrades, negotiate driver contracts, and climb from local circuits to global glory.
                </p>

                <!-- Live Telemetry Simulator Widget -->
                <div class="bg-zinc-950/90 border border-zinc-800 rounded-md p-4 font-mono text-xs shadow-2xl backdrop-blur-sm relative">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-zinc-800/80 text-[11px] text-zinc-400">
                        <span class="flex items-center gap-2 text-zinc-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                            <span>LIVE PADDOCK TELEMETRY</span>
                        </span>
                        <div class="flex items-center gap-2 text-[10px]">
                            <span class="text-zinc-500">TRACK:</span>
                            <span class="text-amber-400 font-bold">SENTUL SPEEDWAY</span>
                            <span class="text-zinc-600">|</span>
                            <span class="text-emerald-400">28°C DRY</span>
                        </div>
                    </div>

                    <!-- Telemetry Meters -->
                    <div class="grid grid-cols-2 gap-3 text-[11px]">
                        <!-- Lap Delta Target with live fluctuating number -->
                        <div class="bg-zinc-900/90 p-2.5 rounded border border-zinc-800/60">
                            <div class="text-zinc-500 text-[10px] uppercase flex justify-between">
                                <span>Optimal Lap Delta</span>
                                <span class="text-[9px] text-zinc-500">SECTOR 1-2</span>
                            </div>
                            <div class="text-white font-bold text-sm mt-0.5 flex items-baseline gap-1.5">
                                <span id="telemetry-lap">1:18.420</span>
                                <span id="telemetry-delta" class="text-emerald-400 text-[10px] font-mono transition-all duration-300">(-0.214s)</span>
                            </div>
                        </div>

                        <!-- RPM & Throttle Trace Indicator with dynamic bars -->
                        <div class="bg-zinc-900/90 p-2.5 rounded border border-zinc-800/60 flex flex-col justify-between">
                            <div class="text-zinc-500 text-[10px] uppercase flex justify-between">
                                <span>Engine Output Trace</span>
                                <span class="text-[9px] text-amber-400 font-bold">PUSH MODE</span>
                            </div>
                            <div class="flex items-end justify-between gap-1 h-5 mt-1 pt-1">
                                <div class="w-2 bg-orange-500 rounded-xs bar-anim-1"></div>
                                <div class="w-2 bg-amber-500 rounded-xs bar-anim-2"></div>
                                <div class="w-2 bg-orange-400 rounded-xs bar-anim-3"></div>
                                <div class="w-2 bg-amber-400 rounded-xs bar-anim-4"></div>
                                <div class="w-2 bg-orange-500 rounded-xs bar-anim-5"></div>
                                <div class="w-2 bg-red-500 rounded-xs bar-anim-2"></div>
                                <div class="w-2 bg-amber-500 rounded-xs bar-anim-1"></div>
                                <div class="w-2 bg-orange-600 rounded-xs bar-anim-3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Speed & Asset Summary Strip -->
                    <div class="mt-3 pt-3 border-t border-zinc-800/60 flex items-center justify-between text-[11px] text-zinc-400">
                        <div class="flex items-center gap-1.5">
                            <span>SPEED TRAP:</span>
                            <span id="telemetry-speed" class="text-white font-bold">314.8</span>
                            <span class="text-[10px] text-zinc-500">KM/H</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span>BUDGET: <strong class="text-amber-400">50,000 CR</strong></span>
                            <span class="text-zinc-600">|</span>
                            <span>CAR: <strong class="text-zinc-200">KURO GT</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Left Info -->
            <div class="relative z-10 flex items-center justify-between text-[11px] text-zinc-500 font-mono border-t border-zinc-800/80 pt-4">
                <span>&copy; {{ date('Y') }} Racing Manager Simulation</span>
                <div class="flex items-center gap-2">
                    <span>PKT: <strong id="telemetry-packet" class="text-zinc-400">14,890</strong></span>
                    <span class="text-zinc-600">|</span>
                    <span class="text-emerald-400">LOSS: 0.0%</span>
                </div>
            </div>
        </div>

        <!-- Right Side: Authentication Form Area (Desktop 42% / Mobile 100%) -->
        <div class="w-full lg:w-5/12 bg-zinc-950 flex flex-col justify-between p-6 sm:p-12 min-h-screen">
            <!-- Mobile Top Logo (Visible only on mobile/tablet) -->
            <div class="flex lg:hidden items-center justify-between pb-6 border-b border-zinc-800/80 mb-6">
                <div class="flex items-center gap-2.5">
                    <x-application-logo class="w-8 h-8 rounded-lg shadow-md shrink-0" />
                    <span class="font-black text-sm text-white uppercase tracking-wider font-mono">Racing Manager</span>
                </div>
                <span class="text-[10px] font-mono text-zinc-400 bg-zinc-900 px-2.5 py-1 rounded border border-zinc-800">PADDOCK</span>
            </div>

            <!-- Form Container -->
            <div class="my-auto w-full max-w-md mx-auto">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-5 bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 px-3.5 py-2.5 rounded text-xs flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-5 bg-cyan-950/80 border border-cyan-500/40 text-cyan-200 px-3.5 py-2.5 rounded text-xs flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 bg-red-950/80 border border-red-500/40 text-red-200 px-3.5 py-2.5 rounded text-xs">
                        <div class="font-bold flex items-center gap-1.5 text-red-300 mb-1">
                            <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Authentication Error</span>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-red-300/90 pl-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>

            <!-- Mobile / Bottom Footer -->
            <div class="pt-6 border-t border-zinc-900 text-center lg:text-left text-[11px] text-zinc-600 font-mono mt-6">
                <span>Racing Manager &bull; Paddock Authentication Portal</span>
            </div>
        </div>
    </div>

    <!-- Lightweight Vanilla JS Pit-Wall Telemetry Simulation -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Live UTC Clock
            const clockEl = document.getElementById('telemetry-clock');
            function updateClock() {
                if (!clockEl) return;
                const now = new Date();
                const hours = String(now.getUTCHours()).padStart(2, '0');
                const minutes = String(now.getUTCMinutes()).padStart(2, '0');
                const seconds = String(now.getUTCSeconds()).padStart(2, '0');
                clockEl.textContent = `${hours}:${minutes}:${seconds} UTC`;
            }
            updateClock();
            setInterval(updateClock, 1000);

            // 2. Dynamic Telemetry Fluctuations
            const deltaEl = document.getElementById('telemetry-delta');
            const lapEl = document.getElementById('telemetry-lap');
            const speedEl = document.getElementById('telemetry-speed');
            const packetEl = document.getElementById('telemetry-packet');

            let packetCount = 14890;

            setInterval(function () {
                // Fluctuating lap delta
                if (deltaEl && lapEl) {
                    const baseDelta = (Math.random() * (0.280 - 0.160) + 0.160).toFixed(3);
                    const isPositive = Math.random() > 0.85;
                    const sign = isPositive ? '+' : '-';
                    const colorClass = isPositive ? 'text-amber-400' : 'text-emerald-400';

                    deltaEl.className = `${colorClass} text-[10px] font-mono transition-all duration-300 scale-105`;
                    deltaEl.textContent = `(${sign}${baseDelta}s)`;

                    setTimeout(() => {
                        if (deltaEl) deltaEl.classList.remove('scale-105');
                    }, 300);
                }

                // Fluctuating speed trap
                if (speedEl) {
                    const speed = (Math.random() * (316.5 - 312.0) + 312.0).toFixed(1);
                    speedEl.textContent = speed;
                }

                // Increment packet count
                if (packetEl) {
                    packetCount += Math.floor(Math.random() * 4) + 1;
                    packetEl.textContent = packetCount.toLocaleString();
                }
            }, 2400);
        });
    </script>
</body>
</html>
