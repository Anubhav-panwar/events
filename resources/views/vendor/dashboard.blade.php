@php
    $hasVendorEventsCreateRoute = \Illuminate\Support\Facades\Route::has('vendor.events.create');
    $hasVendorOrdersRoute = \Illuminate\Support\Facades\Route::has('vendor.orders.index');
    $hasVendorCheckinRoute = \Illuminate\Support\Facades\Route::has('vendor.checkin.form');
    $hasVendorProfileRoute = \Illuminate\Support\Facades\Route::has('vendor.profile.edit');
    $hasVendorEventsEditRoute = \Illuminate\Support\Facades\Route::has('vendor.events.edit');
    $hasVendorTicketsCreateRoute = \Illuminate\Support\Facades\Route::has('vendor.tickets.create');
    $hasVendorEventsPublishRoute = \Illuminate\Support\Facades\Route::has('vendor.events.publish');
    $hasVendorEventsUnpublishRoute = \Illuminate\Support\Facades\Route::has('vendor.events.unpublish');
    $hasVendorEventsDestroyRoute = \Illuminate\Support\Facades\Route::has('vendor.events.destroy');
    $hasEventsShowRoute = \Illuminate\Support\Facades\Route::has('events.show');
    $hasVendorsShowRoute = \Illuminate\Support\Facades\Route::has('vendors.show');

    $totalEvents = $events ? $events->total() : 0;
    $publishedEvents = $profile ? $profile->events()->where('status', 'published')->count() : 0;
    $draftEvents = $profile ? $profile->events()->where('status', 'draft')->count() : 0;
    $paidOrders = $profile ? \App\Models\Order::where('vendor_profile_id', $profile->id)->where('status', 'paid')->get() : collect();
    $totalOrdersCount = $paidOrders->count();
    $totalRevenue = $paidOrders->sum('total');
    $followers = $profile ? $profile->followers()->count() : 0;
@endphp

