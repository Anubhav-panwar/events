<x-app-layout>
    @push('meta')
        <meta property="og:title" content="{{ $event->title }}">
        <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 180) }}">
        <meta property="og:type" content="event">
        <meta property="og:url" content="{{ route('events.show', $event->slug) }}">
    @endpush

    <x-slot name="header">
        <div>
            <h2 class="page-title">{{ $event->title }}</h2>
            <p class="page-subtitle">{{ $event->event_date->format('M d, Y') }} • {{ $event->address }}</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content max-w-5xl space-y-6">
            <div class="surface p-6">
                <div class="text-slate-700 mb-3">{{ $event->event_date->format('Y-m-d') }} • {{ $event->start_time->format('H:i') }} {{ $event->end_time ? ' - '.$event->end_time->format('H:i') : '' }}</div>
                <div class="mb-4 text-slate-700">{{ $event->description }}</div>
                <div class="mb-4 text-slate-600">{{ $event->address }}</div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($event->media as $m)
                        @if($m->type === 'image')
                            <img class="rounded-xl border border-slate-200" src="{{ Storage::disk($m->disk)->url($m->path) }}" alt="{{ $m->original_name }}">
                        @else
                            <video class="rounded-xl border border-slate-200" src="{{ Storage::disk($m->disk)->url($m->path) }}" controls></video>
                        @endif
                    @endforeach
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    @php
                        $eventDate = \Carbon\Carbon::parse($event->event_date);
                        $startTimeStr = $event->start_time instanceof \Carbon\Carbon ? $event->start_time->format('H:i') : (string)$event->start_time;
                        $endTimeStr = $event->end_time ? ($event->end_time instanceof \Carbon\Carbon ? $event->end_time->format('H:i') : (string)$event->end_time) : null;
                        $startCarbon = (clone $eventDate)->setTimeFromTimeString($startTimeStr);
                        $endCarbon = $endTimeStr ? (clone $eventDate)->setTimeFromTimeString($endTimeStr) : (clone $startCarbon)->addHour();
                        $start = $startCarbon->utc()->format('Ymd\THis\Z');
                        $end = $endCarbon->utc()->format('Ymd\THis\Z');
                        $googleUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text='.urlencode($event->title).'&dates='.$start.'/'.$end.'&details='.urlencode(strip_tags($event->description)).'&location='.urlencode($event->address);
                    @endphp
                    <a class="btn-secondary" href="{{ route('events.ics', $event->slug) }}">Download ICS</a>
                    <a class="btn-primary" href="{{ $googleUrl }}" target="_blank" rel="noopener">Add to Google Calendar</a>
                </div>

                <div class="mt-5 flex flex-wrap gap-3 items-center">
                    <span class="text-sm text-slate-600">Share:</span>
                    @php $url = route('events.show', $event->slug); @endphp
                    <a class="text-emerald-700 text-sm hover:text-emerald-800" target="_blank" href="https://wa.me/?text={{ urlencode($event->title.' '.$url) }}">WhatsApp</a>
                    <a class="text-emerald-700 text-sm hover:text-emerald-800" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}">Facebook</a>
                    <a class="text-emerald-700 text-sm hover:text-emerald-800" target="_blank" href="https://twitter.com/intent/tweet?text={{ urlencode($event->title) }}&url={{ urlencode($url) }}">X</a>
                    <a class="text-emerald-700 text-sm hover:text-emerald-800" target="_blank" href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($url) }}">LinkedIn</a>
                    <button class="text-emerald-700 text-sm hover:text-emerald-800" onclick="navigator.clipboard.writeText('{{ $url }}')">Copy Link</button>
                </div>

                @auth
                <form method="POST" action="{{ route('events.save', $event->slug) }}" class="mt-5">
                    @csrf
                    <button class="btn-primary">Save Event</button>
                </form>
                @endauth
            </div>

            @if($event->ticketTypes->count())
                <div class="surface p-6">
                    @php
                        $remainingTotal = max(($event->ticketTypes->sum('quantity') ?? 0) - ($event->ticketTypes->sum('sold') ?? 0), 0);
                        if($event->capacity){
                            $remainingTotal = min($remainingTotal, max($event->capacity - $event->ticketTypes->sum('sold'), 0));
                        }
                    @endphp
                    <div class="mb-2">
                        @if($remainingTotal <= 0)
                            <span class="badge bg-red-100 text-red-700">Event Full</span>
                        @else
                            <span class="text-sm text-slate-600">{{ $remainingTotal }} seats left</span>
                        @endif
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-3">Buy Tickets</h3>
                    <form method="POST" action="{{ route('orders.buy', $event->slug) }}" class="flex flex-col md:flex-row md:items-end gap-3">
                        @csrf
                        <div class="flex-1">
                            <label class="block mb-1 text-sm text-slate-700">Ticket Type</label>
                            <select name="ticket_type_id" class="input-field">
                                @foreach($event->ticketTypes as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }} - {{ number_format($t->price, 2) }} {{ $t->currency }} ({{ max($t->quantity - $t->sold, 0) }} left)</option>
                                @endforeach
                            </select>
                            @error('ticket_type_id')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm text-slate-700">Quantity</label>
                            <input type="number" min="1" name="quantity" value="1" class="input-field">
                            @error('quantity')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                        </div>
                        <button class="btn-primary" @if($remainingTotal <= 0) disabled @endif>Buy Ticket</button>
                    </form>
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
