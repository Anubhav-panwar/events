<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'CRM Portal' }} — {{ config('app.name', 'Huddle') }}</title>

        <!-- Favicons -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon.png') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')

        <!-- Embedded Fallback Styles for Shared Hosting / cPanel (Immune to Build Caching) -->
        <style>
            .crm-sidebar {
                background-color: #090e1a !important;
                border-right: 1px solid #1e293b !important;
                color: #cbd5e1 !important;
                width: 18rem !important;
                min-width: 18rem !important;
                flex-shrink: 0 !important;
            }
            @media (min-width: 1024px) {
                .crm-sidebar {
                    position: static !important;
                    transform: none !important;
                }
            }
            .crm-sidebar-header {
                height: 4rem !important;
                background-color: #0c1424 !important;
                border-bottom: 1px solid #1e293b !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
            }
            .crm-sidebar .bg-slate-900 {
                background-color: #0c1424 !important;
                border-color: #1e293b !important;
            }
            .crm-sidebar .border-slate-800 {
                border-color: #1e293b !important;
            }
            .crm-sidebar .text-slate-400 {
                color: #94a3b8 !important;
            }
            .crm-sidebar .text-slate-500 {
                color: #64748b !important;
            }
            .crm-nav-item {
                display: flex !important;
                align-items: center !important;
                gap: 0.75rem !important;
                padding: 0.625rem 0.875rem !important;
                border-radius: 0.75rem !important;
                font-size: 0.875rem !important;
                font-weight: 500 !important;
                color: #94a3b8 !important;
                transition: all 0.2s ease !important;
                text-decoration: none !important;
            }
            .crm-nav-item:hover {
                color: #f1f5f9 !important;
                background-color: rgba(30, 41, 59, 0.7) !important;
            }
            .crm-nav-item-active {
                color: #34d399 !important;
                background-color: rgba(16, 185, 129, 0.12) !important;
                border: 1px solid rgba(16, 185, 129, 0.3) !important;
                font-weight: 600 !important;
            }
            .crm-header {
                height: 4rem !important;
                background-color: #ffffff !important;
                border-bottom: 1px solid #e2e8f0 !important;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
            }
            .crm-card {
                background-color: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 0.875rem !important;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04) !important;
            }
            /* Dark banner fallback */
            .crm-banner,
            .bg-gradient-to-r.from-slate-950,
            .bg-gradient-to-r.from-slate-900 {
                background: linear-gradient(135deg, #090e1a 0%, #131c31 50%, #090e1a 100%) !important;
                background-color: #090e1a !important;
                border: 1px solid #1e293b !important;
                color: #ffffff !important;
                border-radius: 1rem !important;
            }
            .crm-banner h1, .crm-banner h2, .crm-banner h3,
            .bg-gradient-to-r.from-slate-950 h1,
            .bg-gradient-to-r.from-slate-900 h1 {
                color: #ffffff !important;
            }
            .crm-banner p,
            .bg-gradient-to-r.from-slate-950 p,
            .bg-gradient-to-r.from-slate-900 p {
                color: #cbd5e1 !important;
            }
            /* Surface class fallback */
            .surface {
                background-color: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 0.75rem !important;
                box-shadow: 0 1px 3px 0 rgba(0,0,0,0.04) !important;
            }
            /* Button fallbacks */
            .btn-emerald {
                background-color: #059669 !important;
                color: #ffffff !important;
                font-weight: 700 !important;
                border-radius: 0.5rem !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0.5rem 1rem !important;
                text-decoration: none !important;
                transition: background-color 0.2s !important;
            }
            .btn-emerald:hover {
                background-color: #047857 !important;
            }
            .btn-primary {
                background-color: #0f172a !important;
                color: #ffffff !important;
                font-weight: 700 !important;
                border-radius: 0.5rem !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0.5rem 1rem !important;
                text-decoration: none !important;
            }
            .btn-primary:hover {
                background-color: #1e293b !important;
            }
            .btn-secondary {
                background-color: #ffffff !important;
                color: #334155 !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 0.5rem !important;
                font-weight: 600 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0.5rem 1rem !important;
                text-decoration: none !important;
            }
            .btn-secondary:hover {
                background-color: #f8fafc !important;
            }
            .badge {
                display: inline-flex !important;
                align-items: center !important;
                padding: 0.25rem 0.625rem !important;
                border-radius: 9999px !important;
                font-size: 0.75rem !important;
                font-weight: 700 !important;
            }
        </style>
    </head>
    <body class="h-full bg-slate-50 text-slate-900 font-sans antialiased" x-data="{ mobileSidebarOpen: false }">
        @php
            $user = auth()->user();
            $role = $role ?? ($user?->isVendor() ? 'vendor' : ($user?->isAdmin() ? 'admin' : 'user'));
            $vendor = $user?->vendorProfile;
            $hasVendorDashboardRoute = \Illuminate\Support\Facades\Route::has('vendor.dashboard');
            $hasVendorEventsCreateRoute = \Illuminate\Support\Facades\Route::has('vendor.events.create');
            $hasVendorOrdersRoute = \Illuminate\Support\Facades\Route::has('vendor.orders.index');
            $hasVendorCheckinRoute = \Illuminate\Support\Facades\Route::has('vendor.checkin.form');
            $hasVendorProfileRoute = \Illuminate\Support\Facades\Route::has('vendor.profile.edit');
            $hasAdminDashboardRoute = \Illuminate\Support\Facades\Route::has('admin.dashboard');
            $hasHomeRoute = \Illuminate\Support\Facades\Route::has('home');
        @endphp

        <div class="min-h-screen flex bg-slate-50">

            {{-- Mobile Sidebar Backdrop --}}
            <div x-show="mobileSidebarOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-40 bg-slate-950/80 backdrop-blur-sm lg:hidden"
                 @click="mobileSidebarOpen = false"
                 style="display: none;"></div>

            {{-- Sidebar Component (Desktop Pinned & Mobile Drawer) --}}
            <aside class="crm-sidebar fixed inset-y-0 left-0 z-50 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0"
                   :class="mobileSidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0'">

                {{-- Sidebar Top: Brand & Portal Badge --}}
                <div class="crm-sidebar-header flex items-center justify-between px-6">
                    <a href="{{ $hasHomeRoute ? route('home') : url('/') }}" class="flex items-center gap-2.5 group">
                        <div class="w-9 h-9 bg-gradient-to-br from-emerald-500 via-teal-500 to-emerald-700 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-950/30 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-base font-bold text-white tracking-tight leading-none group-hover:text-emerald-400 transition-colors">Huddle CRM</span>
                            <span class="text-[10px] font-semibold text-emerald-400 uppercase tracking-wider mt-0.5">
                                {{ $role === 'admin' ? 'Admin Control' : 'Vendor Portal' }}
                            </span>
                        </div>
                    </a>
                    <button @click="mobileSidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Sidebar Vendor Summary Card (if Vendor) --}}
                @if($role === 'vendor' && $vendor)
                    <div class="p-4 mx-4 mt-4 rounded-xl bg-slate-900 border border-slate-800 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-base shrink-0 shadow">
                            {{ strtoupper(substr($vendor->business_name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-white truncate">{{ $vendor->business_name }}</p>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span class="text-[11px] text-slate-400 truncate">{{ $vendor->city ?: 'Vendor Active' }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Sidebar Navigation Menu --}}
                <div class="flex-1 overflow-y-auto px-4 py-4 space-y-6 hide-scrollbar">
                    @if($role === 'admin')
                        {{-- Admin Navigation --}}
                        <div>
                            <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Platform Management</p>
                            @php
                                $adminTab = request()->query('tab', 'overview');
                            @endphp
                            <nav class="space-y-1">
                                <a href="{{ route('admin.dashboard', ['tab' => 'overview']) }}"
                                   class="crm-nav-item {{ ($adminTab === 'overview') ? 'crm-nav-item-active' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                                    <span>Admin Overview</span>
                                </a>
                                <a href="{{ route('admin.dashboard', ['tab' => 'events']) }}"
                                   class="crm-nav-item {{ ($adminTab === 'events') ? 'crm-nav-item-active' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Platform Events</span>
                                    <span class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded-full {{ ($adminTab === 'events') ? 'bg-emerald-500/30 text-emerald-300' : 'bg-slate-800 text-slate-400' }}">{{ \App\Models\Event::count() }}</span>
                                </a>
                                <a href="{{ route('admin.dashboard', ['tab' => 'vendors']) }}"
                                   class="crm-nav-item {{ ($adminTab === 'vendors') ? 'crm-nav-item-active' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span>Vendors Directory</span>
                                    <span class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded-full {{ ($adminTab === 'vendors') ? 'bg-emerald-500/30 text-emerald-300' : 'bg-slate-800 text-slate-400' }}">{{ \App\Models\VendorProfile::count() }}</span>
                                </a>
                                <a href="{{ route('admin.dashboard', ['tab' => 'financials']) }}"
                                   class="crm-nav-item {{ ($adminTab === 'financials') ? 'crm-nav-item-active' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Financial Volume</span>
                                </a>
                            </nav>
                        </div>
                    @else
                        {{-- Vendor Navigation --}}
                        <div>
                            <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Main Menu</p>
                            <nav class="space-y-1">
                                <a href="{{ $hasVendorDashboardRoute ? route('vendor.dashboard') : url('/vendor/dashboard') }}"
                                   class="crm-nav-item {{ request()->routeIs('vendor.dashboard') ? 'crm-nav-item-active' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    Dashboard
                                </a>
                                <a href="{{ $hasVendorEventsCreateRoute ? route('vendor.events.create') : url('/vendor/events/create') }}"
                                   class="crm-nav-item {{ request()->routeIs('vendor.events.create') ? 'crm-nav-item-active' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Create Event
                                </a>
                                <a href="{{ $hasVendorOrdersRoute ? route('vendor.orders.index') : url('/vendor/orders') }}"
                                   class="crm-nav-item {{ request()->routeIs('vendor.orders.*') ? 'crm-nav-item-active' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                    Ticket Orders
                                </a>
                                <a href="{{ $hasVendorCheckinRoute ? route('vendor.checkin.form') : url('/vendor/checkin') }}"
                                   class="crm-nav-item {{ request()->routeIs('vendor.checkin.*') ? 'crm-nav-item-active' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    QR Ticket Check-In
                                </a>
                            </nav>
                        </div>

                        <div>
                            <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Settings</p>
                            <nav class="space-y-1">
                                <a href="{{ $hasVendorProfileRoute ? route('vendor.profile.edit') : url('/vendor/profile') }}"
                                   class="crm-nav-item {{ request()->routeIs('vendor.profile.*') ? 'crm-nav-item-active' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Business Profile
                                </a>
                            </nav>
                        </div>
                    @endif

                    {{-- Single Back to Website Link as Requested --}}
                    <div>
                        <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Main Site</p>
                        <nav class="space-y-1">
                            <a href="{{ $hasHomeRoute ? route('home') : url('/') }}"
                               class="crm-nav-item text-slate-400 hover:text-emerald-400 group">
                                <svg class="w-5 h-5 text-slate-500 group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span>Back to Main Website</span>
                            </a>
                        </nav>
                    </div>
                </div>

                {{-- Sidebar Footer: User Details & Logout --}}
                <div class="p-4 border-t border-slate-800 bg-slate-900/80">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-white truncate">{{ $user?->name }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ $user?->email }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Logout" class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-slate-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            {{-- Main Content Canvas --}}
            <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-slate-50">

                {{-- Top CRM Header Bar --}}
                <header class="crm-header flex items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        {{-- Mobile Menu Trigger --}}
                        <button type="button"
                                @click="mobileSidebarOpen = true"
                                class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>

                        {{-- Breadcrumb / Title Slot --}}
                        <div>
                            @isset($breadcrumb)
                                {{ $breadcrumb }}
                            @else
                                <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
                                    <a href="{{ $hasHomeRoute ? route('home') : url('/') }}" class="hover:text-slate-800">Home</a>
                                    <span>/</span>
                                    <span class="text-slate-900 font-bold capitalize">{{ $role }} CRM</span>
                                </div>
                            @endisset
                        </div>
                    </div>

                    {{-- Top Bar Quick Actions --}}
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        @if($role === 'vendor')
                            @if($hasVendorCheckinRoute)
                                <a href="{{ route('vendor.checkin.form') }}" class="btn-secondary text-xs py-2 px-3 hidden sm:inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    Scan Ticket
                                </a>
                            @endif
                            @if($hasVendorEventsCreateRoute)
                                <a href="{{ route('vendor.events.create') }}" class="btn-emerald text-xs py-2 px-3.5 inline-flex items-center gap-1.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    + New Event
                                </a>
                            @endif
                        @elseif($role === 'admin')
                            <span class="px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full text-xs font-bold flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Super Administrator
                            </span>
                        @endif

                        {{-- Website Link --}}
                        <a href="{{ $hasHomeRoute ? route('home') : url('/') }}" class="text-xs font-semibold text-slate-600 hover:text-emerald-700 inline-flex items-center gap-1.5 border-l border-slate-200 pl-3">
                            <span>Main Website</span>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </header>

                {{-- CRM Main Body --}}
                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    {{-- Flash Messages --}}
                    @if (session('status') || session('success') || (isset($errors) && $errors->any()))
                        <div class="mb-6 space-y-2 max-w-4xl" id="crmToastContainer">
                            @if (session('status') || session('success'))
                                <div class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl p-4 shadow-sm" id="crmSuccessToast">
                                    <div class="w-7 h-7 bg-emerald-100 rounded-full flex items-center justify-center shrink-0 text-emerald-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-emerald-950">Success</p>
                                        <p class="text-xs text-emerald-800 mt-0.5">{{ session('status') ?: session('success') }}</p>
                                    </div>
                                    <button onclick="document.getElementById('crmSuccessToast').remove()" class="text-emerald-500 hover:text-emerald-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endif

                            @if (isset($errors) && $errors->any())
                                <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-900 rounded-xl p-4 shadow-sm" id="crmErrorToast">
                                    <div class="w-7 h-7 bg-red-100 rounded-full flex items-center justify-center shrink-0 text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-red-950">Action required</p>
                                        <ul class="text-xs text-red-800 list-disc list-inside mt-1 space-y-0.5">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <button onclick="document.getElementById('crmErrorToast').remove()" class="text-red-500 hover:text-red-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