<x-crm-layout title="Vendor Dashboard" role="vendor">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <span>Vendor CRM</span>
            <span>/</span>
            <span class="text-slate-900 font-bold">Dashboard</span>
        </div>
    </x-slot>

    <div class="space-y-6 max-w-7xl mx-auto">

        {{-- Welcome Header Banner --}}
        <div class="crm-banner bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden border border-slate-800">
            <div class="absolute right-0 top-0 -mt-8 -mr-8 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold border border-emerald-500/30">
                            Vendor Console
                        </span>
                        <span class="text-xs text-slate-400">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                        {{ $profile ? $profile->business_name : 'Welcome to your Vendor CRM' }}
                    </h1>
                    <p class="text-slate-300 text-sm mt-1 max-w-2xl leading-relaxed">
                        Manage your live events, track attendee ticket purchases, validate QR admissions, and grow your audience.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    @if($hasVendorEventsCreateRoute)
                        <a href="{{ route('vendor.events.create') }}" class="btn-emerald text-sm px-4 py-2.5 shadow-md shadow-emerald-950/20 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Create New Event
                        </a>
                    @endif
                    @if($hasVendorCheckinRoute)
                        <a href="{{ route('vendor.checkin.form') }}" class="btn-secondary text-sm px-4 py-2.5 bg-slate-800/80 border-slate-700 text-white hover:bg-slate-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            Scan Ticket
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Setup Prompt if Profile is missing --}}
        @if(!$profile)
            <div class="surface p-6 border-l-4 border-amber-500 bg-amber-50/50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Complete Your Business Profile</h3>
                        <p class="text-xs text-slate-600 mt-0.5">Your profile is missing business details, address, and gallery. Add them to start publishing events.</p>
                    </div>
                </div>
                @if($hasVendorProfileRoute)
                    <a href="{{ route('vendor.profile.edit') }}" class="btn-primary text-xs py-2 px-4 shrink-0">
                        Setup Profile &rarr;
                    </a>
                @endif
            </div>
        @endif

        {{-- Key Metrics KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            {{-- Metric 1: Total Events --}}
            <div class="surface p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Events</span>
                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-950">{{ $totalEvents }}</span>
                    <span class="text-xs font-semibold text-emerald-600">{{ $publishedEvents }} published</span>
                </div>
                <div class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span>{{ $draftEvents }} in draft</span>
                </div>
            </div>

            {{-- Metric 2: Tickets Sold --}}
            <div class="surface p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tickets Sold</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-950">{{ $totalOrdersCount }}</span>
                    <span class="text-xs font-semibold text-emerald-600">Paid orders</span>
                </div>
                <div class="mt-2 text-xs text-slate-500">Across all scheduled listings</div>
            </div>

            {{-- Metric 3: Total Revenue --}}
            <div class="surface p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Ticket Revenue</span>
                    <div class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-950">${{ number_format($totalRevenue, 2) }}</span>
                    <span class="text-xs font-bold text-slate-500">USD</span>
                </div>
                <div class="mt-2 text-xs text-slate-500">Net platform receipts</div>
            </div>

            {{-- Metric 4: Followers --}}
            <div class="surface p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Followers</span>
                    <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0v4H7v-4m10 0H7"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-950">{{ $followers }}</span>
                    <span class="text-xs font-semibold text-purple-600">Subscribers</span>
                </div>
                <div class="mt-2 text-xs text-slate-500">Notified of new events</div>
            </div>
        </div>

        {{-- Events Management Section --}}
        <div class="surface p-6" id="events">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 border-b border-slate-100 pb-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Events Management</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Manage schedules, uploaded media, ticket tiers, and publication status.</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($hasVendorEventsCreateRoute)
                        <a href="{{ route('vendor.events.create') }}" class="btn-primary text-xs py-2 px-3.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Event
                        </a>
                    @endif
                </div>
            </div>

            @if($events && $events->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="pb-3 pr-4">Event Details</th>
                                <th class="pb-3 pr-4">Schedule</th>
                                <th class="pb-3 pr-4">Location</th>
                                <th class="pb-3 pr-4">Tickets / Price</th>
                                <th class="pb-3 pr-4">Status</th>
                                <th class="pb-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($events as $event)
                                @php
                                    $img = $event->media->where('type', 'image')->first();
                                    $imgCount = $event->media->where('type', 'image')->count();
                                    $imgUrl = $img ? $img->url : 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=400&q=80';
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    {{-- Event Column with Dynamic Thumbnail --}}
                                    <td class="py-4 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div class="relative w-14 h-14 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shrink-0 shadow-sm">
                                                <img src="{{ $imgUrl }}" class="w-full h-full object-cover" alt="{{ $event->title }}">
                                                @if($imgCount > 1)
                                                    <span class="absolute bottom-0.5 right-0.5 px-1 py-0.2 bg-black/75 text-[9px] font-bold text-white rounded">
                                                        {{ $imgCount }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                @if($hasEventsShowRoute)
                                                    <a href="{{ route('events.show', $event->slug) }}" target="_blank" class="font-bold text-slate-900 group-hover:text-emerald-700 transition-colors truncate block max-w-xs">
                                                        {{ $event->title }}
                                                    </a>
                                                @else
                                                    <span class="font-bold text-slate-900 truncate block max-w-xs">{{ $event->title }}</span>
                                                @endif
                                                <div class="flex items-center gap-2 mt-1">
                                                    @if($event->category)
                                                        <span class="text-[11px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">{{ $event->category->name }}</span>
                                                    @endif
                                                    @if($event->is_featured)
                                                        <span class="text-[10px] font-bold text-amber-800 bg-amber-100 px-1.5 py-0.5 rounded">Featured</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Schedule --}}
                                    <td class="py-4 pr-4 whitespace-nowrap text-slate-700 text-xs">
                                        <p class="font-semibold text-slate-900">{{ $event->event_date?->format('M j, Y') }}</p>
                                        <p class="text-slate-500 mt-0.5">{{ $event->start_time instanceof \Carbon\Carbon ? $event->start_time->format('g:i A') : $event->start_time }}</p>
                                    </td>

                                    {{-- Location --}}
                                    <td class="py-4 pr-4 text-xs text-slate-600">
                                        <p class="font-medium text-slate-900 truncate max-w-[160px]">{{ $event->venue_name ?: ($event->city ?: 'TBA') }}</p>
                                        <p class="text-slate-400 truncate max-w-[160px]">{{ $event->city }}{{ $event->country ? ', '.$event->country : '' }}</p>
                                    </td>

                                    {{-- Tickets / Pricing --}}
                                    <td class="py-4 pr-4 whitespace-nowrap text-xs">
                                        @if($event->event_type === 'free')
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-bold rounded border border-emerald-200">FREE</span>
                                        @else
                                            <span class="font-bold text-slate-900">${{ number_format((float) $event->base_price, 2) }}</span>
                                        @endif
                                        <p class="text-slate-500 text-[11px] mt-1">{{ $event->ticketTypes->count() }} ticket type(s)</p>
                                    </td>

                                    {{-- Status --}}
                                    <td class="py-4 pr-4 whitespace-nowrap">
                                        @if($event->status === 'published')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-bold border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Live
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 text-xs font-bold border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Draft
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions Toolbar --}}
                                    <td class="py-4 text-right whitespace-nowrap text-xs">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if($hasEventsShowRoute)
                                                <a href="{{ route('events.show', $event->slug) }}" target="_blank" title="View Public Page" class="p-1.5 text-slate-400 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </a>
                                            @endif

                                            @if($hasVendorEventsEditRoute)
                                                <a href="{{ route('vendor.events.edit', $event) }}" title="Edit Details & Media" class="p-1.5 text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </a>
                                            @endif

                                            @if($hasVendorTicketsCreateRoute)
                                                <a href="{{ route('vendor.tickets.create', $event) }}" title="Add Ticket Type" class="p-1.5 text-slate-600 hover:text-teal-700 hover:bg-teal-50 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                                </a>
                                            @endif

                                            @if($event->status === 'draft')
                                                @if($hasVendorEventsPublishRoute)
                                                    <form method="POST" action="{{ route('vendor.events.publish', $event) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" title="Publish live" class="px-2 py-1 rounded bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-[11px] font-bold transition-colors">
                                                            Publish
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                @if($hasVendorEventsUnpublishRoute)
                                                    <form method="POST" action="{{ route('vendor.events.unpublish', $event) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" title="Unpublish to draft" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium transition-colors">
                                                            Draft
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                            @if($hasVendorEventsDestroyRoute)
                                                <form method="POST" action="{{ route('vendor.events.destroy', $event) }}" class="inline" onsubmit="return confirm('Permanently delete this event?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Delete" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100">
                    {{ $events->links() }}
                </div>
            @else
                <div class="text-center py-16 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                    <div class="w-14 h-14 bg-white rounded-2xl border border-slate-200 flex items-center justify-center mx-auto text-slate-400 mb-3 shadow-sm">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">No events listed yet</h3>
                    <p class="text-slate-500 text-xs mt-1 max-w-sm mx-auto">Create your first event listing with multiple images, schedule, and ticket prices.</p>
                    @if($hasVendorEventsCreateRoute)
                        <a href="{{ route('vendor.events.create') }}" class="btn-primary text-xs py-2 px-4 mt-4 inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Create Your First Event
                        </a>
                    @endif
                </div>
            @endif
        </div>

    </div>
</x-crm-layout>
