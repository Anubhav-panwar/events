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
        <title>{{ $vendor->business_name }} — {{ __('Verified Event Organizer on Huddle') }}</title>
        <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($vendor->description), 160) }}">
        <meta property="og:title" content="{{ $vendor->business_name }} — {{ __('Verified Event Organizer on Huddle') }}">
        <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($vendor->description), 160) }}">
        <meta property="og:type" content="profile">
        <meta property="og:url" content="{{ route('vendors.show', $vendor->slug) }}">
        @if($coverImage)
            <meta property="og:image" content="{{ $coverImage->url }}">
        @endif
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $vendor->business_name }}">
        <meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($vendor->description), 160) }}">
        @if($coverImage)
            <meta name="twitter:image" content="{{ $coverImage->url }}">
        @endif
    @endpush

    {{-- Root Vendor Page Container with unified Alpine state --}}
    <div x-data="{ activeTab: 'events', shareModalOpen: false }">
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
                    <a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Home') }}</a>
                    <span>/</span>
                    <a href="{{ route('vendors.index') }}" class="hover:text-white transition-colors">{{ __('Vendors') }}</a>
                    <span>/</span>
                    <span class="text-emerald-400 font-bold truncate max-w-[150px] sm:max-w-none">{{ $vendor->business_name }}</span>
                </nav>

                <div class="flex items-center gap-2">
                    <button type="button" @click="shareModalOpen = true" onclick="window.openVendorShareModal()" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-semibold backdrop-blur-sm border border-white/15 transition-all flex items-center gap-1.5 shadow-sm cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        <span>{{ __('Share Profile') }}</span>
                    </button>
                    @if($vendor->website)
                        <a href="{{ $vendor->website }}" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-semibold backdrop-blur-sm border border-white/15 transition-all flex items-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span class="hidden sm:inline">{{ __('Official Website') }}</span>
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
                            {{ __('Verified Organizer') }}
                        </span>
                        @if($vendorReviewsCount > 0)
                            <button @click="activeTab = 'reviews'" class="px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 text-xs font-bold border border-amber-500/40 flex items-center gap-1 transition-colors cursor-pointer">
                                <span class="text-amber-400">★</span>
                                <span>{{ number_format($vendorAverageRating, 1) }}</span>
                                <span class="text-amber-200/80 font-normal">({{ $vendorReviewsCount }} {{ $vendorReviewsCount === 1 ? __('review') : __('reviews') }})</span>
                            </button>
                        @endif
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

    <div>
        {{-- Sticky Highlights & Follow Action Bar --}}
        <section class="bg-white border-b border-slate-200 sticky top-16 z-40 shadow-sm">
        <div class="app-content">
            <div class="flex flex-wrap items-center justify-between gap-4 py-3.5">
                {{-- KPI Metric Strip --}}
                <div class="flex items-center gap-6 sm:gap-8">
                    <div>
                        <p class="text-xl sm:text-2xl font-bold text-slate-950 leading-none">{{ $eventCount }}</p>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mt-1">{{ __('Events') }}</p>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div>
                        <p class="text-xl sm:text-2xl font-bold text-slate-950 leading-none">{{ $followerCount }}</p>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mt-1">{{ __('Followers') }}</p>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div class="cursor-pointer" @click="activeTab = 'reviews'">
                        @if($vendorReviewsCount > 0)
                            <p class="text-xl sm:text-2xl font-bold text-amber-600 leading-none">{{ number_format($vendorAverageRating, 1) }} ★</p>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mt-1">{{ $vendorReviewsCount }} {{ $vendorReviewsCount === 1 ? __('Review') : __('Reviews') }}</p>
                        @else
                            <p class="text-xl sm:text-2xl font-bold text-emerald-700 leading-none">4.9 ★</p>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mt-1">{{ __('Trust Score') }}</p>
                        @endif
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
                                    {{ __('Following') }}
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('vendors.follow', $vendor->slug) }}">
                                @csrf
                                <button class="btn-emerald text-xs sm:text-sm py-2 px-5 inline-flex items-center gap-1.5 shadow-sm shadow-emerald-950/20 font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    {{ __('Follow Organizer') }}
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-emerald text-xs sm:text-sm py-2 px-5 font-bold shadow-sm">
                            {{ __('Follow Organizer') }}
                        </a>
                    @endauth

                    <button type="button" @click="shareModalOpen = true" onclick="window.openVendorShareModal()" class="btn-secondary text-xs sm:text-sm py-2 px-3.5 inline-flex items-center gap-1.5 shadow-sm cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        <span>{{ __('Share Profile') }}</span>
                    </button>

                    @if($vendor->phone)
                        <a href="tel:{{ $vendor->phone }}" class="btn-secondary text-xs sm:text-sm py-2 px-3.5 hidden sm:inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ __('Call Host') }}
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
                    {{ __('Upcoming Events') }} ({{ $eventCount }})
                </button>

                @if($allImages->count() || $allVideos->count())
                    <button @click="activeTab = 'gallery'"
                            :class="activeTab === 'gallery' ? 'border-emerald-600 text-emerald-800 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 font-medium'"
                            class="py-3 text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ __('Photo & Media Gallery') }} ({{ $allImages->count() + $allVideos->count() }})
                    </button>
                @endif

                <button @click="activeTab = 'about'"
                        :class="activeTab === 'about' ? 'border-emerald-600 text-emerald-800 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 font-medium'"
                        class="py-3 text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ __('About Organizer') }}
                </button>

                @if($vendor->latitude && $vendor->longitude || $vendor->address)
                    <button @click="activeTab = 'location'"
                            :class="activeTab === 'location' ? 'border-emerald-600 text-emerald-800 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 font-medium'"
                            class="py-3 text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        {{ __('Location & Hours') }}
                    </button>
                @endif

                <button @click="activeTab = 'reviews'"
                        :class="activeTab === 'reviews' ? 'border-emerald-600 text-emerald-800 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 font-medium'"
                        class="py-3 text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <span class="text-amber-500">★</span>
                    {{ __('Reviews & Ratings') }} ({{ $vendorReviewsCount }})
                </button>
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
                                <h2 class="text-xl font-bold text-slate-900">{{ __('Scheduled Experiences') }}</h2>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('Tickets booked directly through Huddle with instant QR issuance') }}</p>
                            </div>
                            <span class="badge bg-emerald-50 text-emerald-800 border-emerald-200">{{ $eventCount }} {{ __('Active Listings') }}</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @forelse($events as $event)
                                <x-event-card :event="$event" />
                            @empty
                                <div class="col-span-2 surface p-12 text-center">
                                    <div class="w-16 h-16 bg-emerald-50 text-emerald-700 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                                        🎟️
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900">{{ __('No scheduled events right now') }}</h3>
                                    <p class="text-sm text-slate-500 mt-1">{{ __('This organizer is curating upcoming schedules. Follow them to get notified instantly.') }}</p>
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
                                    <h2 class="text-xl font-bold text-slate-900">{{ __('Media Showcase') }}</h2>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ __('High-resolution past events, stages, and venue experiences') }}</p>
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
                                    <h3 class="text-base font-bold text-slate-900 mb-4">{{ __('Video Highlights') }}</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @foreach($allVideos as $m)
                                            <div class="rounded-xl border border-slate-200 overflow-hidden bg-black aspect-video flex items-center justify-center shadow-sm">
                                                <video class="w-full h-full object-cover" src="{{ $m->url }}" preload="metadata" playsinline controls></video>
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
                            <h2 class="text-xl font-bold text-slate-900 mb-4">{{ __('About') }} {{ $vendor->business_name }}</h2>
                            <p class="text-slate-700 leading-relaxed text-base whitespace-pre-line">
                                {{ $vendor->description ?: __('This organizer has not provided a detailed bio yet.') }}
                            </p>

                            @if($vendor->categories->count())
                                <div class="mt-8 pt-6 border-t border-slate-100">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">{{ __('Event Specialties') }}</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($vendor->categories as $cat)
                                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800 text-xs font-bold border border-slate-200">
                                                {{ __($cat->name) }}
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
                                <h2 class="text-xl font-bold text-slate-900 mb-3">{{ __('Headquarters & Venue Location') }}</h2>
                                <p class="text-sm text-slate-600 mb-5">
                                    {{ $vendor->address ?: __('Address on file') }}{{ $vendor->city ? ', '.$vendor->city : '' }}{{ $vendor->country ? ', '.$vendor->country : '' }}
                                </p>

                                @php
                                    $vMapQuery = ($vendor->latitude && $vendor->longitude)
                                        ? "{$vendor->latitude},{$vendor->longitude}"
                                        : urlencode(trim(($vendor->address ?? '') . ' ' . ($vendor->city ?? '') . ' ' . ($vendor->country ?? '')));
                                @endphp
                                <iframe
                                    class="w-full h-72 rounded-xl border border-slate-200 shadow-sm"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://maps.google.com/maps?q={{ $vMapQuery }}&z=15&output=embed"
                                    allowfullscreen>
                                </iframe>
                                <div class="mt-3 flex items-center justify-end">
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $vMapQuery }}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                                        <span>{{ __('Open in Google Maps') }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- TAB 5: Customer Reviews & Ratings --}}
                    <div x-show="activeTab === 'reviews'" class="space-y-6" style="display: none;">
                        <div class="surface p-6 sm:p-7 space-y-6" id="vendor-reviews-section">
                            {{-- Header --}}
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                                        <span class="text-amber-400">★</span>
                                        <span>{{ __('Organizer Ratings & Reviews') }}</span>
                                    </h2>
                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ __('Direct feedback from verified customers who booked events hosted by') }} {{ $vendor->business_name }}.
                                    </p>
                                </div>

                                @if($vendorReviewsCount > 0)
                                    <div class="flex items-center gap-3 bg-amber-50/80 border border-amber-200/80 px-4 py-2 rounded-2xl shrink-0">
                                        <div class="text-3xl font-extrabold text-amber-900">
                                            {{ number_format($vendorAverageRating, 1) }}
                                        </div>
                                        <div>
                                            <div class="flex text-amber-400 text-sm">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span>{{ $i <= round($vendorAverageRating) ? '★' : '☆' }}</span>
                                                @endfor
                                            </div>
                                            <p class="text-[11px] font-semibold text-amber-800">
                                                {{ $vendorReviewsCount }} {{ $vendorReviewsCount === 1 ? __('review') : __('reviews') }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Rating Breakdown Grid --}}
                            @if($vendorReviewsCount > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/60 p-4 rounded-xl border border-slate-100">
                                    <div class="space-y-1.5">
                                        @for($star = 5; $star >= 1; $star--)
                                            @php
                                                $cnt = $vendorRatingCounts[$star] ?? 0;
                                                $pct = $vendorReviewsCount > 0 ? round(($cnt / $vendorReviewsCount) * 100) : 0;
                                            @endphp
                                            <div class="flex items-center gap-2 text-xs">
                                                <span class="w-8 text-slate-600 font-bold shrink-0">{{ $star }} ★</span>
                                                <div class="flex-1 h-2 bg-slate-200 rounded-full overflow-hidden">
                                                    <div class="h-full bg-amber-400 rounded-full" style="width: {{ $pct }}%"></div>
                                                </div>
                                                <span class="w-10 text-right text-slate-400 font-mono text-[11px]">{{ $cnt }}</span>
                                            </div>
                                        @endfor
                                    </div>
                                    <div class="flex flex-col justify-center items-center text-center p-3 border-t md:border-t-0 md:border-l border-slate-200/70">
                                        <span class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mb-1.5">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        </span>
                                        <p class="text-xs font-bold text-slate-800">{{ __('100% Verified Customer Reviews') }}</p>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-tight">{{ __('Only customers who have confirmed event reservations with this organizer can post reviews.') }}</p>
                                    </div>
                                </div>
                            @endif

                            {{-- Write / Update Review Box --}}
                            <div class="border border-slate-200 rounded-2xl p-5 bg-white shadow-2xs">
                                @auth
                                    @if($canReviewVendor)
                                        <div x-data="{
                                            rating: {{ old('rating', $userVendorReview?->rating ?? 5) }},
                                            isEditing: {{ $userVendorReview ? 'false' : 'true' }},
                                        }">
                                            @if($userVendorReview)
                                                <div x-show="!isEditing" class="space-y-3">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center gap-2">
                                                            <span class="badge bg-emerald-100 text-emerald-800 text-xs font-bold">{{ __('Your Verified Review') }}</span>
                                                            <span class="text-amber-400 text-sm">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    {{ $i <= $userVendorReview->rating ? '★' : '☆' }}
                                                                @endfor
                                                            </span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" @click="isEditing = true" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                                                                {{ __('Edit Review') }}
                                                            </button>
                                                            <span class="text-slate-300">|</span>
                                                            <form method="POST" action="{{ route('vendors.reviews.destroy', [$vendor->slug, $userVendorReview->id]) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete your review?') }}')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="text-xs font-semibold text-red-600 hover:text-red-700">
                                                                    {{ __('Delete') }}
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    @if($userVendorReview->review)
                                                        <p class="text-sm text-slate-700 italic bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                                                            &ldquo;{{ $userVendorReview->review }}&rdquo;
                                                        </p>
                                                    @endif
                                                </div>
                                            @endif

                                            <form method="POST" action="{{ route('vendors.reviews.store', $vendor->slug) }}" x-show="isEditing" class="space-y-4">
                                                @csrf
                                                <div class="flex items-center justify-between">
                                                    <h3 class="text-sm font-bold text-slate-900">
                                                        {{ $userVendorReview ? __('Update Your Review') : __('Write an Organizer Review') }}
                                                    </h3>
                                                    @if($userVendorReview)
                                                        <button type="button" @click="isEditing = false" class="text-xs text-slate-500 hover:text-slate-700">
                                                            {{ __('Cancel') }}
                                                        </button>
                                                    @endif
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Overall Experience Rating') }}</label>
                                                    <div class="flex items-center gap-1.5">
                                                        @for($star = 1; $star <= 5; $star++)
                                                            <button type="button" @click="rating = {{ $star }}" class="text-2xl transition-transform hover:scale-110 focus:outline-hidden" :class="rating >= {{ $star }} ? 'text-amber-400' : 'text-slate-300'">
                                                                ★
                                                            </button>
                                                        @endfor
                                                        <input type="hidden" name="rating" :value="rating">
                                                        <span class="ml-2 text-xs font-bold text-slate-600" x-text="rating + ' / 5 ' + (rating === 5 ? '{{ __('Excellent') }}' : (rating === 4 ? '{{ __('Great') }}' : (rating === 3 ? '{{ __('Good') }}' : (rating === 2 ? '{{ __('Fair') }}' : '{{ __('Poor') }}'))))"></span>
                                                    </div>
                                                    @error('rating')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Your Feedback (Optional)') }}</label>
                                                    <textarea name="review" rows="3" class="input-field text-sm" placeholder="{{ __('How was your experience with this organizer? Organization, punctuality, venue, communication...') }}">{{ old('review', $userVendorReview?->review) }}</textarea>
                                                    @error('review')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                                                </div>

                                                <div class="flex items-center justify-between pt-1">
                                                    <span class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                        {{ __('Posting as Verified Customer') }}
                                                    </span>
                                                    <button type="submit" class="btn-emerald text-xs px-5 py-2 font-bold shadow-xs">
                                                        {{ $userVendorReview ? __('Update Review') : __('Submit Review') }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    @else
                                        <div class="flex items-start gap-3 text-slate-600 text-xs py-1">
                                            <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-slate-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-bold text-slate-800">{{ __('Verified Customer Reviews') }}</p>
                                                <p class="text-slate-500 mt-0.5 leading-relaxed">
                                                    {{ $vendorReviewEligibilityMessage }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="flex items-center justify-between gap-4 text-xs py-1">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            </div>
                                            <p class="text-slate-600">
                                                {{ __('Booked an event with this organizer? Sign in to submit your verified review.') }}
                                            </p>
                                        </div>
                                        <a href="{{ route('login') }}" class="btn-secondary text-xs px-3.5 py-1.5 shrink-0">
                                            {{ __('Login') }}
                                        </a>
                                    </div>
                                @endauth
                            </div>

                            {{-- Reviews List --}}
                            <div class="space-y-4 pt-2">
                                <h3 class="text-sm font-bold text-slate-900 flex items-center justify-between">
                                    <span>{{ __('Customer Reviews') }} ({{ $vendorReviewsCount }})</span>
                                </h3>

                                @if($vendor->reviews->isNotEmpty())
                                    <div class="divide-y divide-slate-100">
                                        @foreach($vendor->reviews as $r)
                                            <div class="py-4 first:pt-0 last:pb-0 space-y-2">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-bold text-xs flex items-center justify-center shadow-2xs">
                                                            {{ strtoupper(substr($r->user->name ?? 'User', 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-xs font-bold text-slate-900">{{ $r->user->name ?? __('Verified Guest') }}</span>
                                                                @if($r->is_verified_customer)
                                                                    <span class="inline-flex items-center gap-0.5 text-[10px] font-semibold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                                                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                                        {{ __('Verified Customer') }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <p class="text-[11px] text-slate-400 mt-0.5">
                                                                {{ $r->created_at->diffForHumans() }}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-2">
                                                        <div class="flex text-amber-400 text-xs">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <span>{{ $i <= $r->rating ? '★' : '☆' }}</span>
                                                            @endfor
                                                        </div>
                                                        @if(auth()->check() && (auth()->id() === $r->user_id || auth()->user()->isAdmin()))
                                                            <form method="POST" action="{{ route('vendors.reviews.destroy', [$vendor->slug, $r->id]) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this review?') }}')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="text-slate-400 hover:text-red-600 transition-colors p-1" title="{{ __('Delete Review') }}">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if($r->review)
                                                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed pl-12">
                                                        {{ $r->review }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                                        <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-2 text-base">
                                            ★
                                        </div>
                                        <p class="text-xs font-bold text-slate-700">{{ __('No reviews yet') }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Be the first verified customer to share your experience with this organizer!') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
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
                                <h3 class="text-sm font-bold text-slate-900">{{ __('Verified Partner') }}</h3>
                                <p class="text-xs text-slate-500">{{ __('Official Huddle Event Host') }}</p>
                            </div>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            {{ $vendor->business_name }} {{ __('is verified for identity, organizer licensing, and ticket delivery safety.') }}
                        </p>
                    </div>

                    {{-- Contact & Social Channels --}}
                    <div class="surface p-6">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 mb-4">{{ __('Direct Connect') }}</h3>
                        <div class="space-y-3">
                            @if($vendor->website)
                                <a href="{{ $vendor->website }}" target="_blank" rel="noopener" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-800 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <span class="text-emerald-600">🌐</span> {{ __('Official Website') }}
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
                                {{ __('Operating Schedule') }}
                            </h3>
                            <div class="space-y-2">
                                @php $today = now()->format('l'); @endphp
                                @foreach($vendor->opening_hours as $row)
                                    <div class="flex justify-between items-center text-xs {{ ($row['day'] ?? '') === $today ? 'font-bold text-emerald-800 bg-emerald-50 p-1.5 rounded' : 'text-slate-600' }}">
                                        <span>{{ __($row['day'] ?? 'Day') }}</span>
                                        <span>
                                            @if(!empty($row['closed']))
                                                <span class="text-red-500 font-semibold">{{ __('Closed') }}</span>
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

    {{-- Share Vendor Profile Modal --}}
    <div id="vendorShareModal"
         x-show="shareModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity"
             @click="shareModalOpen = false"
             onclick="window.closeVendorShareModal()"
             x-show="shareModalOpen"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="shareModalOpen"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 p-6">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white font-black flex items-center justify-center text-lg shadow-sm">
                            {{ strtoupper(substr($vendor->business_name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 leading-tight" id="modal-title">
                                {{ __('Share Organizer Profile') }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $vendor->business_name }}</p>
                        </div>
                    </div>
                    <button type="button" @click="shareModalOpen = false" onclick="window.closeVendorShareModal()" class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Copy Link Box --}}
                <div class="mt-5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">{{ __('Copy Profile Link') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="text"
                               readonly
                               value="{{ route('vendors.show', $vendor->slug) }}"
                               id="vendorShareInput"
                               class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono text-slate-700 select-all focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <button type="button"
                                id="vendorCopyBtn"
                                data-copy-url="{{ route('vendors.share', ['slug' => $vendor->slug, 'channel' => 'copy']) }}"
                                data-direct-url="{{ route('vendors.show', $vendor->slug) }}"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm transition-all hover:-translate-y-0.5 flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            <span class="vendor-copy-label">{{ __('Copy') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Device Share (Web Share API) --}}
                <div class="mt-4" id="vendorNativeShareContainer" style="display: none;">
                    <button type="button"
                            id="vendorNativeShareBtn"
                            class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition-colors flex items-center justify-center gap-2 border border-slate-200 cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>{{ __('Share via Device...') }}</span>
                    </button>
                </div>

                {{-- Social & Messaging Channels --}}
                <div class="mt-5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2.5">{{ __('Share to Social & Messaging') }}</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        @php
                            $vendorChannels = [
                                'whatsapp' => ['label' => __('WhatsApp'), 'icon' => '💬', 'color' => 'bg-emerald-600 hover:bg-emerald-700 text-white'],
                                'x' => ['label' => __('X'), 'icon' => '𝕏', 'color' => 'bg-slate-900 hover:bg-black text-white'],
                                'facebook' => ['label' => __('Facebook'), 'icon' => '👥', 'color' => 'bg-blue-600 hover:bg-blue-700 text-white'],
                                'linkedin' => ['label' => __('LinkedIn'), 'icon' => 'in', 'color' => 'bg-sky-700 hover:bg-sky-800 text-white'],
                                'telegram' => ['label' => __('Telegram'), 'icon' => '✈️', 'color' => 'bg-sky-500 hover:bg-sky-600 text-white'],
                                'email' => ['label' => __('Email'), 'icon' => '✉️', 'color' => 'bg-slate-700 hover:bg-slate-800 text-white'],
                            ];
                        @endphp
                        @foreach($vendorChannels as $channelKey => $chan)
                            <a href="{{ route('vendors.share', ['slug' => $vendor->slug, 'channel' => $channelKey]) }}"
                               target="_blank"
                               rel="noopener"
                               class="flex items-center gap-2 p-2.5 rounded-xl text-xs font-semibold {{ $chan['color'] }} shadow-sm transition-all hover:-translate-y-0.5 justify-center">
                                <span>{{ $chan['icon'] }}</span>
                                <span>{{ $chan['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Statistics Strip --}}
                <div class="mt-6 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400">
                    <span>{{ __('Total Shares') }}: {{ array_sum($shareCounts ?? []) }}</span>
                    <div class="flex items-center gap-3">
                        @foreach($vendorChannels as $channelKey => $chan)
                            <span>{{ $chan['label'] }}: {{ (int)($shareCounts[$channelKey] ?? 0) }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
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
            if (e.key === 'Escape') {
                closeLightbox();
                window.closeVendorShareModal();
            }
        });

        window.openVendorShareModal = function() {
            const modal = document.getElementById('vendorShareModal');
            if (modal) {
                modal.style.display = 'block';
            }
        };

        window.closeVendorShareModal = function() {
            const modal = document.getElementById('vendorShareModal');
            if (modal) {
                modal.style.display = 'none';
            }
        };

        // Native device share for vendor profile
        const vendorNativeShareBtn = document.getElementById('vendorNativeShareBtn');
        const vendorNativeShareContainer = document.getElementById('vendorNativeShareContainer');
        if (navigator.share && vendorNativeShareBtn && vendorNativeShareContainer) {
            vendorNativeShareContainer.style.display = 'block';
            vendorNativeShareBtn.addEventListener('click', async function () {
                try {
                    await navigator.share({
                        title: {{ Js::from($vendor->business_name) }},
                        text: {{ Js::from(__('Check out :vendor on Huddle', ['vendor' => $vendor->business_name])) }},
                        url: {{ Js::from(route('vendors.show', $vendor->slug)) }}
                    });
                } catch (err) {}
            });
        }

        // Copy vendor profile link
        document.getElementById('vendorCopyBtn')?.addEventListener('click', async function () {
            const url = this.getAttribute('data-copy-url');
            const directUrl = this.getAttribute('data-direct-url') || window.location.href;
            const label = this.querySelector('.vendor-copy-label') || this;
            try {
                await navigator.clipboard.writeText(directUrl);
                label.textContent = {{ Js::from(__('Copied!')) }};
                setTimeout(() => {
                    label.textContent = {{ Js::from(__('Copy')) }};
                }, 2500);

                if (url) {
                    fetch(url, { headers: { 'Accept': 'application/json' } }).catch(() => {});
                }
            } catch (e) {
                const tempInput = document.createElement('input');
                tempInput.value = directUrl;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                label.textContent = {{ Js::from(__('Copied!')) }};
                setTimeout(() => {
                    label.textContent = {{ Js::from(__('Copy')) }};
                }, 2500);
            }
        });
    </script>
    @endpush
</x-app-layout>
