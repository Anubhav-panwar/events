<x-app-layout :title="__('Booking Confirmation')">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200">
                        ✅ {{ __('Confirmed & Paid') }}
                    </span>
                    <span class="text-xs text-slate-400 font-mono">#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <h1 class="page-title text-2xl sm:text-3xl font-extrabold text-slate-950">
                    {{ __('Booking Confirmation') }}
                </h1>
                <p class="page-subtitle text-xs sm:text-sm text-slate-500 mt-1">
                    {{ __('Your tickets have been issued and verified. Please present your unique ticket code or QR at entry.') }}
                </p>
            </div>
            <a href="{{ route('account.tickets.index') }}" class="btn-secondary text-xs py-2 px-3.5 rounded-xl font-bold">
                {{ __('My Tickets') }} &rarr;
            </a>
        </div>
    </x-slot>

    @php
        $event = $order->event ?: $order->items->first()?->ticketType?->event;
        $allTickets = $order->items->flatMap->tickets;
    @endphp

    <section class="page-section py-8">
        <div class="app-content max-w-4xl space-y-6">

            {{-- Success Celebration Banner --}}
            <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700 text-white shadow-xl shadow-emerald-900/10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-xs font-bold tracking-wide backdrop-blur-xs">
                        <span>🎉</span>
                        <span>{{ __('Booking Successful') }}</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black tracking-tight">
                        {{ $event?->title ?? __('Your Event Tickets') }}
                    </h2>
                    @if($event)
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-emerald-100">
                            <span>📅 {{ $event->event_date?->translatedFormat('l, M j, Y') ?? __('Date TBA') }}</span>
                            <span>⏰ {{ $event->start_time instanceof \Carbon\Carbon ? $event->start_time->format('g:i A') : $event->start_time }}</span>
                            @if($event->venue_name || $event->city)
                                <span>📍 {{ $event->venue_name ?: $event->city }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="shrink-0 flex flex-wrap items-center gap-2">
                    @if($event && $event->isCalendarEligible())
                        <a href="{{ route('events.ics', $event->slug) }}" class="px-4 py-2.5 rounded-xl bg-white text-emerald-900 text-xs font-bold hover:bg-emerald-50 transition-colors shadow-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ __('Add to Calendar') }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Unique Ticket Codes Issued Card --}}
            <div class="surface p-6 sm:p-7 rounded-3xl border border-slate-200/80 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span>🎟️</span>
                            <span>{{ __('Issued Ticket Codes') }} ({{ $allTickets->count() }})</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ __('Each booked ticket has a unique code that cannot be duplicated or reused.') }}
                        </p>
                    </div>
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                        {{ __('Payment Verified') }}
                    </span>
                </div>

                <div class="space-y-4">
                    @forelse($allTickets as $idx => $ticket)
                        @php
                            $ticketType = $ticket->orderItem?->ticketType;
                            $meta = $ticketType?->tierMeta() ?? ['label' => __('Standard'), 'icon' => '🎟️', 'bg_badge' => 'bg-slate-100 text-slate-800 border-slate-200'];
                        @endphp
                        <div class="p-4 sm:p-5 rounded-2xl border border-slate-200/90 bg-slate-50/60 hover:bg-white hover:border-slate-300 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4"
                             x-data="{ copied: false }">
                            {{-- Left: Attendee & Tier Details --}}
                            <div class="space-y-1.5 flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-bold text-slate-400">#{{ $idx + 1 }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $meta['bg_badge'] }}">
                                        {{ $meta['icon'] }} {{ $meta['label'] }}
                                    </span>
                                    <h3 class="text-sm font-bold text-slate-900 truncate">
                                        {{ $ticketType?->name ?? __('Event Admission') }}
                                    </h3>
                                    @if($ticket->checked_in_at)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">
                                            ✔ {{ __('Checked In') }}
                                        </span>
                                    @else
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                            ✅ {{ __('Valid') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <span>👤 {{ __('Attendee') }}: <strong class="text-slate-800">{{ $ticket->attendee_name ?: $order->user?->name }}</strong></span>
                                    <span>•</span>
                                    <span>📅 {{ __('Issued') }}: {{ $ticket->issued_at?->translatedFormat('M j, Y H:i') ?? now()->translatedFormat('M j, Y H:i') }}</span>
                                </div>
                                @if($ticketType?->description)
                                    <p class="text-[11px] text-slate-400 italic line-clamp-1">"{{ $ticketType->description }}"</p>
                                @endif
                            </div>

                            {{-- Right: Monospace Unique Ticket Code + Copy + View QR --}}
                            <div class="flex items-center gap-2.5 shrink-0 self-start md:self-auto">
                                <div class="text-right">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">{{ __('Unique Ticket Code') }}</span>
                                    <code class="text-sm font-mono font-black text-slate-900 bg-white border border-slate-200 px-3 py-1.5 rounded-xl tracking-wider select-all shadow-2xs">
                                        {{ $ticket->code }}
                                    </code>
                                </div>

                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $ticket->code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="btn-secondary text-xs p-2 rounded-xl text-slate-600 hover:text-emerald-700 hover:border-emerald-300"
                                        title="{{ __('Copy Code') }}">
                                    <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    <span x-show="copied" class="text-emerald-600 font-bold text-[10px]">✓</span>
                                </button>

                                <a href="{{ route('account.tickets.show', $ticket) }}"
                                   class="btn-primary text-xs py-2 px-3 rounded-xl font-bold flex items-center gap-1 shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    {{ __('View QR') }}
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-500 text-xs">
                            {{ __('Your tickets are being finalized...') }}
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Order Summary & Payment Receipt Card --}}
            <div class="surface p-6 sm:p-7 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 border-b border-slate-100 pb-3">
                    {{ __('Order Summary & Payment Receipt') }}
                </h3>

                <div class="space-y-2 text-xs">
                    @foreach($order->items as $item)
                        <div class="flex items-center justify-between py-1">
                            <div>
                                <span class="font-bold text-slate-800">{{ $item->ticketType?->name ?? __('Ticket') }}</span>
                                <span class="text-slate-400">× {{ $item->quantity }}</span>
                            </div>
                            <span class="font-semibold text-slate-900">
                                ${{ number_format($item->quantity * (float)$item->unit_price, 2) }}
                            </span>
                        </div>
                    @endforeach

                    @if($order->platform_fee > 0)
                        <div class="flex items-center justify-between py-1 text-slate-500">
                            <span>{{ __('Service & Processing Fee') }}</span>
                            <span>${{ number_format($order->platform_fee, 2) }}</span>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-sm">
                        <span class="font-bold text-slate-900">{{ __('Total Amount Paid') }}</span>
                        <span class="font-black text-emerald-700 text-base">
                            ${{ number_format($order->total, 2) }} {{ $order->currency ?: 'USD' }}
                        </span>
                    </div>

                    <div class="pt-2 flex flex-wrap items-center justify-between text-[11px] text-slate-400">
                        <span>{{ __('Payment Method') }}: <strong class="text-slate-700">{{ ucfirst($order->payment_provider ?: 'Card') }}</strong></span>
                        <span>{{ __('Paid On') }}: <strong class="text-slate-700">{{ $order->paid_at?->translatedFormat('M j, Y H:i') ?? now()->translatedFormat('M j, Y H:i') }}</strong></span>
                    </div>
                </div>
            </div>

            {{-- Bottom Navigation --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                <a href="{{ route('events.show', $event->slug) }}" class="btn-secondary text-xs py-2.5 px-4 rounded-xl">
                    &larr; {{ __('Back to Event Page') }}
                </a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('account.tickets.index') }}" class="btn-primary text-xs py-2.5 px-5 rounded-xl font-bold shadow-sm">
                        {{ __('View All My Tickets') }} &rarr;
                    </a>
                </div>
            </div>

        </div>
    </section>
</x-app-layout>
