<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="page-title">Vendor Dashboard</h2>
                <p class="page-subtitle">Manage your profile, events, tickets, and check-ins.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('vendor.events.create') }}" class="btn-primary text-sm">
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Event
                </a>
                <a href="{{ route('vendor.orders.index') }}" class="btn-secondary text-sm">
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/></svg>
                    Orders
                </a>
                <a href="{{ route('vendor.checkin.form') }}" class="btn-secondary text-sm">
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    QR Check-In
                </a>
            </div>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content max-w-6xl space-y-6">

            @php
                $totalEvents = $events ? $events->total() : 0;
                $publishedEvents = $profile ? $profile->events()->where('status', 'published')->count() : 0;
                $totalOrders = $profile ? \App\Models\Order::where('vendor_profile_id', $profile->id)->whereIn('status', ['paid'])->count() : 0;
                $followers = $profile ? $profile->followers()->count() : 0;
            @endphp

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="surface p-5 text-center">
                    <div class="text-3xl font-bold text-slate-900">{{ $totalEvents }}</div>
                    <div class="text-sm text-slate-500 mt-1">Total Events</div>
                </div>
                <div class="surface p-5 text-center">
                    <div class="text-3xl font-bold text-emerald-600">{{ $publishedEvents }}</div>
                    <div class="text-sm text-slate-500 mt-1">Published</div>
                </div>
                <div class="surface p-5 text-center">
                    <div class="text-3xl font-bold text-sky-600">{{ $totalOrders }}</div>
                    <div class="text-sm text-slate-500 mt-1">Tickets Sold</div>
                </div>
                <div class="surface p-5 text-center">
                    <div class="text-3xl font-bold text-purple-600">{{ $followers }}</div>
                    <div class="text-sm text-slate-500 mt-1">Followers</div>
                </div>
            </div>

            {{-- Profile Status --}}
            <div class="surface p-6">
                @if(!$profile)
                    <div class="flex flex-col items-center py-4 text-center">
                        <svg class="w-12 h-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">Set Up Your Vendor Profile</h3>
                        <p class="text-slate-600 mb-5 max-w-md">Add your business info, gallery, opening hours, and social links so customers can find and trust you.</p>
                        <a href="{{ route('vendor.profile.edit') }}" class="btn-primary">Create Vendor Profile</a>
                    </div>
                @else
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-slate-900 rounded flex items-center justify-center text-white text-2xl font-bold shrink-0">
                                {{ strtoupper(substr($profile->business_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 text-lg">{{ $profile->business_name }}</div>
                                <div class="text-slate-500 text-sm">{{ $profile->address }}{{ $profile->city ? ' | '.$profile->city : '' }}</div>
                                @if($profile->categories->count())
                                    <div class="flex gap-1 mt-1">
                                        @foreach($profile->categories as $cat)
                                            <span class="badge text-xs">{{ $cat->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('vendor.profile.edit') }}" class="btn-secondary text-sm">Edit Profile</a>
                            <a href="{{ route('vendors.show', $profile->slug) }}" target="_blank" class="btn-secondary text-sm">View Public →</a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Events Table --}}
            <div class="surface p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-slate-900">Your Events</h3>
                    <a href="{{ route('vendor.events.create') }}" class="btn-primary text-sm px-4 py-2">+ Add Event</a>
                </div>

                @forelse($events ?? [] as $event)
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 py-4 border-b border-slate-100 last:border-0">
                        <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-slate-100 rounded border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                                    @php $img = $event->media->where('type','image')->first(); @endphp
                                    @if($img)
                                        <img src="{{ Storage::disk($img->disk)->url($img->path) }}" class="w-full h-full object-cover" alt="">
                                    @else
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @endif
                            </div>
                            <div>
                                <a href="{{ route('events.show', $event->slug) }}" target="_blank" class="font-semibold text-emerald-700 hover:underline">{{ $event->title }}</a>
                                <div class="text-sm text-slate-500 mt-0.5 flex flex-wrap gap-x-3 gap-y-0.5">
                                    <span>{{ $event->event_date?->format('M j, Y') }}</span>
                                    <span class="inline-flex items-center gap-1">
                                        @if($event->status === 'published')
                                            <span class="w-2 h-2 bg-emerald-400 rounded-full inline-block"></span> Published
                                        @else
                                            <span class="w-2 h-2 bg-amber-400 rounded-full inline-block"></span> Draft
                                        @endif
                                    </span>
                                    <span class="uppercase text-xs font-medium">{{ $event->event_type }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 shrink-0">
                            <a href="{{ route('vendor.events.edit', $event) }}" class="btn-secondary text-xs px-3 py-1.5">Edit</a>
                            <a href="{{ route('vendor.tickets.create', $event) }}" class="btn-secondary text-xs px-3 py-1.5">+ Ticket</a>
                            @if($event->status === 'draft')
                                <form method="POST" action="{{ route('vendor.events.publish', $event) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn-primary text-xs px-3 py-1.5">Publish</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('vendor.events.unpublish', $event) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn-secondary text-xs px-3 py-1.5 text-amber-700">Unpublish</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('vendor.events.destroy', $event) }}" onsubmit="return confirm('Delete this event?')">
                                @csrf @method('DELETE')
                                <button class="btn-secondary text-xs px-3 py-1.5 text-red-600 hover:text-red-700 hover:border-red-200">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 border-2 border-dashed border-slate-200 rounded-lg">
                        <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-slate-600 mb-4 font-bold">No events yet.</p>
                        <a href="{{ route('vendor.events.create') }}" class="btn-primary">Create First Event</a>
                    </div>
                @endforelse

                @if(($events ?? null) && method_exists($events, 'links'))
                    <div class="mt-4">{{ $events->links() }}</div>
                @endif
            </div>

            {{-- Quick Links --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                <a href="{{ route('vendor.profile.edit') }}" class="surface p-5 text-center card-hover block border border-slate-200 hover:border-slate-400">
                    <svg class="w-8 h-8 text-slate-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <p class="text-sm font-bold text-slate-900">Edit Profile</p>
                </a>
                <a href="{{ route('vendor.orders.index') }}" class="surface p-5 text-center card-hover block border border-slate-200 hover:border-slate-400">
                    <svg class="w-8 h-8 text-slate-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="text-sm font-bold text-slate-900">View Orders</p>
                </a>
                <a href="{{ route('vendor.checkin.form') }}" class="surface p-5 text-center card-hover block border border-slate-200 hover:border-slate-400">
                    <svg class="w-8 h-8 text-slate-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-bold text-slate-900">QR Check-In</p>
                </a>
                <a href="{{ route('vendors.index') }}" class="surface p-5 text-center card-hover block border border-slate-200 hover:border-slate-400">
                    <svg class="w-8 h-8 text-slate-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <p class="text-sm font-bold text-slate-900">Browse Vendors</p>
                </a>
            </div>
        </div>
    </section>
</x-app-layout>
