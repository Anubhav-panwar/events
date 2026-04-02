<x-app-layout>
    @push('meta')
        <meta property="og:title" content="{{ $event->title }}">
        <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 180) }}">
        <meta property="og:type" content="event">
        <meta property="og:url" content="{{ route('events.show', $event->slug) }}">
        @if($event->media->first())
            <meta property="og:image" content="{{ Storage::disk($event->media->first()->disk)->url($event->media->first()->path) }}">
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
        $heroImage = $event->media->where('type', 'image')->first();
        $heroBg = $heroImage ? Storage::disk($heroImage->disk)->url($heroImage->path) : 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1600&q=80';
        $remainingTotal = max(($event->ticketTypes->sum('quantity') ?? 0) - ($event->ticketTypes->sum('sold') ?? 0), 0);
        if($event->capacity){ $remainingTotal = min($remainingTotal, max($event->capacity - $event->ticketTypes->sum('sold'), 0)); }
        $channels = ['whatsapp' => ['label'=>'WhatsApp','icon'=>'💬','color'=>'bg-green-500'], 'facebook' => ['label'=>'Facebook','icon'=>'👥','color'=>'bg-blue-600'], 'x' => ['label'=>'X','icon'=>'𝕏','color'=>'bg-black'], 'linkedin' => ['label'=>'LinkedIn','icon'=>'in','color'=>'bg-blue-700']];
    @endphp

    {{-- Hero --}}
    <div class="relative h-72 md:h-96 overflow-hidden">
        <img src="{{ $heroBg }}" class="absolute inset-0 w-full h-full object-cover" alt="{{ $event->title }}">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/85 via-slate-900/30 to-transparent"></div>
        <div class="relative app-content h-full flex items-end pb-8">
            <div class="max-w-3xl">
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="badge">{{ strtoupper($event->event_type) }}</span>
                    @if($event->is_featured)
                        <span class="px-3 py-1 rounded-full bg-amber-400 text-amber-900 text-xs font-bold">Featured</span>
                    @endif
                    @if($event->category)
                        <span class="badge bg-sky-500/90 text-white">{{ $event->category->name }}</span>
                    @endif
                </div>
                <h1 class="text-2xl md:text-4xl font-bold text-white leading-tight">{{ $event->title }}</h1>
                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1.5 text-slate-200 text-sm">
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
                            <h2 class="text-xl font-bold text-slate-900 mb-4">Photos & Videos</h2>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($event->media as $m)
                                    @if($m->type === 'video')
                                        <video class="rounded-xl border border-slate-200 w-full aspect-video object-cover" src="{{ Storage::disk($m->disk)->url($m->path) }}" controls></video>
                                    @else
                                        <div class="media-tile aspect-square cursor-pointer" onclick="openLightbox('{{ Storage::disk($m->disk)->url($m->path) }}')">
                                            <img src="{{ Storage::disk($m->disk)->url($m->path) }}" alt="{{ $m->original_name ?? '' }}" loading="lazy">
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
                                <a href="{{ route('events.ics', $event->slug) }}" class="btn-secondary text-sm">
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
                                    <a href="{{ route('events.share', ['slug' => $event->slug, 'channel' => $key]) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-medium {{ $channel['color'] }} hover:opacity-90 transition-all hover:-translate-y-0.5 shadow-sm hover:shadow-md">
                                        <span>{{ $channel['icon'] }}</span>
                                        {{ $channel['label'] }}
                                    </a>
                                @endforeach
                                <button id="copyShareBtn" data-url="{{ route('events.share', ['slug' => $event->slug, 'channel' => 'copy']) }}"
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
                                    <form method="POST" action="{{ route('events.unsave', $event->slug) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-secondary text-sm">
                                            <svg class="w-4 h-4 mr-1.5 inline" fill="currentColor" viewBox="0 0 24 24"><path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                            Saved
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('events.save', $event->slug) }}">
                                        @csrf
                                        <button class="btn-primary text-sm">
                                            <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                            Save Event
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('events.reminders.store', $event->slug) }}" class="flex gap-2">
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
                                    <form method="POST" action="{{ route('events.reminders.destroy', $event->slug) }}">
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
                                <a href="{{ route('vendors.show', $event->vendorProfile->slug) }}" class="font-semibold text-emerald-700 hover:text-emerald-800 hover:underline">
                                    {{ $event->vendorProfile?->business_name }}
                                </a>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $event->vendorProfile?->city }}{{ $event->vendorProfile?->country ? ', '.$event->vendorProfile->country : '' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('vendors.show', $event->vendorProfile->slug) }}" class="mt-4 block text-center text-sm text-emerald-700 hover:underline">View Vendor Profile →</a>
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
                            <form method="POST" action="{{ route('orders.buy', $event->slug) }}" class="space-y-3">
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

                                <button class="btn-primary w-full"
                                    @if($event->ticketTypes->count() && $remainingTotal <= 0) disabled @endif>
                                    @if($event->event_type === 'free')
                                        Register Free
                                    @else
                                        Buy Ticket
                                    @endif
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn-primary w-full text-center block">Login to Book</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Lightbox --}}
    <div id="lightbox" class="fixed inset-0 bg-black/90 z-[200] hidden flex items-center justify-center p-4" onclick="closeLightbox()">
        <img id="lightboxImg" src="" class="max-h-[90vh] max-w-full rounded-xl object-contain" alt="">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white bg-white/20 rounded-full p-2 hover:bg-white/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    @push('scripts')
    <script>
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
        function openLightbox(src) {
            document.getElementById('lightboxImg').src = src;
            document.getElementById('lightbox').classList.remove('hidden');
            document.getElementById('lightbox').classList.add('flex');
        }
        function closeLightbox() {
            document.getElementById('lightbox').classList.add('hidden');
            document.getElementById('lightbox').classList.remove('flex');
        }
    </script>
    @endpush
</x-app-layout>
