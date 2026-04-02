<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">My Tickets</h2>
            <p class="page-subtitle">Your issued event tickets with QR codes and calendar links.</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content max-w-5xl">
            @if($tickets->count())
                <div class="space-y-4">
                    @foreach($tickets as $ticket)
                        @php
                            $event = $ticket->orderItem?->ticketType?->event;
                            $type = $ticket->orderItem?->ticketType;
                        @endphp
                        <div class="surface overflow-hidden">
                            <div class="flex flex-col md:flex-row">
                                {{-- Colored Left Strip --}}
                                <div class="w-full md:w-2 bg-gradient-to-b from-emerald-500 to-teal-600 shrink-0"></div>

                                {{-- Ticket Content --}}
                                <div class="flex-1 p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                                    {{-- Event Info --}}
                                    <div class="flex-1 min-w-0">
                                        @if($event)
                                            <a href="{{ route('events.show', $event->slug) }}" class="font-bold text-lg text-slate-900 hover:text-emerald-700 transition-colors line-clamp-1">
                                                {{ $event->title }}
                                            </a>
                                            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1.5 text-sm text-slate-500">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    {{ $event->event_date?->format('M j, Y') }}
                                                </span>
                                                @if($event->venue_name ?: $event->city)
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                                        {{ $event->venue_name ?: $event->city }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="font-bold text-slate-900">Event Removed</span>
                                        @endif

                                        <div class="flex flex-wrap gap-2 mt-3">
                                            @if($type)
                                                <span class="badge">{{ $type->name }}</span>
                                            @endif
                                            @if($ticket->checked_in_at)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded bg-slate-100 text-slate-600 text-xs font-bold border border-slate-200">Checked In: {{ $ticket->checked_in_at->format('M j, H:i') }}</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">Valid</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Ticket Code + Actions --}}
                                    <div class="flex flex-col items-end gap-3 shrink-0">
                                        <div class="text-right">
                                            <p class="text-xs text-slate-400 mb-0.5">Ticket Code</p>
                                            <code class="text-sm font-mono font-semibold text-slate-700 bg-slate-100 px-3 py-1.5 rounded-lg">{{ $ticket->code }}</code>
                                        </div>
                                        <div class="flex gap-2">
                                            <a href="{{ route('account.tickets.show', $ticket) }}" class="btn-primary text-xs px-4 py-2">
                                                View QR
                                            </a>
                                            @if($event)
                                                <a href="{{ route('events.ics', $event->slug) }}" class="btn-secondary text-xs px-4 py-2 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> 
                                                    Calendar
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">{{ $tickets->links() }}</div>
            @else
                <div class="surface p-12 text-center max-w-lg mx-auto border-dashed border-2 border-slate-200 shadow-none">
                    <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">No tickets yet</h3>
                    <p class="text-slate-600 mb-8">When you register for or purchase tickets to events, they'll appear here with your QR codes.</p>
                    <a href="{{ route('events.index') }}" class="btn-primary px-8 py-3">Browse Events</a>
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
