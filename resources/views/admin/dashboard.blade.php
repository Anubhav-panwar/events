<x-crm-layout title="Admin Dashboard" role="admin">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <span>Admin CRM</span>
            <span>/</span>
            <span class="text-slate-900 font-bold capitalize">
                @if($tab === 'users')
                    User Management
                @elseif($tab === 'categories')
                    Categories Management
                @elseif($tab === 'events')
                    Platform Events
                @elseif($tab === 'vendors')
                    Vendors Directory
                @elseif($tab === 'financials')
                    Financial Volume
                @else
                    Platform Overview
                @endif
            </span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Admin Banner --}}
        <div class="crm-banner bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800">
            <div class="absolute right-0 top-0 -mt-10 -mr-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-bold border border-emerald-500/30">
                            Super Admin Console
                        </span>
                        <span class="text-xs text-slate-400">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                        @if($tab === 'users')
                            User &amp; Access Management
                        @elseif($tab === 'categories')
                            Category &amp; Taxonomy Management
                        @elseif($tab === 'events')
                            Platform Events Management
                        @elseif($tab === 'vendors')
                            Vendors Directory &amp; Moderation
                        @elseif($tab === 'financials')
                            Financial Analytics &amp; Volume
                        @else
                            System Platform Overview
                        @endif
                    </h1>
                    <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                        @if($tab === 'users')
                            Manage customer, vendor, and administrator accounts, assign permissions, reset credentials, or provision new vendor storefronts.
                        @elseif($tab === 'categories')
                            Create and manage event categories and taxonomies used across the marketplace and vendor onboarding.
                        @elseif($tab === 'events')
                            Monitor and manage all {{ $kpis['total_events'] }} events submitted by registered vendors across all categories.
                        @elseif($tab === 'vendors')
                            Oversee approved vendor organizations, business profiles, and marketplace storefronts.
                        @elseif($tab === 'financials')
                            Review gross marketplace transaction volume, ticket receipts, and booking orders.
                        @else
                            Full administrative oversight for marketplace events, vendor verifications, attendee ticket orders, and platform revenue.
                        @endif
                    </p>
                </div>

                {{-- Tab Quick Switcher in Banner --}}
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <a href="{{ route('admin.dashboard', ['tab' => 'overview']) }}"
                       class="text-xs py-2 px-3.5 rounded-lg font-bold transition-all {{ $tab === 'overview' ? 'bg-emerald-500 text-slate-950 shadow-md' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        Overview
                    </a>
                    <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}"
                       class="text-xs py-2 px-3.5 rounded-lg font-bold transition-all {{ $tab === 'users' ? 'bg-emerald-500 text-slate-950 shadow-md' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        Users ({{ $kpis['total_users'] }})
                    </a>
                    <a href="{{ route('admin.dashboard', ['tab' => 'events']) }}"
                       class="text-xs py-2 px-3.5 rounded-lg font-bold transition-all {{ $tab === 'events' ? 'bg-emerald-500 text-slate-950 shadow-md' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        Events ({{ $kpis['total_events'] }})
                    </a>
                    <a href="{{ route('admin.dashboard', ['tab' => 'categories']) }}"
                       class="text-xs py-2 px-3.5 rounded-lg font-bold transition-all {{ $tab === 'categories' ? 'bg-emerald-500 text-slate-950 shadow-md' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        Categories ({{ $kpis['total_categories'] }})
                    </a>
                    <a href="{{ route('admin.dashboard', ['tab' => 'vendors']) }}"
                       class="text-xs py-2 px-3.5 rounded-lg font-bold transition-all {{ $tab === 'vendors' ? 'bg-emerald-500 text-slate-950 shadow-md' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        Vendors ({{ $kpis['total_vendors'] }})
                    </a>
                    <a href="{{ route('admin.dashboard', ['tab' => 'financials']) }}"
                       class="text-xs py-2 px-3.5 rounded-lg font-bold transition-all {{ $tab === 'financials' ? 'bg-emerald-500 text-slate-950 shadow-md' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        Volume (${{ number_format($kpis['revenue'], 0) }})
                    </a>
                </div>
            </div>
        </div>

        {{-- 4 KPI Cards (Always visible at top) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            {{-- Users --}}
            <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}" class="crm-card p-5 hover:border-slate-400 transition-all block group {{ $tab === 'users' ? 'ring-2 ring-emerald-500/20 border-emerald-500' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 group-hover:text-slate-700">Registered Users</span>
                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-950">{{ number_format($kpis['total_users']) }}</span>
                    <span class="text-xs font-semibold text-slate-500">Accounts</span>
                </div>
                <p class="text-xs text-slate-500 mt-2">{{ $kpis['vendor_users'] }} vendors &bull; {{ $kpis['admin_users'] }} admins &rarr;</p>
            </a>

            {{-- Vendors --}}
            <a href="{{ route('admin.dashboard', ['tab' => 'vendors']) }}" class="crm-card p-5 hover:border-emerald-400 transition-all block group {{ $tab === 'vendors' ? 'ring-2 ring-emerald-500/20 border-emerald-500' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 group-hover:text-emerald-700">Vendors</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-950">{{ number_format($kpis['total_vendors']) }}</span>
                    <span class="text-xs font-semibold text-emerald-600">{{ $kpis['approved_vendors'] }} approved</span>
                </div>
                <p class="text-xs text-slate-500 mt-2">Active business accounts &rarr;</p>
            </a>

            {{-- Events --}}
            <a href="{{ route('admin.dashboard', ['tab' => 'events']) }}" class="crm-card p-5 hover:border-teal-400 transition-all block group {{ $tab === 'events' ? 'ring-2 ring-teal-500/20 border-teal-500' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 group-hover:text-teal-700">Platform Events</span>
                    <div class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-950">{{ number_format($kpis['total_events']) }}</span>
                    <span class="text-xs font-semibold text-teal-600">{{ $kpis['published_events'] }} published</span>
                </div>
                <p class="text-xs text-slate-500 mt-2">Active marketplace listings &rarr;</p>
            </a>

            {{-- Revenue --}}
            <a href="{{ route('admin.dashboard', ['tab' => 'financials']) }}" class="crm-card p-5 hover:border-purple-400 transition-all block group {{ $tab === 'financials' ? 'ring-2 ring-purple-500/20 border-purple-500' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 group-hover:text-purple-700">Total Volume</span>
                    <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-950">${{ number_format($kpis['revenue'], 2) }}</span>
                    <span class="text-xs font-bold text-slate-500">USD</span>
                </div>
                <p class="text-xs text-slate-500 mt-2">From {{ $kpis['paid_orders'] }} paid bookings &rarr;</p>
            </a>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 1: OVERVIEW --}}
        {{-- ========================================================================= --}}
        @if($tab === 'overview')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Recent Events Table --}}
                <div class="crm-card p-6 lg:col-span-2 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-base font-bold text-slate-950">Recent Platform Events</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Latest listings created by vendors across the platform.</p>
                        </div>
                        <a href="{{ route('admin.dashboard', ['tab' => 'events']) }}" class="text-xs font-bold text-emerald-700 hover:underline">
                            View All ({{ $kpis['total_events'] }}) &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                                    <th class="pb-3 pr-3">Event</th>
                                    <th class="pb-3 pr-3">Vendor</th>
                                    <th class="pb-3 pr-3">Date</th>
                                    <th class="pb-3 pr-3">Status</th>
                                    <th class="pb-3 text-right">Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($recentEvents as $event)
                                    @php
                                        $img = $event->media->where('type', 'image')->first();
                                        $imgUrl = $img ? $img->url : 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=200&q=80';
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 pr-3">
                                            <div class="flex items-center gap-2.5">
                                                <img src="{{ $imgUrl }}" class="w-9 h-9 rounded-lg object-cover border border-slate-200 shrink-0" alt="">
                                                <a href="{{ route('events.show', $event->slug) }}" target="_blank" class="font-bold text-slate-900 hover:text-emerald-700 transition-colors truncate max-w-[200px] block">
                                                    {{ $event->title }}
                                                </a>
                                            </div>
                                        </td>
                                        <td class="py-3 pr-3 text-slate-600 font-medium truncate max-w-[120px]">
                                            {{ $event->vendorProfile?->business_name ?? '—' }}
                                        </td>
                                        <td class="py-3 pr-3 text-slate-500 whitespace-nowrap">
                                            {{ $event->event_date?->format('M j, Y') }}
                                        </td>
                                        <td class="py-3 pr-3 whitespace-nowrap">
                                            @if($event->status === 'published')
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-bold border border-emerald-200 text-[10px]">
                                                    Live
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 font-bold border border-amber-200 text-[10px]">
                                                    Draft
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-right font-bold text-slate-900 text-xs">
                                            {{ $event->event_type === 'free' ? 'FREE' : '$' . number_format($event->base_price, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400">No events on the platform yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Active Vendors List Card --}}
                <div class="crm-card p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-base font-bold text-slate-950">Active Vendors</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Approved business profiles</p>
                        </div>
                        <a href="{{ route('admin.dashboard', ['tab' => 'vendors']) }}" class="text-xs font-bold text-emerald-700 hover:underline">
                            Directory &rarr;
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($recentVendors as $v)
                            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50 hover:bg-white hover:border-slate-300 transition-all">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-xs shrink-0 shadow-sm">
                                        {{ strtoupper(substr($v->business_name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 truncate">{{ $v->business_name }}</p>
                                        <p class="text-[11px] text-slate-400 truncate">{{ $v->city ?: 'Location not set' }}</p>
                                    </div>
                                </div>
                                @if(\Illuminate\Support\Facades\Route::has('vendors.show') && filled($v->slug))
                                    <a href="{{ route('vendors.show', $v->slug) }}" target="_blank" class="text-slate-400 hover:text-emerald-700 p-1" title="View Storefront">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-6">No vendors registered yet.</p>
                        @endforelse
                    </div>
                </div>
        {{-- ========================================================================= --}}
        {{-- TAB 2: USER & ROLE MANAGEMENT --}}
        {{-- ========================================================================= --}}
        @elseif($tab === 'users')
            <div class="crm-card p-6 space-y-6">
                {{-- Header & Create Button --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-slate-950">Platform Users &amp; Roles Directory</h2>
                            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">{{ $allUsers->total() }} Accounts</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Manage permissions, switch user roles between Customer / Vendor / Admin, or provision new accounts.</p>
                    </div>

                    <button type="button" onclick="openCreateUserModal()"
                            class="btn-emerald text-xs font-bold py-2.5 px-4 shadow-sm self-start sm:self-auto inline-flex items-center gap-2 hover:shadow transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span>+ Create User / Vendor</span>
                    </button>
                </div>

                {{-- Role Quick Filters & Search Form --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    {{-- Role Pills --}}
                    <div class="flex flex-wrap items-center gap-1.5">
                        <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}"
                           class="text-xs font-bold py-1.5 px-3 rounded-lg border transition-all {{ $roleFilter === 'all' ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                            All Accounts ({{ $kpis['total_users'] }})
                        </a>
                        <a href="{{ route('admin.dashboard', ['tab' => 'users', 'role' => 'user']) }}"
                           class="text-xs font-bold py-1.5 px-3 rounded-lg border transition-all {{ $roleFilter === 'user' ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                            Customers ({{ $kpis['customer_users'] }})
                        </a>
                        <a href="{{ route('admin.dashboard', ['tab' => 'users', 'role' => 'vendor']) }}"
                           class="text-xs font-bold py-1.5 px-3 rounded-lg border transition-all {{ $roleFilter === 'vendor' ? 'bg-emerald-700 text-white border-emerald-700 shadow-xs' : 'bg-white text-emerald-800 border-emerald-200 hover:bg-emerald-50' }}">
                            Vendors &amp; Hosts ({{ $kpis['vendor_users'] }})
                        </a>
                        <a href="{{ route('admin.dashboard', ['tab' => 'users', 'role' => 'admin']) }}"
                           class="text-xs font-bold py-1.5 px-3 rounded-lg border transition-all {{ $roleFilter === 'admin' ? 'bg-purple-700 text-white border-purple-700 shadow-xs' : 'bg-white text-purple-800 border-purple-200 hover:bg-purple-50' }}">
                            Super Admins ({{ $kpis['admin_users'] }})
                        </a>
                    </div>

                    {{-- Search Form --}}
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="users">
                        @if($roleFilter !== 'all')
                            <input type="hidden" name="role" value="{{ $roleFilter }}">
                        @endif
                        <div class="relative min-w-[220px]">
                            <input type="text" name="q" value="{{ $search }}"
                                   placeholder="Search name, email, vendor..."
                                   class="w-full text-xs rounded-lg border border-slate-200 pl-8 pr-3 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button type="submit" class="btn-secondary text-xs py-1.5 px-3">Search</button>
                        @if($search !== '' || $roleFilter !== 'all')
                            <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium ml-1">Reset</a>
                        @endif
                    </form>
                </div>

                {{-- Users Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                                <th class="pb-3 pr-4">User Details</th>
                                <th class="pb-3 pr-4">Assigned Role &amp; Quick Switch</th>
                                <th class="pb-3 pr-4">Vendor Profile &amp; Storefront</th>
                                <th class="pb-3 pr-4">Joined Date</th>
                                <th class="pb-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($allUsers as $u)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    {{-- User Info --}}
                                    <td class="py-3.5 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs text-white shrink-0 shadow-xs
                                                {{ $u->isAdmin() ? 'bg-gradient-to-br from-purple-500 to-indigo-600' : ($u->isVendor() ? 'bg-gradient-to-br from-emerald-500 to-teal-600' : 'bg-gradient-to-br from-slate-600 to-slate-800') }}">
                                                {{ strtoupper(substr($u->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <p class="font-bold text-slate-900 truncate">{{ $u->name }}</p>
                                                    @if($u->id === auth()->id())
                                                        <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.2 rounded border border-purple-200">You</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[11px] text-slate-500 truncate">{{ $u->email }}</span>
                                                    @if($u->email_verified_at)
                                                        <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200">✓ Verified</span>
                                                    @else
                                                        <span class="text-[9px] font-bold text-amber-700 bg-amber-50 px-1 py-0.2 rounded border border-amber-200">Unverified</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Role & Quick Role Switcher --}}
                                    <td class="py-3.5 pr-4">
                                        <div class="space-y-1.5">
                                            @if($u->isAdmin())
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-800 font-bold border border-purple-200 text-[11px]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span> Super Admin
                                                </span>
                                            @elseif($u->isVendor())
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-bold border border-emerald-200 text-[11px]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Vendor / Host
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold border border-slate-200 text-[11px]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Customer
                                                </span>
                                            @endif

                                            {{-- Quick Role Switch dropdown --}}
                                            @if($u->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.users.role', $u) }}" class="block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="role" onchange="if(confirm('Change role for &quot;{{ addslashes($u->name) }}&quot; to ' + this.options[this.selectedIndex].text + '?')) { this.form.submit(); } else { this.value = '{{ $u->role }}'; }"
                                                            class="text-[10px] font-semibold py-0.5 px-2 rounded-md border border-slate-200 bg-white hover:border-slate-300 text-slate-700 cursor-pointer shadow-2xs focus:ring-1 focus:ring-emerald-500">
                                                        <option value="user" {{ $u->role === 'user' ? 'selected' : '' }}>Switch: Customer</option>
                                                        <option value="vendor" {{ $u->role === 'vendor' ? 'selected' : '' }}>Switch: Vendor</option>
                                                        <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Switch: Super Admin</option>
                                                    </select>
                                                </form>
                                            @else
                                                <span class="text-[10px] text-slate-400 block italic">Current Session</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Vendor Profile Info --}}
                                    <td class="py-3.5 pr-4">
                                        @if($u->isVendor())
                                            @if($u->vendorProfile)
                                                <div class="min-w-0">
                                                    <p class="font-bold text-slate-800 truncate">{{ $u->vendorProfile->business_name }}</p>
                                                    <p class="text-[11px] text-slate-400 truncate">{{ $u->vendorProfile->city ?: 'Location not set' }}</p>
                                                    @if(\Illuminate\Support\Facades\Route::has('vendors.show') && filled($u->vendorProfile->slug))
                                                        <a href="{{ route('vendors.show', $u->vendorProfile->slug) }}" target="_blank"
                                                           class="text-[11px] font-bold text-emerald-700 hover:underline inline-flex items-center gap-1 mt-0.5">
                                                            <span>Storefront</span> &rarr;
                                                        </a>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-amber-600 font-semibold text-[11px]">Auto-provisioning profile</span>
                                            @endif
                                        @elseif($u->isAdmin())
                                            <span class="text-slate-400 font-medium text-[11px]">Admin Control Access</span>
                                        @else
                                            <span class="text-slate-400 font-normal text-[11px]">Regular Attendee</span>
                                        @endif
                                    </td>

                                    {{-- Registered Date --}}
                                    <td class="py-3.5 pr-4 text-slate-500 whitespace-nowrap">
                                        <p class="font-medium text-slate-800">{{ $u->created_at?->format('M j, Y') }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $u->created_at?->diffForHumans() }}</p>
                                    </td>

                                    {{-- Action Buttons --}}
                                    <td class="py-3.5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            {{-- Edit Button --}}
                                            <button type="button"
                                                    onclick="openEditUserModal({{ json_encode([
                                                        'id' => $u->id,
                                                        'name' => $u->name,
                                                        'email' => $u->email,
                                                        'role' => $u->role,
                                                        'business_name' => $u->vendorProfile?->business_name ?? '',
                                                        'city' => $u->vendorProfile?->city ?? '',
                                                        'phone' => $u->vendorProfile?->phone ?? '',
                                                        'is_approved' => (bool) ($u->vendorProfile?->is_approved ?? true),
                                                    ]) }})"
                                                    class="btn-secondary text-[11px] py-1 px-2.5 inline-flex items-center gap-1 hover:bg-slate-50">
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                <span>Edit</span>
                                            </button>

                                            {{-- Delete Button --}}
                                            @if($u->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                                      onsubmit="return confirm('Permanently delete user &quot;{{ addslashes($u->name) }}&quot;? This cannot be undone.');"
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-[11px] py-1 px-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 hover:border-red-300 font-bold transition-colors">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400">
                                        No users found matching your search criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($allUsers->hasPages())
                    <div class="border-t border-slate-100 pt-4">
                        {{ $allUsers->links() }}
                    </div>
                @endif
            </div>

        {{-- ========================================================================= --}}
        {{-- TAB 3: PLATFORM EVENTS FULL MANAGEMENT --}}
        {{-- ========================================================================= --}}
        @elseif($tab === 'events')
            <div class="crm-card p-6 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Platform Events Directory</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Filter, inspect, and moderate all vendor event listings.</p>
                    </div>

                    {{-- Search & Filters Form --}}
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-center gap-2">
                        <input type="hidden" name="tab" value="events">
                        <div class="relative">
                            <input type="text" name="q" value="{{ $search }}" placeholder="Search title, city, vendor..."
                                   class="input-field text-xs py-1.5 px-3 pr-8 w-48 sm:w-64">
                            @if($search)
                                <a href="{{ route('admin.dashboard', ['tab' => 'events']) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">&times;</a>
                            @endif
                        </div>
                        <select name="status" class="input-field text-xs py-1.5 px-3 w-28" onchange="this.form.submit()">
                            <option value="all" @selected($status === 'all')>All Status</option>
                            <option value="published" @selected($status === 'published')>Live Only</option>
                            <option value="draft" @selected($status === 'draft')>Draft Only</option>
                        </select>
                        <select name="category_id" class="input-field text-xs py-1.5 px-3 w-36" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary text-xs py-1.5 px-3.5">Filter</button>
                        @if($search || $status !== 'all' || request('category_id'))
                            <a href="{{ route('admin.dashboard', ['tab' => 'events']) }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium ml-1">Reset</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                                <th class="pb-3 pr-4">Event Details</th>
                                <th class="pb-3 pr-4">Vendor</th>
                                <th class="pb-3 pr-4">Location</th>
                                <th class="pb-3 pr-4">Date &amp; Time</th>
                                <th class="pb-3 pr-4">Price</th>
                                <th class="pb-3 pr-4">Status</th>
                                <th class="pb-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($allEvents as $event)
                                @php
                                    $img = $event->media->where('type', 'image')->first();
                                    $imgUrl = $img ? $img->url : 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=200&q=80';
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3.5 pr-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $imgUrl }}" class="w-11 h-11 rounded-lg object-cover border border-slate-200 shrink-0" alt="">
                                            <div class="min-w-0">
                                                <a href="{{ route('events.show', $event->slug) }}" target="_blank" class="font-bold text-slate-900 hover:text-emerald-700 text-sm truncate max-w-[220px] block">
                                                    {{ $event->title }}
                                                </a>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="inline-flex px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold text-[10px]">
                                                        {{ $event->category?->name ?? 'General' }}
                                                    </span>
                                                    @if($event->media->where('type', 'image')->count() > 1)
                                                        <span class="text-[10px] text-slate-400 font-medium">📷 {{ $event->media->where('type', 'image')->count() }} photos</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 pr-4 font-semibold text-slate-700 truncate max-w-[140px]">
                                        {{ $event->vendorProfile?->business_name ?? '—' }}
                                    </td>
                                    <td class="py-3.5 pr-4 text-slate-600">
                                        <div class="truncate max-w-[130px] font-medium">{{ $event->city ?: 'Online / TBD' }}</div>
                                        <div class="text-[10px] text-slate-400 truncate max-w-[130px]">{{ $event->venue_name }}</div>
                                    </td>
                                    <td class="py-3.5 pr-4 whitespace-nowrap text-slate-600">
                                        <div class="font-medium">{{ $event->event_date?->format('M j, Y') }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $event->start_time }}</div>
                                    </td>
                                    <td class="py-3.5 pr-4 font-bold text-slate-900 whitespace-nowrap">
                                        {{ $event->event_type === 'free' ? 'FREE' : '$' . number_format($event->base_price, 2) }}
                                    </td>
                                    <td class="py-3.5 pr-4 whitespace-nowrap">
                                        @if($event->status === 'published')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-bold border border-emerald-200 text-[11px]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Live
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 font-bold border border-amber-200 text-[11px]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Draft
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 text-right whitespace-nowrap">
                                        <a href="{{ route('events.show', $event->slug) }}" target="_blank"
                                           class="btn-secondary text-[11px] py-1.5 px-2.5 inline-flex items-center gap-1">
                                            <span>View Page</span>
                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">No events matched your search criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($allEvents->hasPages())
                    <div class="border-t border-slate-100 pt-4">
                        {{ $allEvents->links() }}
                    </div>
                @endif
            </div>

        {{-- ========================================================================= --}}
        {{-- TAB: CATEGORIES MANAGEMENT --}}
        {{-- ========================================================================= --}}
        @elseif($tab === 'categories')
            <div class="crm-card p-6 space-y-6">
                {{-- Header & Create Button --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-slate-950">Category &amp; Taxonomy Management</h2>
                            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">{{ $allCategories->total() }} Categories</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Create, edit, or delete event categories. Changes update live filters across the marketplace.</p>
                    </div>

                    <button type="button" onclick="openCreateCategoryModal()"
                            class="btn-emerald text-xs font-bold py-2.5 px-4 shadow-sm self-start sm:self-auto inline-flex items-center gap-2 hover:shadow transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ New Category</span>
                    </button>
                </div>

                {{-- Search bar --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="categories">
                        <div class="relative min-w-[240px]">
                            <input type="text" name="q" value="{{ $search }}"
                                   placeholder="Search category name, slug..."
                                   class="w-full text-xs rounded-lg border border-slate-200 pl-8 pr-3 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button type="submit" class="btn-secondary text-xs py-1.5 px-3">Search</button>
                        @if($search !== '')
                            <a href="{{ route('admin.dashboard', ['tab' => 'categories']) }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium ml-1">Reset</a>
                        @endif
                    </form>

                    <span class="text-xs text-slate-500">Total Taxonomies: <strong class="text-slate-800">{{ $kpis['total_categories'] }}</strong></span>
                </div>

                {{-- Categories Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                                <th class="pb-3 pr-4">Category Name</th>
                                <th class="pb-3 pr-4">Slug Identifier</th>
                                <th class="pb-3 pr-4 text-center">Linked Events</th>
                                <th class="pb-3 pr-4 text-center">Linked Vendors</th>
                                <th class="pb-3 pr-4">Created Date</th>
                                <th class="pb-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($allCategories as $cat)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3.5 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center font-bold text-xs shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 text-sm">{{ $cat->name }}</p>
                                                <p class="text-[10px] text-slate-400">ID #{{ $cat->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        <code class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-700 text-[11px] font-mono">{{ $cat->slug }}</code>
                                    </td>
                                    <td class="py-3.5 pr-4 text-center">
                                        @if($cat->events_count > 0)
                                            <a href="{{ route('admin.dashboard', ['tab' => 'events', 'category_id' => $cat->id]) }}"
                                               class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-bold border border-emerald-200 text-[11px] hover:bg-emerald-100 transition-colors">
                                                {{ $cat->events_count }} events &rarr;
                                            </a>
                                        @else
                                            <span class="text-slate-400 font-medium text-[11px]">0 events</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 pr-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-medium text-[11px]">
                                            {{ $cat->vendor_profiles_count }} vendors
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-slate-500 whitespace-nowrap">
                                        <p class="font-medium text-slate-800">{{ $cat->created_at?->format('M j, Y') ?? '—' }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $cat->created_at?->diffForHumans() }}</p>
                                    </td>
                                    <td class="py-3.5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            {{-- Edit Category Button --}}
                                            <button type="button"
                                                    onclick="openEditCategoryModal({{ json_encode([
                                                        'id' => $cat->id,
                                                        'name' => $cat->name,
                                                        'slug' => $cat->slug,
                                                    ]) }})"
                                                    class="btn-secondary text-[11px] py-1 px-2.5 inline-flex items-center gap-1 hover:bg-slate-50">
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                <span>Edit</span>
                                            </button>

                                            {{-- Delete Category Button --}}
                                            <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}"
                                                  onsubmit="return confirm('Are you sure you want to delete category &quot;{{ addslashes($cat->name) }}&quot;? Any events tagged with it will remain safe and be reset to uncategorized.');"
                                                  class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-[11px] py-1 px-2 rounded-lg text-red-600 hover:text-red-800 hover:bg-red-50 border border-transparent hover:border-red-200 transition-colors inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400">
                                        No categories found. Click "+ New Category" above to create one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($allCategories->hasPages())
                    <div class="border-t border-slate-100 pt-4">
                        {{ $allCategories->links() }}
                    </div>
                @endif
            </div>

        {{-- ========================================================================= --}}
        {{-- TAB 4: VENDORS DIRECTORY FULL MANAGEMENT --}}
        {{-- ========================================================================= --}}
        @elseif($tab === 'vendors')
            <div class="crm-card p-6 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Vendors Directory &amp; Business Profiles</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Manage approved organizations and merchant storefronts.</p>
                    </div>

                    {{-- Search Form --}}
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="vendors">
                        <div class="relative">
                            <input type="text" name="q" value="{{ $search }}" placeholder="Search business or city..."
                                   class="input-field text-xs py-1.5 px-3 pr-8 w-60">
                            @if($search)
                                <a href="{{ route('admin.dashboard', ['tab' => 'vendors']) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">&times;</a>
                            @endif
                        </div>
                        <button type="submit" class="btn-primary text-xs py-1.5 px-3.5">Search</button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                                <th class="pb-3 pr-4">Business Organization</th>
                                <th class="pb-3 pr-4">Owner Account</th>
                                <th class="pb-3 pr-4">City / Region</th>
                                <th class="pb-3 pr-4">Status</th>
                                <th class="pb-3 text-right">Storefront</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($allVendors as $vendor)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3.5 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-sm">
                                                {{ strtoupper(substr($vendor->business_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 text-sm">{{ $vendor->business_name }}</p>
                                                <p class="text-[11px] text-slate-400">slug: {{ $vendor->slug }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        <p class="font-semibold text-slate-800">{{ $vendor->user?->name ?? '—' }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $vendor->user?->email ?? '—' }}</p>
                                    </td>
                                    <td class="py-3.5 pr-4 text-slate-600 font-medium">
                                        {{ $vendor->city ?: 'Location not set' }}{{ $vendor->country ? ', ' . $vendor->country : '' }}
                                    </td>
                                    <td class="py-3.5 pr-4 whitespace-nowrap">
                                        @if($vendor->is_approved)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-bold border border-emerald-200 text-[11px]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 font-bold border border-amber-200 text-[11px]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 text-right whitespace-nowrap">
                                        @if(\Illuminate\Support\Facades\Route::has('vendors.show') && filled($vendor->slug))
                                            <a href="{{ route('vendors.show', $vendor->slug) }}" target="_blank"
                                               class="btn-secondary text-[11px] py-1.5 px-2.5 inline-flex items-center gap-1">
                                                <span>View Storefront</span>
                                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400">No vendors found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($allVendors->hasPages())
                    <div class="border-t border-slate-100 pt-4">
                        {{ $allVendors->links() }}
                    </div>
                @endif
            </div>

        {{-- ========================================================================= --}}
        {{-- TAB 4: FINANCIAL VOLUME & RECENT ORDERS --}}
        {{-- ========================================================================= --}}
        @elseif($tab === 'financials')
            <div class="crm-card p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Platform Financial Analytics</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Ticket transactions, gross platform receipts, and attendee checkout logs.</p>
                    </div>
                </div>

                {{-- Financial KPI Highlights --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-xs font-bold text-slate-500 uppercase">Gross Platform Volume</span>
                        <p class="text-2xl font-bold text-slate-950 mt-1">${{ number_format($kpis['revenue'], 2) }}</p>
                        <p class="text-[11px] text-emerald-600 font-semibold mt-1">Paid ticket bookings</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-xs font-bold text-slate-500 uppercase">Paid Orders Count</span>
                        <p class="text-2xl font-bold text-slate-950 mt-1">{{ number_format($kpis['paid_orders']) }}</p>
                        <p class="text-[11px] text-slate-500 mt-1">Completed checkouts</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-xs font-bold text-slate-500 uppercase">Average Order Value</span>
                        <p class="text-2xl font-bold text-slate-950 mt-1">
                            ${{ $kpis['paid_orders'] > 0 ? number_format($kpis['revenue'] / $kpis['paid_orders'], 2) : '0.00' }}
                        </p>
                        <p class="text-[11px] text-slate-500 mt-1">Per transaction average</p>
                    </div>
                </div>

                {{-- Orders Table --}}
                <div class="overflow-x-auto mt-4">
                    <table class="min-w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                                <th class="pb-3 pr-4">Order ID</th>
                                <th class="pb-3 pr-4">Event</th>
                                <th class="pb-3 pr-4">Customer</th>
                                <th class="pb-3 pr-4">Total Amount</th>
                                <th class="pb-3 pr-4">Date</th>
                                <th class="pb-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($allOrders as $order)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3.5 pr-4 font-mono font-bold text-slate-800">
                                        #{{ $order->id }}
                                    </td>
                                    <td class="py-3.5 pr-4 font-medium text-slate-900 truncate max-w-[200px]">
                                        {{ $order->event?->title ?? 'Platform Event' }}
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        <p class="font-semibold text-slate-800">{{ $order->user?->name ?? 'Guest Buyer' }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $order->user?->email ?? '—' }}</p>
                                    </td>
                                    <td class="py-3.5 pr-4 font-bold text-slate-950 text-sm">
                                        ${{ number_format($order->total, 2) }}
                                    </td>
                                    <td class="py-3.5 pr-4 text-slate-500 whitespace-nowrap">
                                        {{ $order->created_at?->format('M j, Y h:i A') }}
                                    </td>
                                    <td class="py-3.5 text-right whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-bold border border-emerald-200 text-[11px]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400">No checkout transactions recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($allOrders->hasPages())
                    <div class="border-t border-slate-100 pt-4">
                        {{ $allOrders->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 1: CREATE NEW USER OR VENDOR --}}
    {{-- ========================================================================= --}}
    <div id="createUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 sm:p-8 space-y-5 border border-slate-200 relative">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Provision New Account</h3>
                        <p class="text-xs text-slate-500">Create a customer, event vendor, or administrative member.</p>
                    </div>
                </div>
                <button type="button" onclick="closeCreateUserModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf

                {{-- Role Selection Radio Pills --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Select Account Role *</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:border-slate-400 text-center transition-all has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-500/20">
                            <input type="radio" name="role" value="user" checked onchange="toggleCreateVendorFields(this.value)" class="sr-only">
                            <span class="text-xs font-bold text-slate-900">Customer</span>
                            <span class="text-[10px] text-slate-500 mt-0.5">Attendee / Buyer</span>
                        </label>
                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:border-slate-400 text-center transition-all has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-500/20">
                            <input type="radio" name="role" value="vendor" onchange="toggleCreateVendorFields(this.value)" class="sr-only">
                            <span class="text-xs font-bold text-slate-900">Vendor / Host</span>
                            <span class="text-[10px] text-slate-500 mt-0.5">Event Organizer</span>
                        </label>
                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:border-slate-400 text-center transition-all has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50/50 has-[:checked]:ring-2 has-[:checked]:ring-purple-500/20">
                            <input type="radio" name="role" value="admin" onchange="toggleCreateVendorFields(this.value)" class="sr-only">
                            <span class="text-xs font-bold text-slate-900">Super Admin</span>
                            <span class="text-[10px] text-slate-500 mt-0.5">Full Console Access</span>
                        </label>
                    </div>
                </div>

                {{-- Name & Email --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Sarah Jenkins"
                               class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Address *</label>
                        <input type="email" name="email" required placeholder="sarah@example.com"
                               class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                {{-- Password & Confirm --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Password (Min 8 chars) *</label>
                        <input type="password" name="password" required minlength="8" placeholder="••••••••"
                               class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required minlength="8" placeholder="••••••••"
                               class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                {{-- Dynamic Vendor Profile Fields (Visible when role is vendor) --}}
                <div id="createVendorFields" class="hidden p-4 rounded-xl bg-emerald-50/60 border border-emerald-200 space-y-3">
                    <p class="text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Vendor Organization Profile</span>
                    </p>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Business / Brand Name</label>
                        <input type="text" name="business_name" placeholder="Leave empty to use Full Name"
                               class="w-full text-xs rounded-lg border border-slate-300 px-3 py-1.5 bg-white">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">City</label>
                            <input type="text" name="city" placeholder="e.g. San Francisco"
                                   class="w-full text-xs rounded-lg border border-slate-300 px-3 py-1.5 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Contact Phone</label>
                            <input type="text" name="phone" placeholder="+1-555-0123"
                                   class="w-full text-xs rounded-lg border border-slate-300 px-3 py-1.5 bg-white">
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_approved" id="create_is_approved" value="1" checked
                               class="rounded text-emerald-600 focus:ring-emerald-500">
                        <label for="create_is_approved" class="text-xs font-medium text-slate-700">Auto-approve vendor storefront immediately</label>
                    </div>
                </div>

                {{-- Mark email as verified toggle --}}
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="mark_verified" id="create_mark_verified" value="1" checked
                           class="rounded text-emerald-600 focus:ring-emerald-500">
                    <label for="create_mark_verified" class="text-xs font-medium text-slate-700">Mark account email as verified (instant login allowed)</label>
                </div>

                {{-- Submit Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeCreateUserModal()" class="btn-secondary text-xs py-2 px-4">Cancel</button>
                    <button type="submit" class="btn-emerald text-xs font-bold py-2 px-5 shadow-sm">Provision Account</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 2: EDIT USER & ROLE --}}
    {{-- ========================================================================= --}}
    <div id="editUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 sm:p-8 space-y-5 border border-slate-200 relative">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900" id="editModalHeading">Edit User Account</h3>
                        <p class="text-xs text-slate-500">Update identity credentials, assigned role, and vendor credentials.</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditUserModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" id="editUserForm" action="" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Name & Email --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                        <input type="text" name="name" id="edit_name" required
                               class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Address *</label>
                        <input type="email" name="email" id="edit_email" required
                               class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                {{-- Role Selection --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Account Role &amp; Permissions *</label>
                    <select name="role" id="edit_role" onchange="toggleEditVendorFields(this.value)"
                            class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 font-semibold bg-white focus:ring-1 focus:ring-emerald-500">
                        <option value="user">Customer (Attendee / Regular User)</option>
                        <option value="vendor">Vendor / Host (Event Organizer with Portal Access)</option>
                        <option value="admin">Super Admin (Full Administrative Console)</option>
                    </select>
                </div>

                {{-- Password Reset (Optional) --}}
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2">
                    <p class="text-xs font-bold text-slate-800">Password Reset (Optional)</p>
                    <p class="text-[11px] text-slate-500">Leave both fields empty if you do not want to alter the current password.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                        <input type="password" name="password" minlength="8" placeholder="New Password"
                               class="w-full text-xs rounded-lg border border-slate-300 px-3 py-1.5 bg-white">
                        <input type="password" name="password_confirmation" minlength="8" placeholder="Confirm New Password"
                               class="w-full text-xs rounded-lg border border-slate-300 px-3 py-1.5 bg-white">
                    </div>
                </div>

                {{-- Vendor Profile Fields (Visible if role is vendor) --}}
                <div id="editVendorFields" class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200 space-y-3">
                    <p class="text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Vendor Organization Profile</span>
                    </p>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Business / Brand Name</label>
                        <input type="text" name="business_name" id="edit_business_name"
                               class="w-full text-xs rounded-lg border border-slate-300 px-3 py-1.5 bg-white">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">City</label>
                            <input type="text" name="city" id="edit_city"
                                   class="w-full text-xs rounded-lg border border-slate-300 px-3 py-1.5 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Contact Phone</label>
                            <input type="text" name="phone" id="edit_phone"
                                   class="w-full text-xs rounded-lg border border-slate-300 px-3 py-1.5 bg-white">
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_approved" id="edit_is_approved" value="1"
                               class="rounded text-emerald-600 focus:ring-emerald-500">
                        <label for="edit_is_approved" class="text-xs font-medium text-slate-700">Vendor storefront is approved &amp; verified</label>
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeEditUserModal()" class="btn-secondary text-xs py-2 px-4">Cancel</button>
                    <button type="submit" class="btn-emerald text-xs font-bold py-2 px-5 shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 3: CREATE CATEGORY --}}
    {{-- ========================================================================= --}}
    <div id="createCategoryModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 sm:p-7 space-y-5 border border-slate-200 relative">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Create New Category</h3>
                        <p class="text-xs text-slate-500">Define a taxonomy category for event listings and discovery.</p>
                    </div>
                </div>
                <button type="button" onclick="closeCreateCategoryModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Category Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Comedy & Improv, Live Music, Tech Talks"
                           class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Custom Slug (Optional)</label>
                    <input type="text" name="slug" placeholder="e.g. comedy-improv (auto-generated if left blank)"
                           class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 font-mono text-slate-700 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    <p class="text-[10px] text-slate-400 mt-1">Leave empty to automatically generate a clean, URL-safe slug.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeCreateCategoryModal()" class="btn-secondary text-xs py-2 px-4">Cancel</button>
                    <button type="submit" class="btn-emerald text-xs font-bold py-2 px-5 shadow-sm">Create Category</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 4: EDIT CATEGORY --}}
    {{-- ========================================================================= --}}
    <div id="editCategoryModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 sm:p-7 space-y-5 border border-slate-200 relative">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" id="editCategoryHeading">Edit Category</h3>
                        <p class="text-xs text-slate-500">Update taxonomy name and URL slug identifier.</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditCategoryModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" id="editCategoryForm" action="" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Category Name *</label>
                    <input type="text" name="name" id="edit_cat_name" required
                           class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Slug Identifier *</label>
                    <input type="text" name="slug" id="edit_cat_slug" required
                           class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 font-mono text-slate-700 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeEditCategoryModal()" class="btn-secondary text-xs py-2 px-4">Cancel</button>
                    <button type="submit" class="btn-emerald text-xs font-bold py-2 px-5 shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- JavaScript for Admin Management Modals --}}
    <script>
        function openCreateUserModal() {
            document.getElementById('createUserModal').classList.remove('hidden');
        }

        function closeCreateUserModal() {
            document.getElementById('createUserModal').classList.add('hidden');
        }

        function toggleCreateVendorFields(role) {
            const container = document.getElementById('createVendorFields');
            if (role === 'vendor') {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }

        function openEditUserModal(user) {
            const modal = document.getElementById('editUserModal');
            const form = document.getElementById('editUserForm');
            form.action = "{{ url('/admin/users') }}/" + user.id;

            document.getElementById('editModalHeading').textContent = 'Edit Account: ' + user.name;
            document.getElementById('edit_name').value = user.name || '';
            document.getElementById('edit_email').value = user.email || '';
            document.getElementById('edit_role').value = user.role || 'user';
            document.getElementById('edit_business_name').value = user.business_name || '';
            document.getElementById('edit_city').value = user.city || '';
            document.getElementById('edit_phone').value = user.phone || '';
            document.getElementById('edit_is_approved').checked = Boolean(user.is_approved);

            toggleEditVendorFields(user.role);
            modal.classList.remove('hidden');
        }

        function closeEditUserModal() {
            document.getElementById('editUserModal').classList.add('hidden');
        }

        function toggleEditVendorFields(role) {
            const container = document.getElementById('editVendorFields');
            if (role === 'vendor') {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }

        function openCreateCategoryModal() {
            document.getElementById('createCategoryModal').classList.remove('hidden');
        }

        function closeCreateCategoryModal() {
            document.getElementById('createCategoryModal').classList.add('hidden');
        }

        function openEditCategoryModal(cat) {
            const modal = document.getElementById('editCategoryModal');
            const form = document.getElementById('editCategoryForm');
            form.action = "{{ url('/admin/categories') }}/" + cat.id;

            document.getElementById('editCategoryHeading').textContent = 'Edit Category: ' + cat.name;
            document.getElementById('edit_cat_name').value = cat.name || '';
            document.getElementById('edit_cat_slug').value = cat.slug || '';

            modal.classList.remove('hidden');
        }

        function closeEditCategoryModal() {
            document.getElementById('editCategoryModal').classList.add('hidden');
        }

        // Close on ESC key or clicking outside backdrop
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCreateUserModal();
                closeEditUserModal();
                closeCreateCategoryModal();
                closeEditCategoryModal();
            }
        });

        document.getElementById('createUserModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeCreateUserModal();
        });

        document.getElementById('editUserModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeEditUserModal();
        });

        document.getElementById('createCategoryModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeCreateCategoryModal();
        });

        document.getElementById('editCategoryModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeEditCategoryModal();
        });
    </script>
</x-crm-layout>
