<x-app-layout>
    @push('meta')
        <meta property="og:title" content="{{ $event->title }}">
        <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 180) }}">
        <meta property="og:type" content="event">
        <meta property="og:url" content="{{ route('events.show', $event->slug) }}">
        @if($event->media->first())
            <meta property="og:image" content="{{ $event->media->first()->url }}">
        @endif
    @endpush

    @php
        $eventDate = \Carbon\Carbon::parse($event->event_date);
        $startTimeStr = $event->start_time instanceof \Carbon\Carbon ? $event->start_time->format('H:i') : (string)$event->start_time;
        $endTimeStr = $event->end_time ? ($event->end_time instanceof \Carbon\Carbon ? $event->end_time->format('H:i') : (string)$event->end_time) : null;
        $startCarbon = (clone $eventDate)->setTimeFromTimeString($startTimeStr);
        $endCarbon = $endTimeStr ? (clone $eventDate)->setTimeFromTimeString($endTimeStr) : (clone $startCarbon)->addHour();
        $start = $startCarbon->utc()->format('Ymd\THis\Z');
        $end = $endCarbon->utc()->format('Ymd\THis\Z');
        $googleUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text='.urlencode($event->title).'&dates='.$start.'/'.$end.'&details='.urlencode(strip_tags($event->description)).'&location='.urlencode($event->address ?? '');
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
        $channels = ['whatsapp' => ['label'=>'WhatsApp','icon'=>'💬','color'=>'bg-green-500'], 'facebook' => ['label'=>'Facebook','icon'=>'👥','color'=>'bg-blue-600'], 'x' => ['label'=>'X','icon'=>'𝕏','color'=>'bg-black'], 'linkedin' => ['label'=>'LinkedIn','icon'=>'in','color'=>'bg-blue-700']];
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
                    <button @click="prev()" type="button" aria-label="Previous photo" class="pointer-events-auto p-2.5 rounded-full bg-slate-900/70 hover:bg-slate-900 text-white shadow-lg backdrop-blur-sm border border-white/15 transition-all hover:scale-110">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button @click="next()" type="button" aria-label="Next photo" class="pointer-events-auto p-2.5 rounded-full bg-slate-900/70 hover:bg-slate-900 text-white shadow-lg backdrop-blur-sm border border-white/15 transition-all hover:scale-110">
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
                    <span>Full View</span>
                </button>
            </div>

            {{-- Hero Title and Info --}}
            <div class="relative app-content h-full flex flex-col justify-end pb-8 z-10">
                <div class="max-w-3xl">
                    <div class="flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-slate-900/80 text-emerald-300 border-emerald-500/30">{{ strtoupper($event->event_type) }}</span>
                        @if($event->is_featured)
                            <span class="px-3 py-1 rounded-full bg-amber-400 text-amber-950 text-xs font-bold shadow">Featured</span>
                        @endif
                        @if($event->category)
                            <span class="badge bg-emerald-600 text-white border-none">{{ $event->category->name }}</span>
                        @endif
                    </div>
                    <h1 class="text-2xl md:text-4xl font-bold text-white leading-tight tracking-tight">{{ $event->title }}</h1>
                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1.5 text-slate-200 text-sm font-medium">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $event->event_date?->format('l, M j, Y') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $startTimeStr }}{{ $endTimeStr ? ' – '.$endTimeStr : '' }}
                        </span>
                        @if($event->venue_name || $event->city)
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                {{ $event->venue_name ?: $event->city }}
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
                        Gallery:
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
                        <h2 class="text-xl font-bold text-slate-900 mb-5">Event Details</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded border border-slate-200 shadow-sm">
                                <div class="w-9 h-9 bg-white border border-slate-200 rounded flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wide">Day & Time</p>
                                    <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $event->event_date?->format('l, M j, Y') }}</p>
                                    <p class="text-sm text-slate-600">{{ $startTimeStr }}{{ $endTimeStr ? ' – '.$endTimeStr : '' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded border border-slate-200 shadow-sm">
                                <div class="w-9 h-9 bg-white border border-slate-200 rounded flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wide">Where</p>
                                    <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $event->venue_name ?: ($event->city ?: 'TBA') }}</p>
                                    @if($event->address)
                                        <p class="text-sm text-slate-600">{{ $event->address }}</p>
                                    @endif
                                    @if($event->city)
                                        <p class="text-sm text-slate-500">{{ $event->city }}{{ $event->country ? ', '.$event->country : '' }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded border border-slate-200 shadow-sm">
                                <div class="w-9 h-9 bg-white border border-slate-200 rounded flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0v4H7v-4m10 0H7"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wide">For Whom</p>
                                    <p class="text-sm text-slate-900 mt-0.5 font-bold">{{ ($event->audience && count($event->audience)) ? implode(', ', $event->audience) : 'Everyone welcome' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded border border-slate-200 shadow-sm">
                                <div class="w-9 h-9 bg-white border border-slate-200 rounded flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wide">Category / Tags</p>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @if($event->category)
                                            <span class="badge text-xs">{{ $event->category->name }}</span>
                                        @endif
                                        @if($event->tags && count($event->tags))
                                            @foreach($event->tags as $tag)
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full text-xs">{{ $tag }}</span>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="text-slate-700 leading-relaxed">{{ $event->description }}</p>
                    </div>

                    {{-- Media Gallery --}}
                    @if($event->media->count() > 0)
                        <div class="surface p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900">Photos &amp; Videos</h2>
                                    <p class="text-xs text-slate-500 mt-0.5">Click any image to view in high resolution</p>
                                </div>
                                <span class="badge bg-slate-100 text-slate-700 text-xs font-bold">{{ $event->media->count() }} Media</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                @foreach($event->media as $m)
                                    @if($m->type === 'video')
                                        <div class="rounded-xl border border-slate-200 overflow-hidden bg-black aspect-square flex items-center justify-center">
                                            <video class="w-full h-full object-cover" src="{{ $m->url }}" controls></video>
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

                    {{-- Map --}}
                    @if($event->latitude && $event->longitude)
                        <div class="surface p-6">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">Location</h2>
                            <iframe class="w-full h-60 rounded-xl border border-slate-200" loading="lazy"
                                src="https://maps.google.com/maps?q={{ $event->latitude }},{{ $event->longitude }}&z=15&output=embed">
                            </iframe>
                        </div>
                    @endif

                    {{-- Save, Calendar & Share --}}
                    <div class="surface p-6 space-y-5">
                        {{-- Calendar Buttons --}}
                        <div>
                            <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Save to Calendar
                            </h3>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ $hasIcsRoute ? route('events.ics', $event->slug) : url('/e/'.$event->slug.'/ics') }}" class="btn-secondary text-sm">
                                    Download .ICS (iPhone/Android/Outlook)
                                </a>
                                <a href="{{ $googleUrl }}" target="_blank" rel="noopener" class="btn-primary text-sm">
                                    Add to Google Calendar
                                </a>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-5">
                            {{-- Share --}}
                            <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                Share with Friends
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($channels as $key => $channel)
                                    @php
                                        $channelShareUrl = $hasShareRoute ? route('events.share', ['slug' => $event->slug, 'channel' => $key]) : url('/e/'.$event->slug.'/share/'.$key);
                                    @endphp
                                    <a href="{{ $channelShareUrl }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-medium {{ $channel['color'] }} hover:opacity-90 transition-all hover:-translate-y-0.5 shadow-sm hover:shadow-md">
                                        <span>{{ $channel['icon'] }}</span>
                                        {{ $channel['label'] }}
                                    </a>
                                @endforeach
                                @php
                                    $copyShareUrl = $hasShareRoute ? route('events.share', ['slug' => $event->slug, 'channel' => 'copy']) : url('/e/'.$event->slug.'/share/copy');
                                @endphp
                                <button id="copyShareBtn" data-url="{{ $copyShareUrl }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-slate-700 text-sm font-medium bg-slate-100 hover:bg-slate-200 transition-all border border-slate-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    Copy Link
                                </button>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400">
                                @foreach($channels as $key => $channel)
                                    <span>{{ $channel['label'] }}: {{ (int)($shareCounts[$key] ?? 0) }}</span>
                                @endforeach
                                <span>Copy: {{ (int)($shareCounts['copy'] ?? 0) }}</span>
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
                                            Saved
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ $hasSaveRoute ? route('events.save', $event->slug) : url('/events/'.$event->slug.'/save') }}">
                                        @csrf
                                        <button class="btn-primary text-sm">
                                            <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                            Save Event
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ $hasReminderStoreRoute ? route('events.reminders.store', $event->slug) : url('/events/'.$event->slug.'/reminders') }}" class="flex gap-2">
                                    @csrf
                                    <select name="minutes_before" class="input-field py-2 text-sm">
                                        <option value="60" @selected(($reminder?->minutes_before ?? null) == 60)>1 hr before</option>
                                        <option value="180" @selected(($reminder?->minutes_before ?? null) == 180)>3 hrs before</option>
                                        <option value="1440" @selected(($reminder?->minutes_before ?? 1440) == 1440)>1 day before</option>
                                        <option value="2880" @selected(($reminder?->minutes_before ?? null) == 2880)>2 days before</option>
                                    </select>
                                    <button class="btn-secondary text-sm whitespace-nowrap">Set Reminder</button>
                                </form>

                                @if($reminder)
                                    <form method="POST" action="{{ $hasReminderDestroyRoute ? route('events.reminders.destroy', $event->slug) : url('/events/'.$event->slug.'/reminders') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm font-bold text-slate-500 hover:text-red-700 transition-colors">Remove Reminder</button>
                                    </form>
                                @endif
                            </div>
                        @endauth
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-5">
                    {{-- Organiser Card --}}
                    <div class="surface p-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Organiser</p>
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
                                    <span class="font-semibold text-slate-800">{{ $event->vendorProfile?->business_name ?? 'Event Host' }}</span>
                                @endif
                                <p class="text-xs text-slate-500 mt-0.5">{{ $event->vendorProfile?->city }}{{ $event->vendorProfile?->country ? ', '.$event->vendorProfile->country : '' }}</p>
                            </div>
                        </div>
                        @if($event->vendorProfile && filled($event->vendorProfile->slug))
                            <a href="{{ route('vendors.show', $event->vendorProfile->slug) }}" class="mt-4 block text-center text-sm text-emerald-700 hover:underline">View Vendor Profile →</a>
                        @endif
                    </div>

                    {{-- Tickets Card --}}
                    <div class="surface p-5">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            Tickets
                        </h3>

                        {{-- Availability badge --}}
                        @if($event->ticketTypes->count())
                            <div class="mb-4">
                                @if($remainingTotal <= 0)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-100 text-red-700 text-sm font-bold border border-red-200 rounded">
                                        Event Full
                                    </div>
                                @elseif($remainingTotal <= 10)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 text-amber-900 text-sm font-bold border border-amber-200 rounded">
                                        Only {{ $remainingTotal }} seats left!
                                    </div>
                                @else
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-800 text-sm font-bold border border-emerald-200 rounded">
                                        {{ $remainingTotal }} seats available
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Ticket type previews --}}
                        @if($event->ticketTypes->count())
                            <div class="space-y-2 mb-4">
                                @foreach($event->ticketTypes as $t)
                                    <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 bg-slate-50">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ $t->name }}</p>
                                            <p class="text-xs text-slate-500">{{ max($t->quantity - $t->sold, 0) }} left</p>
                                        </div>
                                        <span class="text-sm font-bold text-emerald-700">
                                            {{ (float)$t->price <= 0 ? 'FREE' : number_format((float)$t->price, 2).' '.($t->currency ?: 'USD') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($event->event_type === 'free')
                            <div class="mb-4 p-3 rounded border border-emerald-200 bg-emerald-50 text-sm text-emerald-700 font-bold text-center">
                                Free Registration
                            </div>
                        @endif

                        @auth
                            <form method="POST" action="{{ $hasOrdersBuyRoute ? route('orders.buy', $event->slug) : url('/e/'.$event->slug.'/buy') }}" class="space-y-3">
                                @csrf

                                @if($event->ticketTypes->count())
                                    <div>
                                        <label class="block text-xs text-slate-600 mb-1 font-medium">Ticket Type</label>
                                        <select name="ticket_type_id" class="input-field text-sm">
                                            @foreach($event->ticketTypes as $t)
                                                <option value="{{ $t->id }}">{{ $t->name }} — {{ (float)$t->price <= 0 ? 'FREE' : number_format((float)$t->price, 2).' '.($t->currency ?: 'USD') }} ({{ max($t->quantity - $t->sold, 0) }} left)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-xs text-slate-600 mb-1 font-medium">Quantity</label>
                                    <input type="number" min="1" max="20" name="quantity" value="1" class="input-field text-sm">
                                </div>

                                @error('buy')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
                                @error('ticket_type_id')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
                                @error('quantity')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror

                                <button class="btn-emerald w-full py-3 shadow-md shadow-emerald-950/20 font-bold flex items-center justify-center gap-2"
                                    @if($event->ticketTypes->count() && $remainingTotal <= 0) disabled @endif>
                                    @if($event->event_type === 'free')
                                        <span>Register Free</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    @else
                                        <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span>Checkout with Stripe</span>
                                    @endif
                                </button>

                                <div class="pt-3 border-t border-slate-100 flex items-center justify-center gap-4 text-[11px] text-slate-500 font-medium">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Instant QR Ticket
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                        Stripe Protected
                                    </span>
                                </div>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn-primary w-full text-center block py-3">Login to Book Tickets</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Interactive Multi-Photo Lightbox --}}
    <div id="lightbox" class="fixed inset-0 bg-black/95 z-[300] hidden items-center justify-center p-4 select-none" role="dialog" aria-modal="true">
        {{-- Close button --}}
        <button onclick="closeLightbox()" class="absolute top-5 right-5 text-white/80 hover:text-white bg-white/10 hover:bg-white/25 rounded-full p-2.5 transition-all z-30 shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        {{-- Lightbox Navigation Arrows --}}
        <button id="lightboxPrevBtn" onclick="lightboxPrev()" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white bg-white/10 hover:bg-white/25 rounded-full p-3 transition-all z-30 shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button id="lightboxNextBtn" onclick="lightboxNext()" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white bg-white/10 hover:bg-white/25 rounded-full p-3 transition-all z-30 shadow-lg">
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

        document.getElementById('copyShareBtn')?.addEventListener('click', async function () {
            const url = this.getAttribute('data-url');
            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data?.url) {
                    await navigator.clipboard.writeText(data.url);
                    this.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied!';
                    setTimeout(() => this.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg> Copy Link', 2500);
                }
            } catch (e) {}
        });
    </script>
    @endpush
</x-app-layout>
