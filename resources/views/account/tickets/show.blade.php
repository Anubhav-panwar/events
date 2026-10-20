<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('account.tickets.index') }}" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="page-title">{{ __('My Ticket') }}</h2>
                <p class="page-subtitle">{{ __('Present this QR code at the event check-in gate.') }}</p>
            </div>
        </div>
    </x-slot>

    @php
        $event = $ticket->orderItem?->ticketType?->event;
        $ticketType = $ticket->orderItem?->ticketType;
        $qrCode = $ticket->code;
    @endphp

    <section class="page-section">
        <div class="app-content max-w-4xl">

            @if(session('status'))
                <div class="surface border-emerald-200 bg-emerald-50 p-4 mb-5 flex items-center gap-3 text-emerald-800">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('status') }}
                </div>
            @endif

            <div class="surface overflow-hidden">
                {{-- Ticket Header Banner --}}
                <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-600 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-emerald-100 mb-1">{{ __('Official Ticket') }}</p>
                            <h2 class="text-xl md:text-2xl font-bold">{{ __($event?->title) ?? __('Event Ticket') }}</h2>
                            @if($event)
                                <div class="flex flex-wrap gap-x-5 gap-y-1 mt-2 text-sm text-emerald-100">
                                    <span>📅 {{ $event->event_date?->translatedFormat('l, M j, Y') }}</span>
                                    <span>⏰ {{ $event->start_time instanceof \Carbon\Carbon ? $event->start_time->format('g:i A') : $event->start_time }}</span>
                                    @if($event->venue_name ?: $event->city)
                                        <span>📍 {{ __($event->venue_name) ?: (__($event->city) ?: '') }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        @if($ticket->checked_in_at)
                            <div class="bg-white/20 rounded-xl px-4 py-2 text-center">
                                <div class="text-2xl">✅</div>
                                <div class="text-xs mt-1">{{ __('Checked In') }}</div>
                            </div>
                        @else
                            <div class="bg-white/20 rounded-xl px-4 py-2 text-center">
                                <div class="text-2xl">🎟️</div>
                                <div class="text-xs mt-1">{{ __('Valid') }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Ticket Body --}}
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    {{-- Details --}}
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">{{ __('Ticket Type & Category') }}</p>
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-bold text-slate-900 text-lg">{{ __($ticketType?->name) ?? __('General Admission') }}</p>
                                @if($ticketType)
                                    @php $meta = $ticketType->tierMeta(); @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $meta['bg_badge'] }}">
                                        {{ $meta['icon'] }} {{ $meta['label'] }}
                                    </span>
                                @endif
                            </div>
                            @if($ticketType?->description)
                                <p class="text-xs text-slate-500 mt-1 italic">{{ $ticketType->description }}</p>
                            @endif
                        </div>
                        <div x-data="{ copied: false }">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">{{ __('Unique Ticket Code') }}</p>
                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $ticket->code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="text-xs text-emerald-700 hover:text-emerald-800 font-bold flex items-center gap-1 cursor-pointer">
                                    <span x-show="!copied">📋 {{ __('Copy Code') }}</span>
                                    <span x-show="copied" class="text-emerald-600 font-bold">✓ {{ __('Copied!') }}</span>
                                </button>
                            </div>
                            <code class="text-xl font-mono font-bold text-slate-900 bg-slate-100 px-4 py-2.5 rounded-xl tracking-widest block border border-slate-200 select-all">{{ $ticket->code }}</code>
                            <p class="text-[10px] text-slate-400 mt-1">{{ __('Guaranteed unique single-entry code. Cannot be duplicated or reused.') }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">{{ __('Issued') }}</p>
                                <p class="text-sm text-slate-700">{{ $ticket->issued_at?->translatedFormat('M j, Y H:i') ?? $ticket->created_at?->translatedFormat('M j, Y H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium uppercase tracking-wide mb-1">{{ __('Status') }}</p>
                                @if($ticket->checked_in_at)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-slate-100 text-slate-600 text-xs font-semibold rounded-full">
                                        ✔ {{ __('Checked In') }} {{ $ticket->checked_in_at->translatedFormat('M j, H:i') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full">
                                        ✅ {{ __('Valid') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-wrap gap-3 pt-2">
                            @if($event)
                                <a href="{{ route('events.show', $event->slug) }}" class="btn-secondary text-sm">{{ __('View Event') }}</a>
                                @if($event->isCalendarEligible())
                                    <a href="{{ route('events.ics', $event->slug) }}" class="btn-primary text-sm">
                                        📅 {{ __('Download Calendar (.ics)') }}
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-50 text-amber-800 text-xs font-semibold rounded-xl border border-amber-200">
                                        ⏳ {{ __('Date Pending Confirmation') }}
                                    </span>
                                @endif
                            @endif
                            <button onclick="window.print()" class="btn-secondary text-sm">
                                🖨️ {{ __('Print Ticket') }}
                            </button>
                        </div>
                    </div>

                    {{-- QR Code (JS-rendered) --}}
                    <div class="flex flex-col items-center justify-center gap-4">
                        <div class="border-4 border-emerald-500 rounded-2xl p-3 bg-white shadow-xl">
                            <div id="qrcode" class="w-52 h-52"></div>
                        </div>
                        <p class="text-xs text-slate-400 text-center">{{ __('Scan this QR code at the venue gate') }}</p>
                        <code class="text-xs text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg font-mono">{{ $ticket->code }}</code>
                    </div>
                </div>

                {{-- Bottom Dashed Separator --}}
                <div class="mx-6 border-t-2 border-dashed border-slate-200"></div>
                <div class="p-5 flex items-center justify-between text-xs text-slate-400">
                    <span>{{ __('Powered by Huddle') }}</span>
                    @if($event?->vendorProfile)
                        <span>{{ __('Organiser') }}: {{ $event->vendorProfile->business_name }}</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <script>
        QRCode.toCanvas(
            document.createElement('canvas'),
            '{{ $qrCode }}',
            { width: 208, margin: 1, color: { dark: '#0f172a', light: '#ffffff' } },
            function (error, canvas) {
                if (error) {
                    document.getElementById('qrcode').innerHTML = '<p class="text-xs text-red-500">{{ __('QR generation failed') }}</p>';
                    return;
                }
                document.getElementById('qrcode').appendChild(canvas);
            }
        );
    </script>
    @endpush
</x-app-layout>
