<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Huddle') }} — Discover &amp; Book Events</title>
        <meta name="description" content="Discover local events, book tickets, and support great organizers near you.">
        @stack('meta')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        <div class="app-shell pt-16">
            @include('layouts.navigation')

            <!-- Flash Messages -->
            @if (session('status') || session('success') || $errors->any())
                <div class="fixed bottom-6 right-6 z-[100] space-y-2 max-w-sm" id="toastContainer">
                    @if (session('status') || session('success'))
                        <div class="flex items-start gap-3 bg-white border border-emerald-200 text-emerald-800 rounded-2xl shadow-xl p-4 animate-float-soft" id="successToast">
                            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold">Success</p>
                                <p class="text-sm">{{ session('status') ?: session('success') }}</p>
                            </div>
                            <button onclick="document.getElementById('successToast').remove()" class="ml-auto text-emerald-400 hover:text-emerald-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="flex items-start gap-3 bg-white border border-red-200 text-red-800 rounded-2xl shadow-xl p-4" id="errorToast">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold">Please fix the following:</p>
                                <ul class="text-sm list-disc list-inside mt-1 space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <button onclick="document.getElementById('errorToast').remove()" class="ml-auto text-red-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endif
                </div>
                <script>
                    setTimeout(() => {
                        const toasts = document.querySelectorAll('#successToast, #errorToast');
                        toasts.forEach(t => { if (t) t.style.opacity = '0'; setTimeout(() => t?.remove(), 500); });
                    }, 5000);
                </script>
            @endif

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white/85 backdrop-blur-md border-b border-slate-200/80 shadow-sm">
                    <div class="app-content py-6">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="min-h-screen">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="bg-slate-900 text-slate-400 pt-14 pb-8 mt-20">
                <div class="app-content">
                    <div class="grid grid-cols-1 gap-10 md:grid-cols-4 mb-12">
                        <div class="md:col-span-2">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 via-teal-500 to-sky-500 rounded-lg flex items-center justify-center shadow-md">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                                </div>
                                <span class="text-xl font-bold text-white">Huddle</span>
                            </div>
                            <p class="text-sm leading-relaxed max-w-xs">Modern event discovery and ticketing built for trust, speed, and unforgettable experiences.</p>
                            <div class="flex gap-3 mt-5">
                                <a href="#" class="w-9 h-9 bg-slate-800 rounded-full flex items-center justify-center hover:bg-emerald-600 transition-colors text-slate-300 hover:text-white">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                                </a>
                                <a href="#" class="w-9 h-9 bg-slate-800 rounded-full flex items-center justify-center hover:bg-emerald-600 transition-colors text-slate-300 hover:text-white">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                            </div>
                        </div>
                        <div>
                            <h5 class="font-semibold text-white mb-4">Discover</h5>
                            <ul class="space-y-2 text-sm">
                                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a></li>
                                <li><a href="{{ route('events.index') }}" class="hover:text-white transition-colors">Browse Events</a></li>
                                <li><a href="{{ route('vendors.index') }}" class="hover:text-white transition-colors">Find Vendors</a></li>
                                <li><a href="{{ route('contact') }}" class="hover:text-white transition-colors">Contact</a></li>
                            </ul>
                        </div>
                        <div>
                            <h5 class="font-semibold text-white mb-4">Support</h5>
                            <ul class="space-y-2 text-sm">
                                <li><a href="{{ route('faq') }}" class="hover:text-white transition-colors">Help Center / FAQ</a></li>
                                <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">About Huddle</a></li>
                                <li><a href="{{ route('contact') }}" class="hover:text-white transition-colors">Report an Issue</a></li>
                                @guest
                                    <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Become a Vendor</a></li>
                                @endguest
                            </ul>
                        </div>
                    </div>
                    <div class="border-t border-slate-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                        <p>&copy; {{ date('Y') }} Huddle. All rights reserved.</p>
                        <div class="flex gap-4">
                            <a href="#" class="hover:text-white">Privacy Policy</a>
                            <a href="#" class="hover:text-white">Terms of Service</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        @stack('scripts')
    </body>
</html>
