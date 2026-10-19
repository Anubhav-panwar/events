<x-app-layout>
    @push('meta')
        <title>{{ $event->title }} — {{ __('Huddle Events') }}</title>
        <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 180) }}">
        <meta property="og:title" content="{{ $event->title }}">
        <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 180) }}">
        <meta property="og:type" content="event">
        <meta property="og:url" content="{{ route('events.show', $event->slug) }}">
        @if($event->media->first())
            <meta property="og:image" content="{{ $event->media->first()->url }}">
        @endif
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $event->title }}">
        <meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 180) }}">
        @if($event->media->first())
            <meta name="twitter:image" content="{{ $event->media->first()->url }}">
        @endif
    @endpush

    @php
        $isCalendarEligible = $event->isCalendarEligible();
        $eventDate = \Carbon\Carbon::parse($event->event_date);
        $startTimeStr = $event->start_time instanceof \Carbon\Carbon ? $event->start_time->format('H:i') : (string)$event->start_time;
        $endTimeStr = $event->end_time ? ($event->end_time instanceof \Carbon\Carbon ? $event->end_time->format('H:i') : (string)$event->end_time) : null;
        $startCarbon = (clone $eventDate)->setTimeFromTimeString($startTimeStr);
        $endCarbon = $endTimeStr ? (clone $eventDate)->setTimeFromTimeString($endTimeStr) : (clone $startCarbon)->addHours(2);

        $googleUrl = $isCalendarEligible ? $event->googleCalendarUrl() : null;
        $outlookUrl = $isCalendarEligible ? $event->outlookCalendarUrl() : null;
        $yahooUrl = $isCalendarEligible ? $event->yahooCalendarUrl() : null;
        $icsUrl = $isCalendarEligible ? route('events.ics', $event->slug) : null;

        $reminder = auth()->check() ? auth()->user()->eventReminders()->where('event_id', $event->id)->first() : null;
        $isSaved = auth()->check() ? $event->saves()->where('users.id', auth()->id())->exists() : false;
        $images = $event->media->where('type', 'image')->values();
        $videos = $event->media->where('type', 'video')->values();
        $totalPhotos = $images->count();
        $heroImage = $images->first();
        $heroBg = $heroImage ? $heroImage->url : 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1600&q=80';
        $imageUrls = $images->map(fn($m) => [
            'url' => $m->url,
            'caption' => $m->original_name ?? $event->title,
        ])->values()->all();

        $remainingTotal = max(($event->ticketTypes->sum('quantity') ?? 0) - ($event->ticketTypes->sum('sold') ?? 0), 0);
        if($event->capacity){ $remainingTotal = min($remainingTotal, max($event->capacity - $event->ticketTypes->sum('sold'), 0)); }
        $channels = [
            'whatsapp' => ['label'=>__('WhatsApp'),'icon'=>'💬','color'=>'bg-emerald-600 hover:bg-emerald-700'],
            'x' => ['label'=>__('X'),'icon'=>'𝕏','color'=>'bg-slate-900 hover:bg-black'],
            'facebook' => ['label'=>__('Facebook'),'icon'=>'👥','color'=>'bg-blue-600 hover:bg-blue-700'],
            'linkedin' => ['label'=>__('LinkedIn'),'icon'=>'in','color'=>'bg-sky-700 hover:bg-sky-800'],
            'telegram' => ['label'=>__('Telegram'),'icon'=>'✈️','color'=>'bg-sky-500 hover:bg-sky-600'],
            'email' => ['label'=>__('Email'),'icon'=>'✉️','color'=>'bg-slate-700 hover:bg-slate-800'],
        ];
        $hasShareRoute = \Illuminate\Support\Facades\Route::has('events.share');
        $hasIcsRoute = \Illuminate\Support\Facades\Route::has('events.ics');
        $hasSaveRoute = \Illuminate\Support\Facades\Route::has('events.save');
        $hasUnsaveRoute = \Illuminate\Support\Facades\Route::has('events.unsave');
        $hasReminderStoreRoute = \Illuminate\Support\Facades\Route::has('events.reminders.store');
        $hasReminderDestroyRoute = \Illuminate\Support\Facades\Route::has('events.reminders.destroy');
        $hasOrdersBuyRoute = \Illuminate\Support\Facades\Route::has('orders.buy');
    @endphp

    {{-- Interactive Hero & Media Showcase --}}
    <div x-data="{
        activeIdx: 0,
        photos: {{ Js::from($imageUrls) }},
        get currentPhoto() {
            return this.photos.length > 0 ? this.photos[this.activeIdx]?.url : '{{ $heroBg }}';
        },
        next() {
            if (this.photos.length > 1) {
                this.activeIdx = (this.activeIdx + 1) % this.photos.length;
            }
        },
        prev() {
            if (this.photos.length > 1) {
                this.activeIdx = (this.activeIdx - 1 + this.photos.length) % this.photos.length;
            }
        },
        openLightboxAt(idx) {
            window.openLightboxIndex(idx);
        }
    }" class="relative bg-slate-950 overflow-hidden group">
        {{-- Background active image --}}
        <div class="relative h-80 sm:h-96 md:h-[420px] w-full overflow-hidden">
            <img :src="currentPhoto" class="w-full h-full object-cover transition-all duration-500 ease-out" alt="{{ $event->title }}">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/40 to-slate-900/30"></div>

            {{-- Prev / Next Controls on Hero --}}
            <template x-if="photos.length > 1">
                <div class="absolute inset-y-0 inset-x-4 flex items-center justify-between pointer-events-none z-20">
                    <button @click="prev()" type="button" aria-label="{{ __('Previous photo') }}" class="pointer-events-auto p-2.5 rounded-full bg-slate-900/70 hover:bg-slate-900 text-white shadow-lg backdrop-blur-sm border border-white/15 transition-all hover:scale-110">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button @click="next()" type="button" aria-label="{{ __('Next photo') }}" class="pointer-events-auto p-2.5 rounded-full bg-slate-900/70 hover:bg-slate-900 text-white shadow-lg backdrop-blur-sm border border-white/15 transition-all hover:scale-110">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </template>

            {{-- Top Badges: Photo Counter & Fullscreen button --}}
            <div class="absolute top-4 right-4 z-20 flex items-center gap-2">
                <template x-if="photos.length > 0">
                    <span class="px-3 py-1.5 rounded-full bg-slate-900/80 backdrop-blur-md text-white text-xs font-semibold border border-white/20 shadow-md flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span x-text="(activeIdx + 1) + ' / ' + photos.length"></span>
                    </span>
                </template>
                <button type="button" @click="openLightboxAt(activeIdx)" class="px-3 py-1.5 rounded-full bg-white/90 hover:bg-white text-slate-900 text-xs font-bold shadow-md transition-all hover:scale-105 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    <span>{{ __('Full View') }}</span>
                </button>
            </div>

            {{-- Hero Title and Info --}}
            <div class="relative app-content h-full flex flex-col justify-end pb-8 z-10">
                <div class="max-w-3xl">
                    <div class="flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-slate-900/80 text-emerald-300 border-emerald-500/30">{{ strtoupper($event->event_type === 'free' ? __('FREE') : ($event->event_type === 'paid' ? __('PAID') : __($event->event_type))) }}</span>
                        @if($event->is_featured)
                            <span class="px-3 py-1 rounded-full bg-amber-400 text-amber-950 text-xs font-bold shadow">{{ __('Featured') }}</span>
                        @endif
                        @if(!$event->is_date_confirmed)
                            <span class="px-3 py-1 rounded-full bg-amber-500/90 text-white text-xs font-bold shadow flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                {{ __('Schedule Tentative') }}
                            </span>
                        @endif
                        @if(isset($reviewsCount) && $reviewsCount > 0)
                            <a href="#reviews-section" class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-500/90 hover:bg-amber-500 text-white text-xs font-bold shadow transition-all">
                                <span>★</span>
                                <span>{{ number_format($averageRating, 1) }}</span>
                                <span class="text-white/80 font-normal">({{ $reviewsCount }})</span>
                            </a>
                        @endif
                        @if($event->category)
                            <span class="badge bg-emerald-600 text-white border-none">{{ __($event->category->name) }}</span>
                        @endif
                    </div>
                    <h1 class="text-2xl md:text-4xl font-bold text-white leading-tight tracking-tight">{{ __($event->title) }}</h1>
                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1.5 text-slate-200 text-sm font-medium">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $event->event_date?->translatedFormat('l, M j, Y') ?? $event->event_date?->format('l, M j, Y') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $startTimeStr }}{{ $endTimeStr ? ' – '.$endTimeStr : '' }}
                        </span>
                        @if($event->venue_name || $event->city)
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                {{ __($event->venue_name) ?: (__($event->city) ?: __('TBA')) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Dynamic Thumbnail Selector Bar (when multiple images exist) --}}
        <template x-if="photos.length > 1">
            <div class="bg-slate-900 border-t border-slate-800 px-4 py-3">
                <div class="app-content max-w-6xl flex items-center gap-3 overflow-x-auto hide-scrollbar">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 shrink-0 mr-1">
                        {{ __('Gallery:') }}
                    </span>
                    <template x-for="(p, idx) in photos" :key="idx">
                        <button type="button"
                                @click="activeIdx = idx"
                                class="relative w-16 h-12 sm:w-20 sm:h-14 rounded-lg overflow-hidden border-2 shrink-0 transition-all cursor-pointer"
                                :class="activeIdx === idx ? 'border-emerald-400 ring-2 ring-emerald-400/40 scale-105 shadow-md' : 'border-slate-700 opacity-60 hover:opacity-100'">
                            <img :src="p.url" class="w-full h-full object-cover" :alt="p.caption">
                        </button>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <section class="page-section">
        <div class="app-content max-w-6xl">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Event Info Grid --}}
                    <div class="surface p-6">
                        <h2 class="text-xl font-bold text-slate-900 mb-5">{{ __('Event Details') }}</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded border border-slate-200 shadow-sm">
                                <div class="w-9 h-9 bg-white border border-slate-200 rounded flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wide">{{ __('Day & Time') }}</p>
                                    <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $event->event_date?->translatedFormat('l, M j, Y') ?? $event->event_date?->format('l, M j, Y') }}</p>
                                    <p class="text-sm text-slate-600">{{ $startTimeStr }}{{ $endTimeStr ? ' – '.$endTimeStr : '' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded border border-slate-200 shadow-sm">
                                <div class="w-9 h-9 bg-white border border-slate-200 rounded flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wide">{{ __('Where') }}</p>
                                    <p class="text-sm font-bold text-slate-900 mt-0.5">{{ __($event->venue_name) ?: (__($event->city) ?: __('TBA')) }}</p>
                                    @if($event->address)
                                        <p class="text-sm text-slate-600">{{ __($event->address) }}</p>
                                    @endif
                                    @if($event->city)
                                        <p class="text-sm text-slate-500">{{ __($event->city) }}{{ $event->country ? ', '.__($event->country) : '' }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded border border-slate-200 shadow-sm">
                                <div class="w-9 h-9 bg-white border border-slate-200 rounded flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0v4H7v-4m10 0H7"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wide">{{ __('For Whom') }}</p>
                                    <p class="text-sm text-slate-900 mt-0.5 font-bold">{{ ($event->audience && count($event->audience)) ? implode(', ', array_map(fn($a) => __($a), $event->audience)) : __('Everyone welcome') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded border border-slate-200 shadow-sm">
                                <div class="w-9 h-9 bg-white border border-slate-200 rounded flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wide">{{ __('Category / Tags') }}</p>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @if($event->category)
                                            <span class="badge text-xs">{{ __($event->category->name) }}</span>
                                        @endif
                                        @if($event->tags && count($event->tags))
                                            @foreach($event->tags as $tag)
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full text-xs">{{ __($tag) }}</span>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="text-slate-700 leading-relaxed">{{ __($event->description) }}</p>
                    </div>

                    {{-- Media Gallery --}}
                    @if($event->media->count() > 0)
                        <div class="surface p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900">{{ __('Photos & Videos') }}</h2>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ __('Click any image to view in high resolution') }}</p>
                                </div>
                                <span class="badge bg-slate-100 text-slate-700 text-xs font-bold">{{ $event->media->count() }} {{ __('Media') }}</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                @foreach($event->media as $m)
                                    @if($m->type === 'video')
                                        <div class="rounded-xl border border-slate-200 overflow-hidden bg-black aspect-square flex items-center justify-center">
                                            <video class="w-full h-full object-cover" src="{{ $m->url }}" preload="metadata" playsinline controls></video>
                                        </div>
                                    @else
                                        @php
                                            $photoIndex = $images->search(fn($img) => $img->id === $m->id);
                                        @endphp
                                        <div class="media-tile aspect-square rounded-xl overflow-hidden border border-slate-200 cursor-pointer group relative shadow-sm hover:shadow-md" onclick="window.openLightboxIndex({{ $photoIndex !== false ? $photoIndex : 0 }})">
                                            <img src="{{ $m->url }}" alt="{{ $m->original_name ?? $event->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center">
                                                <svg class="w-7 h-7 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Google Maps Location Display --}}
                    @if(($event->latitude && $event->longitude) || $event->address || $event->city)
                        @php
                            $mapQuery = ($event->latitude && $event->longitude)
                                ? "{$event->latitude},{$event->longitude}"
                                : urlencode(trim(($event->address ?? '') . ' ' . ($event->city ?? '') . ' ' . ($event->country ?? '')));
                            $directionsUrl = "https://www.google.com/maps/dir/?api=1&destination={$mapQuery}";
                            $googleMapsUrl = "https://www.google.com/maps/search/?api=1&query={$mapQuery}";
                        @endphp
                        <div class="surface p-6 space-y-4" id="event-location-card">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                                <div>
                                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                        <span>{{ __('Venue & Location') }}</span>
                                    </h2>
                                    @if($event->venue_name)
                                        <p class="text-sm font-semibold text-slate-800 mt-1">{{ __($event->venue_name) }}</p>
                                    @endif
                                    @if($event->address || $event->city)
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            {{ __($event->address ?? '') }}@if($event->address && $event->city), @endif{{ __($event->city ?? '') }}@if($event->country), {{ __($event->country) }}@endif
                                        </p>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="{{ $googleMapsUrl }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-all flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        <span>{{ __('Open in Google Maps') }}</span>
                                    </a>
                                    <a href="{{ $directionsUrl }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                        <span>{{ __('Get Directions') }}</span>
                                    </a>
                                </div>
                            </div>

                            <div class="relative w-full h-64 rounded-xl overflow-hidden border border-slate-200 shadow-inner">
                                <iframe
                                    class="w-full h-full border-0"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://maps.google.com/maps?q={{ $mapQuery }}&z=15&output=embed"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        </div>
                    @endif

                    {{-- Save, Calendar & Share --}}
                    <div class="surface p-6 space-y-5">
                        {{-- Calendar Integration --}}
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ __('Save to Calendar') }}
                                </h3>
                                @if($isCalendarEligible)
                                    <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ __('Date Confirmed') }}
                                    </span>
                                @else
                                    <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        {{ __('Date Tentative') }}
                                    </span>
                                @endif
                            </div>

                            @if($isCalendarEligible)
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                                    {{-- Google Calendar --}}
                                    <a href="{{ $googleUrl }}" target="_blank" rel="noopener"
                                       class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-800 text-xs font-semibold shadow-sm transition-all hover:-translate-y-0.5">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 002 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/></svg>
                                        <span>{{ __('Google Calendar') }}</span>
                                    </a>

                                    {{-- Apple Calendar / Native (.ICS) --}}
                                    <a href="{{ $icsUrl }}" download
                                       class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-800 text-xs font-semibold shadow-sm transition-all hover:-translate-y-0.5"
                                       title="{{ __('Compatible with Apple Calendar on macOS, iOS, Outlook and Android') }}">
                                        <svg class="w-4 h-4 text-slate-900 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.36c.64-.78 1.08-1.86.96-2.95-1 .04-2.16.66-2.84 1.45-.6.69-1.12 1.79-.98 2.86 1.12.09 2.22-.58 2.86-1.36z"/></svg>
                                        <span>{{ __('Apple Calendar / iCal') }}</span>
                                    </a>

                                    {{-- Outlook Live --}}
                                    <a href="{{ $outlookUrl }}" target="_blank" rel="noopener"
                                       class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-800 text-xs font-semibold shadow-sm transition-all hover:-translate-y-0.5">
                                        <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span>{{ __('Outlook Web') }}</span>
                                    </a>

                                    {{-- Yahoo Calendar --}}
                                    <a href="{{ $yahooUrl }}" target="_blank" rel="noopener"
                                       class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-800 text-xs font-semibold shadow-sm transition-all hover:-translate-y-0.5">
                                        <span class="w-4 h-4 flex items-center justify-center font-black text-purple-600 text-xs">Y!</span>
                                        <span>{{ __('Yahoo Calendar') }}</span>
                                    </a>
                                </div>
                            @else
                                <div class="rounded-xl border border-amber-200/80 bg-amber-50/70 p-4 flex items-start gap-3">
                                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <div>
                                        <h4 class="text-xs font-bold text-amber-950 uppercase tracking-wide">{{ __('Schedule Pending Confirmation') }}</h4>
                                        <p class="text-xs text-amber-800 mt-0.5 leading-relaxed">
                                            {{ __('Calendar integration (Google Calendar & .ICS files) is unavailable because this event\'s date and time are not yet confirmed by the organizer.') }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="border-t border-slate-100 pt-5">
                            {{-- Share --}}
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                    {{ __('Share with Friends') }}
                                </h3>
                                <button type="button"
                                        id="nativeShareBtn"
                                        style="display: none;"
                                        class="text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1 rounded-lg border border-emerald-200 transition-colors flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span>{{ __('Device Share') }}</span>
                                </button>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @foreach($channels as $key => $channel)
                                    @php
                                        $channelShareUrl = $hasShareRoute ? route('events.share', ['slug' => $event->slug, 'channel' => $key]) : url('/e/'.$event->slug.'/share/'.$key);
                                    @endphp
                                    <a href="{{ $channelShareUrl }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-white text-xs font-semibold {{ $channel['color'] }} transition-all hover:-translate-y-0.5 shadow-sm hover:shadow">
                                        <span>{{ $channel['icon'] }}</span>
                                        {{ $channel['label'] }}
                                    </a>
                                @endforeach
                                @php
                                    $copyShareUrl = $hasShareRoute ? route('events.share', ['slug' => $event->slug, 'channel' => 'copy']) : url('/e/'.$event->slug.'/share/copy');
                                @endphp
                                <button id="copyShareBtn" data-url="{{ $copyShareUrl }}" data-direct-url="{{ route('events.show', $event->slug) }}"
                                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-slate-700 text-xs font-semibold bg-slate-100 hover:bg-slate-200 transition-all border border-slate-200 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    <span class="copy-text">{{ __('Copy Link') }}</span>
                                </button>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400">
                                @foreach($channels as $key => $channel)
                                    <span>{{ $channel['label'] }}: {{ (int)($shareCounts[$key] ?? 0) }}</span>
                                @endforeach
                                <span>{{ __('Copy') }}: {{ (int)($shareCounts['copy'] ?? 0) }}</span>
                            </div>
                        </div>

                        {{-- Save / Reminder (logged-in users) --}}
                        @auth
                            <div class="border-t border-slate-100 pt-5 flex flex-wrap gap-3">
                                @if($isSaved)
                                    <form method="POST" action="{{ $hasUnsaveRoute ? route('events.unsave', $event->slug) : url('/events/'.$event->slug.'/save') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-secondary text-sm">
                                            <svg class="w-4 h-4 mr-1.5 inline" fill="currentColor" viewBox="0 0 24 24"><path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                            {{ __('Saved') }}
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ $hasSaveRoute ? route('events.save', $event->slug) : url('/events/'.$event->slug.'/save') }}">
                                        @csrf
                                        <button class="btn-primary text-sm">
                                            <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                            {{ __('Save Event') }}
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ $hasReminderStoreRoute ? route('events.reminders.store', $event->slug) : url('/events/'.$event->slug.'/reminders') }}" class="flex gap-2">
                                    @csrf
                                    <select name="minutes_before" class="input-field py-2 text-sm">
                                        <option value="60" @selected(($reminder?->minutes_before ?? null) == 60)>{{ __('1 hr before') }}</option>
                                        <option value="180" @selected(($reminder?->minutes_before ?? null) == 180)>{{ __('3 hrs before') }}</option>
                                        <option value="1440" @selected(($reminder?->minutes_before ?? 1440) == 1440)>{{ __('1 day before') }}</option>
                                        <option value="2880" @selected(($reminder?->minutes_before ?? null) == 2880)>{{ __('2 days before') }}</option>
                                    </select>
                                    <button class="btn-secondary text-sm whitespace-nowrap">{{ __('Set Reminder') }}</button>
                                </form>

                                @if($reminder)
                                    <form method="POST" action="{{ $hasReminderDestroyRoute ? route('events.reminders.destroy', $event->slug) : url('/events/'.$event->slug.'/reminders') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm font-bold text-slate-500 hover:text-red-700 transition-colors">{{ __('Remove Reminder') }}</button>
                                    </form>
                                @endif
                            </div>
                        @endauth
                    </div>

                    {{-- Ratings & Reviews Section --}}
                    <div class="surface p-6 space-y-6" id="reviews-section">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                                    <span class="text-amber-400">★</span>
                                    <span>{{ __('Ratings & Reviews') }}</span>
                                </h2>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ __('Verified feedback from attendees who booked tickets for this event.') }}
                                </p>
                            </div>

                            @if(isset($reviewsCount) && $reviewsCount > 0)
                                <div class="flex items-center gap-3 bg-amber-50/80 border border-amber-200/80 px-4 py-2 rounded-2xl shrink-0">
                                    <div class="text-3xl font-extrabold text-amber-900">
                                        {{ number_format($averageRating, 1) }}
                                    </div>
                                    <div>
                                        <div class="flex text-amber-400 text-sm">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span>{{ $i <= round($averageRating) ? '★' : '☆' }}</span>
                                            @endfor
                                        </div>
                                        <p class="text-[11px] font-semibold text-amber-800">
                                            {{ $reviewsCount }} {{ $reviewsCount === 1 ? __('review') : __('reviews') }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Rating breakdown bars (if reviews exist) --}}
                        @if(isset($reviewsCount) && $reviewsCount > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/60 p-4 rounded-xl border border-slate-100">
                                <div class="space-y-1.5">
                                    @for($star = 5; $star >= 1; $star--)
                                        @php
                                            $cnt = $ratingCounts[$star] ?? 0;
                                            $pct = $reviewsCount > 0 ? round(($cnt / $reviewsCount) * 100) : 0;
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
                                    <p class="text-xs font-bold text-slate-800">{{ __('100% Verified Attendee Reviews') }}</p>
                                    <p class="text-[11px] text-slate-500 mt-0.5 leading-tight">{{ __('Only guests with confirmed ticket reservations can rate this event.') }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Review Submission / User Review Box --}}
                        <div class="border border-slate-200 rounded-2xl p-5 bg-white shadow-2xs">
                            @auth
                                @if(isset($canReview) && $canReview)
                                    <div x-data="{
                                        rating: {{ old('rating', $userReview?->rating ?? 5) }},
                                        isEditing: {{ $userReview ? 'false' : 'true' }},
                                    }">
                                        @if($userReview)
                                            <div x-show="!isEditing" class="space-y-3">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-2">
                                                        <span class="badge bg-emerald-100 text-emerald-800 text-xs font-bold">{{ __('Your Verified Review') }}</span>
                                                        <span class="text-amber-400 text-sm">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                {{ $i <= $userReview->rating ? '★' : '☆' }}
                                                            @endfor
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <button type="button" @click="isEditing = true" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                                                            {{ __('Edit Review') }}
                                                        </button>
                                                        <span class="text-slate-300">|</span>
                                                        <form method="POST" action="{{ route('events.reviews.destroy', [$event->slug, $userReview->id]) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete your review?') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="text-xs font-semibold text-red-600 hover:text-red-700">
                                                                {{ __('Delete') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                @if($userReview->review)
                                                    <p class="text-sm text-slate-700 italic bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                                                        "{{ $userReview->review }}"
                                                    </p>
                                                @endif
                                            </div>
                                        @endif

                                        <form method="POST" action="{{ route('events.reviews.store', $event->slug) }}" x-show="isEditing" class="space-y-4">
                                            @csrf
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-sm font-bold text-slate-900">
                                                    {{ $userReview ? __('Update Your Review') : __('Write a Review') }}
                                                </h3>
                                                @if($userReview)
                                                    <button type="button" @click="isEditing = false" class="text-xs text-slate-500 hover:text-slate-700">
                                                        {{ __('Cancel') }}
                                                    </button>
                                                @endif
                                            </div>

                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Overall Rating') }}</label>
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
                                                <textarea name="review" rows="3" class="input-field text-sm" placeholder="{{ __('Share your experience, sound quality, venue vibe, highlights...') }}">{{ old('review', $userReview?->review) }}</textarea>
                                                @error('review')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                                            </div>

                                            <div class="flex items-center justify-between pt-1">
                                                <span class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                    {{ __('Posting as Verified Attendee') }}
                                                </span>
                                                <button type="submit" class="btn-emerald text-xs px-5 py-2 font-bold shadow-xs">
                                                    {{ $userReview ? __('Update Review') : __('Submit Review') }}
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
                                            <p class="font-bold text-slate-800">{{ __('Verified Attendee Reviews') }}</p>
                                            <p class="text-slate-500 mt-0.5 leading-relaxed">
                                                {{ $reviewEligibilityMessage }}
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
                                            {{ __('Attended this event? Sign in to submit your verified review.') }}
                                        </p>
                                    </div>
                                    <a href="{{ route('login') }}" class="btn-secondary text-xs px-3.5 py-1.5 shrink-0">
                                        {{ __('Login') }}
                                    </a>
                                </div>
                            @endauth
                        </div>

                        {{-- Individual Customer Reviews List --}}
                        <div class="space-y-4 pt-2">
                            <h3 class="text-sm font-bold text-slate-900 flex items-center justify-between">
                                <span>{{ __('Attendee Reviews') }} ({{ $reviewsCount ?? 0 }})</span>
                            </h3>

                            @if(isset($event->reviews) && $event->reviews->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($event->reviews as $rev)
                                        <div class="border border-slate-100 rounded-xl p-4 bg-white shadow-2xs space-y-2.5">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-500 via-teal-500 to-sky-500 flex items-center justify-center text-white text-xs font-bold shadow-xs">
                                                        {{ strtoupper(substr($rev->user?->name ?? 'A', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-sm font-bold text-slate-900">{{ $rev->user?->name ?? __('Attendee') }}</span>
                                                            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                                <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                                {{ __('Verified Attendee') }}
                                                            </span>
                                                        </div>
                                                        <p class="text-[11px] text-slate-400 mt-0.5">
                                                            {{ $rev->created_at?->diffForHumans() }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <span class="text-amber-400 text-sm">
                                                        @for($s = 1; $s <= 5; $s++)
                                                            <span>{{ $s <= $rev->rating ? '★' : '☆' }}</span>
                                                        @endfor
                                                    </span>

                                                    @if(auth()->check() && ($rev->user_id === auth()->id() || auth()->user()->isAdmin()))
                                                        <form method="POST" action="{{ route('events.reviews.destroy', [$event->slug, $rev->id]) }}" onsubmit="return confirm('{{ __('Delete this review?') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="text-slate-400 hover:text-red-600 text-xs p-1" title="{{ __('Delete review') }}">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($rev->review)
                                                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed pl-12">
                                                    {{ $rev->review }}
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 bg-slate-50/70 rounded-xl border border-dashed border-slate-200">
                                    <div class="text-2xl mb-1">💬</div>
                                    <p class="text-sm font-semibold text-slate-700">{{ __('No reviews yet') }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ __('Be the first verified attendee to rate and review this event!') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-5">
                    {{-- Organiser Card --}}
                    <div class="surface p-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">{{ __('Organiser') }}</p>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-xl flex items-center justify-center text-white font-bold text-lg shrink-0">
                                {{ strtoupper(substr($event->vendorProfile?->business_name ?? 'V', 0, 1)) }}
                            </div>
                            <div>
                                @if($event->vendorProfile && filled($event->vendorProfile->slug))
                                    <a href="{{ route('vendors.show', $event->vendorProfile->slug) }}" class="font-semibold text-emerald-700 hover:text-emerald-800 hover:underline">
                                        {{ $event->vendorProfile->business_name }}
                                    </a>
                                @else
                                    <span class="font-semibold text-slate-800">{{ $event->vendorProfile?->business_name ?? __('Event Host') }}</span>
                                @endif
                                <p class="text-xs text-slate-500 mt-0.5">{{ $event->vendorProfile?->city }}{{ $event->vendorProfile?->country ? ', '.$event->vendorProfile->country : '' }}</p>
                            </div>
                        </div>
                        @if($event->vendorProfile && filled($event->vendorProfile->slug))
                            <a href="{{ route('vendors.show', $event->vendorProfile->slug) }}" class="mt-4 block text-center text-sm text-emerald-700 hover:underline">{{ __('View Vendor Profile') }} &rarr;</a>
                        @endif
                    </div>

                    {{-- Tickets Card --}}
                    <div class="surface p-5">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            {{ __('Tickets') }}
                        </h3>

                        {{-- Availability badge --}}
                        @if($event->ticketTypes->count())
                            <div class="mb-4">
                                @if($remainingTotal <= 0)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-100 text-red-700 text-sm font-bold border border-red-200 rounded">
                                        {{ __('Event Full') }}
                                    </div>
                                @elseif($remainingTotal <= 10)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 text-amber-900 text-sm font-bold border border-amber-200 rounded">
                                        {{ __('Only :count seats left!', ['count' => $remainingTotal]) }}
                                    </div>
                                @else
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-800 text-sm font-bold border border-emerald-200 rounded">
                                        {{ __(':count seats available', ['count' => $remainingTotal]) }}
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Available Ticket Categories & Pricing Tier Selection --}}
                        @if($event->ticketTypes->count())
                            <div class="mb-4 space-y-2.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    {{ __('Available Ticket Categories') }}
                                </label>
                                <div class="space-y-2">
                                    @foreach($event->ticketTypes as $t)
                                        @php
                                            $meta = $t->tierMeta();
                                            $available = $t->isAvailable();
                                            $rem = $t->remainingQuantity();
                                        @endphp
                                        <div class="p-3.5 rounded-2xl border transition-all {{ $available ? 'border-slate-200 bg-white hover:border-slate-300 shadow-xs' : 'border-slate-100 bg-slate-50/70 opacity-70' }}">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex items-start gap-2.5 min-w-0">
                                                    <span class="text-base shrink-0 mt-0.5">{{ $meta['icon'] }}</span>
                                                    <div class="min-w-0">
                                                        <div class="flex flex-wrap items-center gap-1.5">
                                                            <p class="text-xs font-bold text-slate-900 truncate">{{ __($t->name) }}</p>
                                                            <span class="px-2 py-0.2 rounded-full text-[10px] font-bold border {{ $meta['bg_badge'] }}">
                                                                {{ $meta['label'] }}
                                                            </span>
                                                        </div>
                                                        @if($t->description)
                                                            <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">{{ __($t->description) }}</p>
                                                        @endif
                                                        <p class="text-[10px] mt-1 {{ $rem > 0 ? 'text-slate-400' : 'text-rose-600 font-bold' }}">
                                                            @if($rem > 0)
                                                                {{ $rem }} {{ __('tickets remaining') }}
                                                            @else
                                                                {{ __('Sold Out') }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <span class="text-sm font-extrabold {{ (float)$t->price <= 0 ? 'text-emerald-700' : 'text-slate-950' }}">
                                                        {{ (float)$t->price <= 0 ? __('FREE') : '$'.number_format((float)$t->price, 2).' '.($t->currency ?: 'USD') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @elseif($event->event_type === 'free')
                            <div class="mb-4 p-3.5 rounded-2xl border border-emerald-200 bg-emerald-50 text-sm text-emerald-800 font-bold text-center flex items-center justify-center gap-2">
                                <span>🎟️</span>
                                <span>{{ __('Free General Admission') }}</span>
                            </div>
                        @endif

                        @auth
                            <form method="POST" action="{{ $hasOrdersBuyRoute ? route('orders.buy', $event->slug) : url('/e/'.$event->slug.'/buy') }}"
                                  class="space-y-4"
                                  x-data="{
                                      selectedTypeId: '{{ $event->ticketTypes->firstWhere('is_active', true)?->id ?? $event->ticketTypes->first()?->id }}',
                                      quantity: 1,
                                      prices: {
                                          @foreach($event->ticketTypes as $t)
                                              '{{ $t->id }}': {{ (float)$t->price }},
                                          @endforeach
                                      },
                                      currencies: {
                                          @foreach($event->ticketTypes as $t)
                                              '{{ $t->id }}': '{{ $t->currency ?: 'USD' }}',
                                          @endforeach
                                      },
                                      getUnit() {
                                          return this.prices[this.selectedTypeId] ?? 0;
                                      },
                                      getTotal() {
                                          return (this.getUnit() * this.quantity).toFixed(2);
                                      },
                                      getCurr() {
                                          return this.currencies[this.selectedTypeId] || 'USD';
                                      }
                                  }">
                                @csrf

                                @if($event->ticketTypes->count())
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('Select Ticket Category') }}</label>
                                        <select name="ticket_type_id" x-model="selectedTypeId" class="input-field text-xs font-semibold rounded-xl bg-white">
                                            @foreach($event->ticketTypes as $t)
                                                @php
                                                    $meta = $t->tierMeta();
                                                    $isAvail = $t->isAvailable();
                                                @endphp
                                                <option value="{{ $t->id }}" @selected(!$isAvail ? false : null)>
                                                    {{ $meta['icon'] }} {{ __($t->name) }} — {{ (float)$t->price <= 0 ? __('FREE') : '$'.number_format((float)$t->price, 2).' '.($t->currency ?: 'USD') }} ({{ max($t->quantity - $t->sold, 0) }} {{ __('left') }}) @if(!$isAvail) [{{ __('Unavailable') }}] @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-xs font-bold text-slate-700">{{ __('Ticket Quantity') }}</label>
                                        <span class="text-[11px] text-slate-400 font-medium">{{ __('Max 20 per order') }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" @click="if(quantity > 1) quantity--" class="w-9 h-9 rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center text-sm">-</button>
                                        <input type="number" min="1" max="20" name="quantity" x-model.number="quantity" class="input-field text-center font-bold text-sm w-20 py-1.5 rounded-xl" required>
                                        <button type="button" @click="if(quantity < 20) quantity++" class="w-9 h-9 rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-700 hover:bg-slate-100 flex items-center justify-center text-sm">+</button>
                                    </div>
                                </div>

                                {{-- Live Price Breakdown Box --}}
                                @if($event->ticketTypes->count())
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-xs flex items-center justify-between">
                                        <span class="text-slate-500 font-medium">{{ __('Total Estimated Price:') }}</span>
                                        <span class="font-black text-slate-900 text-sm">
                                            $<span x-text="getTotal()"></span> <span x-text="getCurr()" class="text-xs font-bold text-slate-500"></span>
                                        </span>
                                    </div>
                                @endif

                                @error('buy')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
                                @error('ticket_type_id')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
                                @error('quantity')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror

                                <button class="btn-emerald w-full py-3 shadow-md shadow-emerald-950/20 font-bold flex items-center justify-center gap-2 rounded-xl"
                                    @if($event->ticketTypes->count() && $remainingTotal <= 0) disabled @endif>
                                    @if($event->event_type === 'free')
                                        <span>{{ __('Register Free') }}</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    @else
                                        <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span>{{ __('Checkout with Stripe') }}</span>
                                    @endif
                                </button>

                                <div class="pt-3 border-t border-slate-100 flex items-center justify-center gap-4 text-[11px] text-slate-500 font-medium">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        {{ __('Unique Code & QR') }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                        {{ __('Stripe Protected') }}
                                    </span>
                                </div>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn-primary w-full text-center block py-3 rounded-xl font-bold">{{ __('Login to Book Tickets') }}</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Recommended Events Section ("You May Also Like") --}}
    @if(isset($recommendedEvents) && $recommendedEvents->isNotEmpty())
        <section class="py-14 bg-slate-50/90 border-t border-slate-200" id="recommended-events-section" aria-labelledby="recommendations-heading">
            <div class="app-content">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-2.5 border border-emerald-200 shadow-xs">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>{{ __('Recommended For You') }}</span>
                        </div>
                        <h2 id="recommendations-heading" class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            {{ __('You May Also Like') }}
                        </h2>
                        <p class="text-sm sm:text-base text-slate-600 mt-1">
                            {{ __('Discover more events tailored to your interests and location.') }}
                        </p>
                    </div>

                    @if($event->category)
                        <a href="{{ route('events.index', ['category_id' => $event->category_id]) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 hover:text-emerald-800 transition-colors group self-start sm:self-auto">
                            <span>{{ __('Explore more') }} {{ __($event->category->name) }}</span>
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('events.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 hover:text-emerald-800 transition-colors group self-start sm:self-auto">
                            <span>{{ __('Explore all events') }}</span>
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endif
                </div>

                {{-- Responsive Grid: 1 col on mobile, 2 cols on tablet, 4 cols on desktop --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($recommendedEvents as $recEvent)
                        <div class="relative">
                            @if(!empty($recEvent->recommendation_reasons) && count($recEvent->recommendation_reasons) > 0)
                                <div class="mb-1 flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                    <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>{{ __($recEvent->recommendation_reasons[0]) }}</span>
                                </div>
                            @endif
                            <x-event-card :event="$recEvent" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Interactive Multi-Photo Lightbox --}}
    <div id="lightbox" class="fixed inset-0 bg-black/95 z-[300] hidden items-center justify-center p-4 select-none" role="dialog" aria-modal="true">
        {{-- Close button --}}
        <button onclick="closeLightbox()" aria-label="{{ __('Close') }}" title="{{ __('Close') }}" class="absolute top-5 right-5 text-white/80 hover:text-white bg-white/10 hover:bg-white/25 rounded-full p-2.5 transition-all z-30 shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        {{-- Lightbox Navigation Arrows --}}
        <button id="lightboxPrevBtn" onclick="lightboxPrev()" aria-label="{{ __('Previous') }}" title="{{ __('Previous') }}" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white bg-white/10 hover:bg-white/25 rounded-full p-3 transition-all z-30 shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button id="lightboxNextBtn" onclick="lightboxNext()" aria-label="{{ __('Next') }}" title="{{ __('Next') }}" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white bg-white/10 hover:bg-white/25 rounded-full p-3 transition-all z-30 shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </button>

        {{-- Center Image Stage --}}
        <div class="max-w-5xl max-h-[85vh] flex flex-col items-center justify-center" onclick="event.stopPropagation()">
            <img id="lightboxImg" src="" class="max-h-[75vh] max-w-full rounded-xl object-contain shadow-2xl transition-all duration-200" alt="">
            <div class="mt-4 flex items-center justify-between w-full px-4 text-white text-sm">
                <span id="lightboxCaption" class="text-white/80 truncate max-w-md"></span>
                <span id="lightboxCounter" class="text-xs font-bold px-3 py-1 bg-white/15 rounded-full"></span>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const galleryPhotos = {{ Js::from($imageUrls) }};
        let currentLightboxIndex = 0;

        window.openLightboxIndex = function(index) {
            if (!galleryPhotos || galleryPhotos.length === 0) return;
            currentLightboxIndex = Math.max(0, Math.min(index, galleryPhotos.length - 1));
            renderLightboxPhoto();
            const lb = document.getElementById('lightbox');
            lb.classList.remove('hidden');
            lb.classList.add('flex');
            document.body.style.overflow = 'hidden';
        };

        window.openLightbox = function(url) {
            const idx = galleryPhotos.findIndex(p => p.url === url);
            window.openLightboxIndex(idx !== -1 ? idx : 0);
        };

        window.closeLightbox = function() {
            const lb = document.getElementById('lightbox');
            lb.classList.add('hidden');
            lb.classList.remove('flex');
            document.body.style.overflow = '';
        };

        window.lightboxNext = function() {
            if (galleryPhotos.length <= 1) return;
            currentLightboxIndex = (currentLightboxIndex + 1) % galleryPhotos.length;
            renderLightboxPhoto();
        };

        window.lightboxPrev = function() {
            if (galleryPhotos.length <= 1) return;
            currentLightboxIndex = (currentLightboxIndex - 1 + galleryPhotos.length) % galleryPhotos.length;
            renderLightboxPhoto();
        };

        function renderLightboxPhoto() {
            if (!galleryPhotos[currentLightboxIndex]) return;
            const photo = galleryPhotos[currentLightboxIndex];
            const img = document.getElementById('lightboxImg');
            img.src = photo.url;
            img.alt = photo.caption || '';
            document.getElementById('lightboxCaption').textContent = photo.caption || '';
            document.getElementById('lightboxCounter').textContent = (currentLightboxIndex + 1) + ' / ' + galleryPhotos.length;

            const hideArrows = galleryPhotos.length <= 1;
            document.getElementById('lightboxPrevBtn').style.display = hideArrows ? 'none' : 'block';
            document.getElementById('lightboxNextBtn').style.display = hideArrows ? 'none' : 'block';
        }

        // Keyboard navigation for lightbox
        document.addEventListener('keydown', function(e) {
            const lb = document.getElementById('lightbox');
            if (!lb || lb.classList.contains('hidden')) return;

            if (e.key === 'Escape') {
                closeLightbox();
            } else if (e.key === 'ArrowRight') {
                lightboxNext();
            } else if (e.key === 'ArrowLeft') {
                lightboxPrev();
            }
        });

        // Click on backdrop to close
        document.getElementById('lightbox')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });

        // Native Device Share
        const nativeShareBtn = document.getElementById('nativeShareBtn');
        if (navigator.share && nativeShareBtn) {
            nativeShareBtn.style.display = 'inline-flex';
            nativeShareBtn.addEventListener('click', async function () {
                try {
                    await navigator.share({
                        title: {{ Js::from($event->title) }},
                        text: {{ Js::from(__('Check out :title on Huddle', ['title' => $event->title])) }},
                        url: {{ Js::from(route('events.show', $event->slug)) }}
                    });
                } catch (err) {}
            });
        }

        // Copy Share Link
        document.getElementById('copyShareBtn')?.addEventListener('click', async function () {
            const url = this.getAttribute('data-url');
            const directUrl = this.getAttribute('data-direct-url') || window.location.href;
            const copySpan = this.querySelector('.copy-text') || this;
            try {
                await navigator.clipboard.writeText(directUrl);
                copySpan.textContent = {{ Js::from(__('Copied!')) }};
                setTimeout(() => {
                    copySpan.textContent = {{ Js::from(__('Copy Link')) }};
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
                copySpan.textContent = {{ Js::from(__('Copied!')) }};
                setTimeout(() => {
                    copySpan.textContent = {{ Js::from(__('Copy Link')) }};
                }, 2500);
            }
        });
    </script>
    @endpush
</x-app-layout>
