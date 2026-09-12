@extends('layouts.auth')

@section('title', 'Sign In - Pit Wall Access')

@section('content')
<div>
    <div class="mb-8">
        <div class="inline-block text-[10px] font-mono font-bold tracking-widest text-orange-400 bg-orange-950/60 border border-orange-500/30 px-2 py-0.5 rounded uppercase mb-2">
            PADDOCK ACCESS // VERIFICATION
        </div>
        <h2 class="text-2xl font-black text-white uppercase tracking-tight">
            Sign In to Paddock
        </h2>
        <p class="text-xs text-zinc-400 mt-1 font-mono">
            Enter your credentials to access telemetry, garage, and team operations.
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email -->
        <div>
            <label for="email" class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-1.5 font-mono">
                Email Address
            </label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="manager@team.com"
                class="w-full bg-zinc-900 border border-zinc-700/80 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 focus:bg-zinc-900/90 rounded px-3.5 py-2.5 text-sm text-white placeholder-zinc-500 outline-none transition-all duration-150 font-mono"
            >
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-1.5 font-mono">
                Password
            </label>
            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="w-full bg-zinc-900 border border-zinc-700/80 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 focus:bg-zinc-900/90 rounded px-3.5 py-2.5 text-sm text-white placeholder-zinc-500 outline-none transition-all duration-150 font-mono"
            >
        </div>

        <!-- Stay logged in -->
        <div class="flex items-center justify-between text-xs pt-1">
            <label class="flex items-center gap-2 cursor-pointer text-zinc-400 hover:text-zinc-300 select-none">
                <input
                    type="checkbox"
                    name="remember"
                    class="rounded bg-zinc-900 border-zinc-700 text-orange-600 focus:ring-orange-500/30 focus:ring-offset-zinc-950"
                >
                <span class="font-mono text-[11px]">Stay logged in</span>
            </label>
        </div>

        <!-- Submit Button -->
        <button
            type="submit"
            class="group relative overflow-hidden w-full mt-2 py-3 px-4 rounded font-bold text-xs text-white bg-orange-600 hover:bg-orange-500 active:bg-orange-700 active:scale-[0.99] transition-all duration-150 uppercase tracking-widest font-mono cursor-pointer shadow-md flex items-center justify-center gap-2"
        >
            <span>Sign In to Paddock</span>
            <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>
    </form>

    <div class="mt-8 pt-6 border-t border-zinc-800/80 text-xs text-zinc-400 font-mono flex items-center justify-between">
        <span>No manager license?</span>
        <a href="{{ route('register') }}" class="font-bold text-orange-400 hover:text-orange-300 underline underline-offset-4 transition-colors">
            Apply for Manager License &rarr;
        </a>
    </div>
</div>
@endsection
