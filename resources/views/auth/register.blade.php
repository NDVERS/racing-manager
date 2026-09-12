@extends('layouts.auth')

@section('title', 'Register - Apply for Manager License')

@section('content')
<div>
    <div class="mb-8">
        <div class="inline-block text-[10px] font-mono font-bold tracking-widest text-orange-400 bg-orange-950/60 border border-orange-500/30 px-2 py-0.5 rounded uppercase mb-2">
            CONSTRUCTOR FEDERATION // LICENSING
        </div>
        <h2 class="text-2xl font-black text-white uppercase tracking-tight">
            Apply for Manager License
        </h2>
        <p class="text-xs text-zinc-400 mt-1 font-mono">
            Register your official profile to found a racing team and enter championship races.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Manager Name -->
        <div>
            <label for="name" class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-1.5 font-mono">
                Team Manager Name
            </label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                placeholder="e.g. Jordan Wolfe"
                class="w-full bg-zinc-900 border border-zinc-700/80 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 focus:bg-zinc-900/90 rounded px-3.5 py-2.5 text-sm text-white placeholder-zinc-500 outline-none transition-all duration-150 font-mono"
            >
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-1.5 font-mono">
                Official Email
            </label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                placeholder="manager@team.com"
                class="w-full bg-zinc-900 border border-zinc-700/80 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 focus:bg-zinc-900/90 rounded px-3.5 py-2.5 text-sm text-white placeholder-zinc-500 outline-none transition-all duration-150 font-mono"
            >
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-1.5 font-mono">
                Access Password (min. 8 chars)
            </label>
            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="••••••••"
                class="w-full bg-zinc-900 border border-zinc-700/80 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 focus:bg-zinc-900/90 rounded px-3.5 py-2.5 text-sm text-white placeholder-zinc-500 outline-none transition-all duration-150 font-mono"
            >
        </div>

        <!-- Password Confirmation -->
        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-zinc-300 uppercase tracking-wider mb-1.5 font-mono">
                Confirm Password
            </label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="••••••••"
                class="w-full bg-zinc-900 border border-zinc-700/80 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 focus:bg-zinc-900/90 rounded px-3.5 py-2.5 text-sm text-white placeholder-zinc-500 outline-none transition-all duration-150 font-mono"
            >
        </div>

        <!-- Submit Button -->
        <button
            type="submit"
            class="group relative overflow-hidden w-full mt-2 py-3 px-4 rounded font-bold text-xs text-white bg-orange-600 hover:bg-orange-500 active:bg-orange-700 active:scale-[0.99] transition-all duration-150 uppercase tracking-widest font-mono cursor-pointer shadow-md flex items-center justify-center gap-2"
        >
            <span>Register New Team Manager</span>
            <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>
    </form>

    <div class="mt-8 pt-6 border-t border-zinc-800/80 text-xs text-zinc-400 font-mono flex items-center justify-between">
        <span>Already hold a license?</span>
        <a href="{{ route('login') }}" class="font-bold text-orange-400 hover:text-orange-300 underline underline-offset-4 transition-colors">
            Sign In to Paddock &rarr;
        </a>
    </div>
</div>
@endsection
