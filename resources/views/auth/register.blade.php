<x-guest-layout title="Create Account">
    {{-- Form Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold tracking-tight text-slate-950">Create your account</h2>
        <p class="text-sm text-slate-500 mt-1">Get instant access to curated events, ticket booking, and community updates.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                Full Name
            </label>
            <div class="relative rounded-xl shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <input id="name"
                       type="text"
                       name="name"
                       value="{{ old('name') }}"
                       required
                       autofocus
                       autocomplete="name"
                       placeholder="Alex Morgan"
                       class="block w-full rounded-xl border-slate-300 pl-10 pr-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1.5 text-xs text-red-600 font-medium" />
        </div>

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
                       autocomplete="username"
                       placeholder="alex@example.com"
                       class="block w-full rounded-xl border-slate-300 pl-10 pr-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-600 font-medium" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                Password
            </label>
            <div class="relative rounded-xl shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input id="password"
                       type="password"
                       name="password"
                       required
                       autocomplete="new-password"
                       placeholder="At least 8 characters"
                       class="block w-full rounded-xl border-slate-300 pl-10 pr-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-600 font-medium" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                Confirm Password
            </label>
            <div class="relative rounded-xl shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <input id="password_confirmation"
                       type="password"
                       name="password_confirmation"
                       required
                       autocomplete="new-password"
                       placeholder="Repeat password"
                       class="block w-full rounded-xl border-slate-300 pl-10 pr-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-xs text-red-600 font-medium" />
        </div>

        <!-- Host Reassurance Pill -->
        <div class="p-3 bg-emerald-50 border border-emerald-200/80 rounded-xl text-xs text-emerald-900 flex items-start gap-2.5">
            <span class="text-base leading-none">💡</span>
            <p><strong>Are you an event host?</strong> You can activate your vendor profile immediately after sign up to publish events and sell tickets.</p>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full btn-emerald py-3 text-sm font-bold shadow-md shadow-emerald-900/10 hover:shadow-lg transition-all flex items-center justify-center gap-2">
                <span>Create Account</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </form>

    {{-- Switch to Login --}}
    <div class="mt-6 pt-6 border-t border-slate-100 text-center">
        <p class="text-xs text-slate-600">
            Already have an account?
            <a href="{{ route('login') }}" class="font-bold text-emerald-700 hover:text-emerald-800 ml-1">
                Sign in &rarr;
            </a>
        </p>
    </div>
</x-guest-layout>
