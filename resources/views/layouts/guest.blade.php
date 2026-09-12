<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Account' }} — {{ config('app.name', 'Huddle') }}</title>

        <!-- Favicons -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon.png') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .auth-brand-panel {
                background: radial-gradient(circle at 10% 20%, rgba(16, 185, 129, 0.18) 0%, transparent 40%),
                            radial-gradient(circle at 90% 80%, rgba(5, 150, 105, 0.15) 0%, transparent 45%),
                            linear-gradient(135deg, #090e1a 0%, #0d1527 50%, #090e1a 100%);
            }
        </style>
    </head>
    <body class="h-full font-sans antialiased bg-slate-50 text-slate-900">
        <div class="min-h-screen flex flex-col lg:flex-row">
            
            {{-- Left Side: Premium Brand Showcase (Desktop only) --}}
            <div class="auth-brand-panel relative hidden lg:flex lg:w-5/12 xl:w-1/2 flex-col justify-between p-12 xl:p-16 text-white border-r border-slate-800 overflow-hidden">
                <div class="absolute -right-20 -top-20 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

                {{-- Top Brand Header --}}
                <div class="relative z-10">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-950/40 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xl font-bold text-white tracking-tight leading-tight">Huddle</span>
                            <span class="text-[11px] font-semibold text-emerald-400 uppercase tracking-wider">Events &amp; Community</span>
                        </div>
                    </a>
                </div>

                {{-- Middle Showcase Text --}}
                <div class="relative z-10 my-auto py-12 max-w-lg">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs font-semibold mb-6">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Verified Event Marketplace
                    </div>
                    <h1 class="text-3xl xl:text-4xl font-bold text-white tracking-tight leading-tight">
                        Your Gateway to Unforgettable Live Experiences.
                    </h1>
                    <p class="mt-4 text-slate-300 text-base leading-relaxed">
                        Discover trending concerts, tech summits, food festivals, and local workshops. Book securely and check in seamlessly.
                    </p>

                    {{-- Feature Bullets --}}
                    <div class="mt-8 space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-300 shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white">Curated Platform Events</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Explore authentic, verified listings from premier organizers across top cities.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-300 shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white">Instant QR &amp; Secure Stripe Payments</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Zero waiting time. Digital tickets issued to your account and email immediately.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bottom Social Proof / Testimonial --}}
                <div class="relative z-10 pt-6 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                    <div class="flex items-center gap-2">
                        <div class="flex -space-x-2">
                            <img class="w-7 h-7 rounded-full border-2 border-slate-900 object-cover" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt="Attendee">
                            <img class="w-7 h-7 rounded-full border-2 border-slate-900 object-cover" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80" alt="Attendee">
                            <img class="w-7 h-7 rounded-full border-2 border-slate-900 object-cover" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&q=80" alt="Attendee">
                        </div>
                        <span class="text-slate-300 font-medium">Joined by 10,000+ community attendees</span>
                    </div>
                    <a href="{{ route('home') }}" class="text-emerald-400 hover:text-emerald-300 transition-colors flex items-center gap-1 font-semibold">
                        &larr; Back to Site
                    </a>
                </div>
            </div>

            {{-- Right Side: Clean Modern Form Canvas --}}
            <div class="flex-1 flex flex-col justify-center px-4 sm:px-6 md:px-8 py-10 sm:py-16 bg-slate-50">
                <div class="sm:mx-auto sm:w-full sm:max-w-md">
                    
                    {{-- Mobile-only Brand Header --}}
                    <div class="lg:hidden text-center mb-6">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-md text-white">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                            </div>
                            <span class="text-2xl font-bold text-slate-900">Huddle</span>
                        </a>
                    </div>

                    {{-- Form Container Card --}}
                    <div class="bg-white py-8 px-6 sm:px-10 rounded-2xl border border-slate-200/90 shadow-xl shadow-slate-200/60">
                        {{ $slot }}
                    </div>

                    <div class="mt-6 text-center text-xs text-slate-500">
                        Protected by 256-bit SSL encryption &bull; <a href="{{ route('about') }}" class="text-emerald-700 hover:underline">Learn more about Huddle</a>
                    </div>
                </div>
            </div>

        </div>
    </body>
</html>
