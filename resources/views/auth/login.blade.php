<x-guest-layout title="Sign In">
    {{-- Form Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold tracking-tight text-slate-950">Welcome back</h2>
        <p class="text-sm text-slate-500 mt-1">Sign in to your account to manage tickets, orders, or your event portal.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                Email Address
            </label>
            <div class="relative rounded-xl shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </div>
                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       autocomplete="username"
                       placeholder="you@example.com"
                       class="block w-full rounded-xl border-slate-300 pl-10 pr-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-600 font-medium" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Password
                </label>
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 transition-colors" href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                @endif
            </div>
            <div class="relative rounded-xl shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input id="password"
                       type="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder="••••••••"
                       class="block w-full rounded-xl border-slate-300 pl-10 pr-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-600 font-medium" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500 h-4 w-4" name="remember">
                <span class="ms-2 text-xs font-medium text-slate-600">Remember this device</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full btn-emerald py-3 text-sm font-bold shadow-md shadow-emerald-900/10 hover:shadow-lg transition-all flex items-center justify-center gap-2">
                <span>Sign In</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </form>

    {{-- Switch to Register --}}
    <div class="mt-6 pt-6 border-t border-slate-100 text-center">
        <p class="text-xs text-slate-600">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-bold text-emerald-700 hover:text-emerald-800 ml-1">
                Create an account &rarr;
            </a>
        </p>
    </div>
</x-guest-layout>
