<x-app-layout>
    @push('meta')
        <meta property="og:title" content="{{ $event->title }}">
        <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 180) }}">
        <meta property="og:type" content="event">
        <meta property="og:url" content="{{ route('events.show', $event->slug) }}">
    @endpush

    
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ $event->title }}</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="card p-6">
                <div class="text-gray-700 mb-2">{{ $event->event_date->format('Y-m-d') }} • {{ $event->start_time->format('H:i') }} {{ $event->end_time ? ' - '.$event->end_time->format('H:i') : '' }}</div>
                <div class="mb-4">{{ $event->description }}</div>
                <div class="mb-4">{{ $event->address }}</div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($event->media as $m)
                        @if($m->type === 'image')
                            <img class="rounded" src="{{ Storage::disk($m->disk)->url($m->path) }}" alt="{{ $m->original_name }}">
                        @else
                            <video class="rounded" src="{{ Storage::disk($m->disk)->url($m->path) }}" controls></video>
                        @endif
                    @endforeach
                </div>
                <div class="mt-4 flex gap-2">
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
                <div class="mt-4 flex gap-2 items-center">
                    <span class="text-sm text-gray-600">Share:</span>
                    @php $url = route('events.show', $event->slug); @endphp
                    <a class="text-blue-600 text-sm" target="_blank" href="https://wa.me/?text={{ urlencode($event->title.' '.$url) }}">WhatsApp</a>
                    <a class="text-blue-600 text-sm" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}">Facebook</a>
                    <a class="text-blue-600 text-sm" target="_blank" href="https://twitter.com/intent/tweet?text={{ urlencode($event->title) }}&url={{ urlencode($url) }}">X</a>
                    <a class="text-blue-600 text-sm" target="_blank" href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($url) }}">LinkedIn</a>
                    <button class="text-blue-600 text-sm" onclick="navigator.clipboard.writeText('{{ $url }}')">Copy Link</button>
                </div>
                @auth
                <form method="POST" action="{{ route('events.save', $event->slug) }}" class="mt-4">
                    @csrf
                    <button class="btn-primary">Save Event</button>
                </form>
                @endauth
                @if($event->ticketTypes->count())
                <form method="POST" action="{{ route('orders.reserve', $event->slug) }}" class="mt-6 flex items-end gap-2">
                    @csrf
                    <div>
                        <label class="block">Ticket Type</label>
                        <select name="ticket_type_id" class="border rounded p-2">
                            @foreach($event->ticketTypes as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} - {{ $t->price }} {{ $t->currency }} ({{ max($t->quantity - $t->sold, 0) }} left)</option>
                            @endforeach
                        </select>
                        @error('ticket_type_id')<div class="text-red-600">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="block">Quantity</label>
                        <input type="number" min="1" name="quantity" value="1" class="border rounded p-2">
                        @error('quantity')<div class="text-red-600">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn-primary">Reserve</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
