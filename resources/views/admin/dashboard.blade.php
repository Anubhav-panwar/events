<x-crm-layout title="Admin Dashboard" role="admin">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <span>Admin CRM</span>
            <span>/</span>
            <span class="text-slate-900 font-bold capitalize">
                @if($tab === 'events')
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
        <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800">
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
                        @if($tab === 'events')
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
                        @if($tab === 'events')
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
                    <a href="{{ route('admin.dashboard', ['tab' => 'events']) }}"
                       class="text-xs py-2 px-3.5 rounded-lg font-bold transition-all {{ $tab === 'events' ? 'bg-emerald-500 text-slate-950 shadow-md' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        Events ({{ $kpis['total_events'] }})
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
            <a href="{{ route('admin.dashboard', ['tab' => 'overview']) }}" class="crm-card p-5 hover:border-slate-400 transition-all block group">
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
                <p class="text-xs text-slate-500 mt-2">Platform members &amp; attendees</p>
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
                                @if(\Illuminate\Support\Facades\Route::has('vendors.show'))
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
            </div>

        {{-- ========================================================================= --}}
        {{-- TAB 2: PLATFORM EVENTS FULL MANAGEMENT --}}
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
                        <select name="status" class="input-field text-xs py-1.5 px-3 w-32" onchange="this.form.submit()">
                            <option value="all" @selected($status === 'all')>All Status</option>
                            <option value="published" @selected($status === 'published')>Live Only</option>
                            <option value="draft" @selected($status === 'draft')>Draft Only</option>
                        </select>
                        <button type="submit" class="btn-primary text-xs py-1.5 px-3.5">Filter</button>
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
        {{-- TAB 3: VENDORS DIRECTORY FULL MANAGEMENT --}}
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
                                        @if(\Illuminate\Support\Facades\Route::has('vendors.show'))
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
</x-crm-layout>
