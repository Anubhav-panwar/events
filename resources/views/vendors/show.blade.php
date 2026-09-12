<x-app-layout>
    @php
        $coverImage = $vendor->media->where('type', 'image')->first();
        $allImages = $vendor->media->where('type', 'image');
        $allVideos = $vendor->media->where('type', 'video');
        $followerCount = $vendor->followers()->count();
        $eventCount = $vendor->events()->where('status', 'published')->count();
        $isFollowing = auth()->check()
            ? auth()->user()->followedVendors()->where('vendor_profile_id', $vendor->id)->exists()
            : false;
    @endphp

    @push('meta')
        <title>{{ $vendor->business_name }} — Verified Event Organizer on Huddle</title>
        <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($vendor->description), 160) }}">
    @endpush

    {{-- Ultra-Premium Hero Banner --}}
    <div class="relative h-72 sm:h-80 md:h-96 overflow-hidden bg-slate-950 border-b border-slate-800">
        {{-- Background Cover Image with Gradient Vignette --}}
        @if($coverImage)
            <img src="{{ $coverImage->url }}" class="absolute inset-0 w-full h-full object-cover opacity-35" alt="{{ $vendor->business_name }}">
        @else
            <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1600&q=70')] bg-cover bg-center opacity-30"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-slate-950/80"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-transparent to-slate-950/60"></div>

        {{-- Hero Content Container --}}
        <div class="relative app-content h-full flex flex-col justify-between py-6 md:py-8 z-10">
            {{-- Breadcrumbs & Top Share Actions --}}
            <div class="flex items-center justify-between">
                <nav class="flex items-center gap-2 text-xs font-medium text-slate-300">
                    <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
                    <span>/</span>
                    <a href="{{ route('vendors.index') }}" class="hover:text-white transition-colors">Vendors</a>
                    <span>/</span>
                    <span class="text-emerald-400 font-bold truncate max-w-[150px] sm:max-w-none">{{ $vendor->business_name }}</span>
                </nav>

                <div class="flex items-center gap-2">
                    @if($vendor->website)
                        <a href="{{ $vendor->website }}" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-semibold backdrop-blur-sm border border-white/15 transition-all flex items-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span class="hidden sm:inline">Official Website</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Organizer Identity Header --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-end gap-5">
                {{-- Logo Avatar with glowing border --}}
                <div class="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 rounded-2xl bg-gradient-to-br from-emerald-500 via-teal-500 to-emerald-700 flex items-center justify-center text-white text-3xl sm:text-4xl font-extrabold shadow-2xl border-4 border-slate-900 ring-2 ring-emerald-500/50 shrink-0">
                    {{ strtoupper(substr($vendor->business_name, 0, 1)) }}
                </div>

                {{-- Name & Verification --}}
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2.5 mb-1.5">
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/40 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Verified Organizer
                        </span>
                        @if($vendor->city)
                            <span class="text-xs text-slate-300 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                {{ $vendor->city }}{{ $vendor->country ? ', '.$vendor->country : '' }}
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight leading-tight drop-shadow-sm">
                        {{ $vendor->business_name }}
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <div x-data="{ activeTab: 'events' }">
        {{-- Sticky Highlights & Follow Action Bar --}}
        <section class="bg-white border-b border-slate-200 sticky top-16 z-40 shadow-sm">
        <div class="app-content">
            <div class="flex flex-wrap items-center justify-between gap-4 py-3.5">
                {{-- KPI Metric Strip --}}
                <div class="flex items-center gap-6 sm:gap-8">
                    <div>
                        <p class="text-xl sm:text-2xl font-bold text-slate-950 leading-none">{{ $eventCount }}</p>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mt-1">Events</p>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div>
                        <p class="text-xl sm:text-2xl font-bold text-slate-950 leading-none">{{ $followerCount }}</p>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mt-1">Followers</p>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div>
                        <p class="text-xl sm:text-2xl font-bold text-emerald-700 leading-none">4.9 ★</p>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mt-1">Trust Score</p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3">
                    @auth
                        @if($isFollowing)
                            <form method="POST" action="{{ route('vendors.unfollow', $vendor->slug) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn-secondary text-xs sm:text-sm py-2 px-4 inline-flex items-center gap-1.5 shadow-sm">
                                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    Following
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('vendors.follow', $vendor->slug) }}">
                                @csrf
                                <button class="btn-emerald text-xs sm:text-sm py-2 px-5 inline-flex items-center gap-1.5 shadow-sm shadow-emerald-950/20 font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Follow Organizer
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-emerald text-xs sm:text-sm py-2 px-5 font-bold shadow-sm">
                            Follow Organizer
                        </a>
                    @endauth

                    @if($vendor->phone)
                        <a href="tel:{{ $vendor->phone }}" class="btn-secondary text-xs sm:text-sm py-2 px-3.5 hidden sm:inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            Call Host
                        </a>
                    @endif
                </div>
            </div>

            {{-- Segmented Tab Bar --}}
            <div class="flex items-center gap-8 border-t border-slate-100 overflow-x-auto hide-scrollbar pt-1">
                <button @click="activeTab = 'events'"
                        :class="activeTab === 'events' ? 'border-emerald-600 text-emerald-800 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 font-medium'"
                        class="py-3 text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Upcoming Events ({{ $eventCount }})
                </button>

                @if($allImages->count() || $allVideos->count())
                    <button @click="activeTab = 'gallery'"
                            :class="activeTab === 'gallery' ? 'border-emerald-600 text-emerald-800 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 font-medium'"
                            class="py-3 text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Photo &amp; Media Gallery ({{ $allImages->count() + $allVideos->count() }})
                    </button>
                @endif

                <button @click="activeTab = 'about'"
                        :class="activeTab === 'about' ? 'border-emerald-600 text-emerald-800 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 font-medium'"
                        class="py-3 text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    About Organizer
                </button>

                @if($vendor->latitude && $vendor->longitude || $vendor->address)
                    <button @click="activeTab = 'location'"
                            :class="activeTab === 'location' ? 'border-emerald-600 text-emerald-800 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 font-medium'"
                            class="py-3 text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        Location &amp; Hours
                    </button>
                @endif
            </div>
        </div>
    </section>

    {{-- Main Storefront Content Area --}}
    <section class="page-section bg-slate-50 min-h-[500px]">
        <div class="app-content">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- Left Content: Dynamic Tab Views (8 Columns) --}}
                <div class="lg:col-span-8">
                    
                    {{-- TAB 1: Upcoming Events --}}
                    <div x-show="activeTab === 'events'" class="space-y-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">Scheduled Experiences</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Tickets booked directly through Huddle with instant QR issuance</p>
                            </div>
                            <span class="badge bg-emerald-50 text-emerald-800 border-emerald-200">{{ $eventCount }} Active Listings</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @forelse($events as $event)
                                <x-event-card :event="$event" />
                            @empty
                                <div class="col-span-2 surface p-12 text-center">
                                    <div class="w-16 h-16 bg-emerald-50 text-emerald-700 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                                        🎟️
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900">No scheduled events right now</h3>
                                    <p class="text-sm text-slate-500 mt-1">This organizer is curating upcoming schedules. Follow them to get notified instantly.</p>
                                </div>
                            @endforelse
                        </div>

                        @if($events->hasPages())
                            <div class="mt-6">{{ $events->links() }}</div>
                        @endif
                    </div>

                    {{-- TAB 2: Photo & Video Gallery --}}
                    @if($allImages->count() || $allVideos->count())
                        <div x-show="activeTab === 'gallery'" class="space-y-6" style="display: none;">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900">Media Showcase</h2>
                                    <p class="text-xs text-slate-500 mt-0.5">High-resolution past events, stages, and venue experiences</p>
                                </div>
                            </div>

                            @if($allImages->count())
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @foreach($allImages as $m)
                                        <div class="media-tile aspect-square rounded-xl overflow-hidden border border-slate-200 cursor-pointer group relative shadow-sm hover:shadow-md transition-all"
                                             onclick="openLightbox('{{ $m->url }}')">
                                            <img src="{{ $m->url }}" alt="{{ $m->original_name ?? $vendor->business_name }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            <div class="absolute inset-0 bg-slate-950/0 group-hover:bg-slate-950/30 transition-colors flex items-center justify-center">
                                                <svg class="w-7 h-7 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($allVideos->count())
                                <div class="pt-6 border-t border-slate-200">
                                    <h3 class="text-base font-bold text-slate-900 mb-4">Video Highlights</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @foreach($allVideos as $m)
                                            <div class="rounded-xl border border-slate-200 overflow-hidden bg-black aspect-video flex items-center justify-center shadow-sm">
                                                <video class="w-full h-full object-cover" src="{{ $m->url }}" controls></video>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- TAB 3: About Organizer --}}
                    <div x-show="activeTab === 'about'" class="space-y-6" style="display: none;">
                        <div class="surface p-7 sm:p-8">
                            <h2 class="text-xl font-bold text-slate-900 mb-4">About {{ $vendor->business_name }}</h2>
                            <p class="text-slate-700 leading-relaxed text-base whitespace-pre-line">
                                {{ $vendor->description ?: 'This organizer has not provided a detailed bio yet.' }}
                            </p>

                            @if($vendor->categories->count())
                                <div class="mt-8 pt-6 border-t border-slate-100">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Event Specialties</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($vendor->categories as $cat)
                                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800 text-xs font-bold border border-slate-200">
                                                {{ $cat->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- TAB 4: Location & Hours --}}
                    @if($vendor->latitude && $vendor->longitude || $vendor->address)
                        <div x-show="activeTab === 'location'" class="space-y-6" style="display: none;">
                            <div class="surface p-7 sm:p-8">
                                <h2 class="text-xl font-bold text-slate-900 mb-3">Headquarters &amp; Venue Location</h2>
                                <p class="text-sm text-slate-600 mb-5">
                                    {{ $vendor->address ?: 'Address on file' }}{{ $vendor->city ? ', '.$vendor->city : '' }}{{ $vendor->country ? ', '.$vendor->country : '' }}
                                </p>

                                @if($vendor->latitude && $vendor->longitude)
                                    <iframe
                                        class="w-full h-72 rounded-xl border border-slate-200 shadow-sm"
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                        src="https://maps.google.com/maps?q={{ $vendor->latitude }},{{ $vendor->longitude }}&z=15&output=embed">
                                    </iframe>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right Sidebar: Organizer Info & Contact (4 Columns) --}}
                <div class="lg:col-span-4 space-y-6">
                    
                    {{-- Verified Organizer Trust Card --}}
                    <div class="surface p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">
                                ✓
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Verified Partner</h3>
                                <p class="text-xs text-slate-500">Official Huddle Event Host</p>
                            </div>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            {{ $vendor->business_name }} is verified for identity, organizer licensing, and ticket delivery safety.
                        </p>
                    </div>

                    {{-- Contact & Social Channels --}}
                    <div class="surface p-6">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 mb-4">Direct Connect</h3>
                        <div class="space-y-3">
                            @if($vendor->website)
                                <a href="{{ $vendor->website }}" target="_blank" rel="noopener" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-800 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <span class="text-emerald-600">🌐</span> Official Website
                                    </span>
                                    <span class="text-slate-400">&rarr;</span>
                                </a>
                            @endif

                            @if($vendor->phone)
                                <a href="tel:{{ $vendor->phone }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-800 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <span class="text-emerald-600">📞</span> {{ $vendor->phone }}
                                    </span>
                                    <span class="text-slate-400">&rarr;</span>
                                </a>
                            @endif

                            @if($vendor->instagram)
                                <a href="{{ $vendor->instagram }}" target="_blank" rel="noopener" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-800 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <span class="text-pink-600">📸</span> Instagram
                                    </span>
                                    <span class="text-slate-400">&rarr;</span>
                                </a>
                            @endif

                            @if($vendor->twitter)
                                <a href="{{ $vendor->twitter }}" target="_blank" rel="noopener" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-800 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <span class="text-slate-900">𝕏</span> X / Twitter
                                    </span>
                                    <span class="text-slate-400">&rarr;</span>
                                </a>
                            @endif

                            @if($vendor->facebook)
                                <a href="{{ $vendor->facebook }}" target="_blank" rel="noopener" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-800 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <span class="text-blue-600">👥</span> Facebook
                                    </span>
                                    <span class="text-slate-400">&rarr;</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Opening Hours --}}
                    @if($vendor->opening_hours && count($vendor->opening_hours))
                        <div class="surface p-6">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Operating Schedule
                            </h3>
                            <div class="space-y-2">
                                @php $today = now()->format('l'); @endphp
                                @foreach($vendor->opening_hours as $row)
                                    <div class="flex justify-between items-center text-xs {{ ($row['day'] ?? '') === $today ? 'font-bold text-emerald-800 bg-emerald-50 p-1.5 rounded' : 'text-slate-600' }}">
                                        <span>{{ $row['day'] ?? 'Day' }}</span>
                                        <span>
                                            @if(!empty($row['closed']))
                                                <span class="text-red-500 font-semibold">Closed</span>
                                            @else
                                                {{ $row['open'] ?? '-' }} – {{ $row['close'] ?? '-' }}
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </section>
    </div>

    {{-- Interactive Lightbox Modal --}}
    <div id="lightbox" class="fixed inset-0 bg-slate-950/95 z-[300] hidden items-center justify-center p-4 select-none" onclick="closeLightbox()">
        <img id="lightboxImg" src="" class="max-h-[90vh] max-w-full rounded-2xl object-contain shadow-2xl" alt="" onclick="event.stopPropagation()">
        <button onclick="closeLightbox()" class="absolute top-5 right-5 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-2.5 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    @push('scripts')
    <script>
        function openLightbox(src) {
            document.getElementById('lightboxImg').src = src;
            const lb = document.getElementById('lightbox');
            lb.classList.remove('hidden');
            lb.classList.add('flex');
        }
        function closeLightbox() {
            const lb = document.getElementById('lightbox');
            lb.classList.add('hidden');
            lb.classList.remove('flex');
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLightbox();
        });
    </script>
    @endpush
</x-app-layout>
