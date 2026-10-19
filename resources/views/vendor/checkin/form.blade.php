<x-crm-layout :title="__('QR & Ticket Code Check-In')" role="vendor">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('vendor.dashboard') }}" class="hover:text-slate-800">{{ __('Vendor CRM') }}</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">{{ __('Ticket Code Verification & Check-In') }}</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6" x-data="{ mode: 'checkin', inputCode: '' }">

        {{-- Header --}}
        <div class="border-b border-slate-200 pb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 flex items-center gap-2.5">
                    <span>{{ __('Attendee Ticket Check-In') }}</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold uppercase tracking-wider">Live</span>
                </h1>
                <p class="text-xs text-slate-500 mt-1">{{ __('Verify attendee ticket codes, check tier perks, and enforce single-use admission safeguards.') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('vendor.orders.index') }}" class="btn-secondary text-xs py-2 px-3 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    {{ __('View Orders') }}
                </a>
            </div>
        </div>

        {{-- Verification Result Card (When a code was scanned) --}}
        @if (session('scanned_result'))
            @php
                $result = session('scanned_result');
                $d = $result['details'] ?? [];
                $tierMeta = $d['tier_meta'] ?? null;
            @endphp
            <div class="p-6 rounded-2xl border shadow-lg transition-all {{ $result['valid'] ? 'bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-white border-emerald-300 shadow-emerald-500/10' : ($result['type'] === 'already_used' ? 'bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-white border-amber-300 shadow-amber-500/10' : 'bg-gradient-to-br from-rose-500/10 via-rose-500/5 to-white border-rose-300 shadow-rose-500/10') }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 font-bold text-white shadow-md {{ $result['valid'] ? 'bg-emerald-600 shadow-emerald-600/30' : ($result['type'] === 'already_used' ? 'bg-amber-600 shadow-amber-600/30' : 'bg-rose-600 shadow-rose-600/30') }}">
                            @if ($result['valid'])
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @elseif ($result['type'] === 'already_used')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-lg font-bold text-slate-900">{{ $result['title'] }}</h3>
                                @if ($result['valid'])
                                    @if ($result['type'] === 'verified_only')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                            {{ __('Verified (Ready to Admit)') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                            {{ __('Admitted') }}
                                        </span>
                                    @endif
                                @elseif ($result['type'] === 'already_used')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        {{ __('Duplicate Scan Blocked') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                        {{ __('Admission Denied') }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600 mt-0.5">{{ $result['message'] }}</p>
                        </div>
                    </div>
                </div>

                @if(!empty($d))
                    <div class="mt-5 pt-4 border-t border-slate-200/80 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        {{-- Attendee --}}
                        <div class="p-3 bg-white/80 rounded-xl border border-slate-200/70">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">{{ __('Attendee') }}</span>
                            <span class="text-sm font-bold text-slate-900 truncate block mt-0.5">{{ $d['attendee_name'] ?? 'Attendee' }}</span>
                        </div>

                        {{-- Tier / Category --}}
                        <div class="p-3 bg-white/80 rounded-xl border border-slate-200/70">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">{{ __('Ticket Category / Tier') }}</span>
                            <div class="mt-1 flex items-center gap-1.5">
                                @if($tierMeta)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold text-white shadow-xs {{ $tierMeta['badge_class'] ?? 'bg-slate-700' }}">
                                        <span>{{ $tierMeta['icon'] ?? '🎟️' }}</span>
                                        <span>{{ $tierMeta['label'] ?? ucfirst($d['tier']) }}</span>
                                    </span>
                                @else
                                    <span class="text-xs font-bold text-slate-800">{{ $d['ticket_type_name'] ?? 'General' }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Event --}}
                        <div class="p-3 bg-white/80 rounded-xl border border-slate-200/70">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">{{ __('Event') }}</span>
                            <span class="text-xs font-semibold text-slate-900 truncate block mt-0.5" title="{{ $d['event_title'] }}">{{ $d['event_title'] }}</span>
                        </div>

                        {{-- Ticket Code --}}
                        <div class="p-3 bg-white/80 rounded-xl border border-slate-200/70">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">{{ __('Verified Code') }}</span>
                            <span class="text-xs font-mono font-bold text-indigo-700 block mt-0.5 truncate">{{ $d['code'] }}</span>
                        </div>
                    </div>

                    {{-- If verified only, provide 1-click admit button --}}
                    @if ($result['valid'] && $result['type'] === 'verified_only')
                        <div class="mt-4 pt-3 border-t border-slate-200/80 flex items-center justify-end gap-3">
                            <form method="POST" action="{{ route('vendor.checkin.validate') }}" class="inline">
                                @csrf
                                <input type="hidden" name="code" value="{{ $d['code'] }}">
                                <input type="hidden" name="mode" value="checkin">
                                <button type="submit" class="btn-emerald py-2 px-4 text-xs font-bold flex items-center gap-2 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    {{ __('Admit Attendee Now') }}
                                </button>
                            </form>
                        </div>
                    @endif
                @endif
            </div>
        @elseif (session('status'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 text-sm shadow-sm">
                <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center shrink-0 text-emerald-600 font-bold">
                    ✓
                </div>
                <div>
                    <p class="font-bold">{{ __('Ticket Validated & Checked In') }}</p>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('status') }}</p>
                </div>
            </div>
        @endif

        {{-- Terminal Form Card --}}
        <div class="surface p-6 sm:p-8 space-y-6">
            
            {{-- Mode Switcher Tabs --}}
            <div class="flex items-center justify-center">
                <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">
                    <button type="button"
                            @click="mode = 'checkin'"
                            :class="mode === 'checkin' ? 'bg-white text-emerald-700 shadow-sm border border-slate-200/80' : 'text-slate-500 hover:text-slate-800'"
                            class="px-4 py-2 rounded-lg transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ __('Check In & Admit') }}</span>
                    </button>
                    <button type="button"
                            @click="mode = 'verify'"
                            :class="mode === 'verify' ? 'bg-white text-blue-700 shadow-sm border border-slate-200/80' : 'text-slate-500 hover:text-slate-800'"
                            class="px-4 py-2 rounded-lg transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>{{ __('Verify Only (Inspect)') }}</span>
                    </button>
                </div>
            </div>

            <div class="text-center space-y-2">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto text-white shadow-lg transition-colors"
                     :class="mode === 'checkin' ? 'bg-gradient-to-br from-emerald-500 via-teal-600 to-emerald-700 shadow-emerald-950/20' : 'bg-gradient-to-br from-blue-500 via-indigo-600 to-blue-700 shadow-blue-950/20'">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <h2 class="text-xl font-bold text-slate-900" x-text="mode === 'checkin' ? '{{ __('Scan or Enter Ticket Code to Admit') }}' : '{{ __('Verify Ticket Code Details') }}'"></h2>
                <p class="text-slate-500 text-xs max-w-md mx-auto" x-text="mode === 'checkin' ? '{{ __('Validates code uniqueness, checks payment status, and marks the ticket as admitted. Duplicate scans will be blocked.') }}' : '{{ __('Inspects tier perks, attendee info, and payment status without consuming the admission pass.') }}'"></p>
            </div>

            <form method="POST" action="{{ route('vendor.checkin.validate') }}" class="space-y-4 max-w-md mx-auto">
                @csrf
                <input type="hidden" name="mode" :value="mode">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5 text-left flex items-center justify-between">
                        <span>{{ __('Unique Ticket Code') }}</span>
                        <span class="text-[10px] text-slate-400 font-normal">Format: TKT-EV#-...</span>
                    </label>
                    <input type="text"
                           name="code"
                           x-model="inputCode"
                           class="input-field text-center text-xl font-mono tracking-widest uppercase font-bold py-3.5 focus:ring-emerald-500 focus:border-emerald-500 @error('code') border-rose-300 focus:ring-rose-500 @enderror"
                           placeholder="e.g. TKT-EV1-G-9X42A..."
                           autocomplete="off"
                           autofocus
                           required>
                    @error('code')
                        <p class="text-xs text-rose-600 font-semibold mt-1.5 text-left flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <button type="submit"
                            @click="mode = 'checkin'"
                            class="btn-emerald w-full py-3 text-xs sm:text-sm font-bold shadow-md shadow-emerald-950/20 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ __('Check In & Admit') }}</span>
                    </button>
                    <button type="submit"
                            @click="mode = 'verify'"
                            class="btn-secondary w-full py-3 text-xs sm:text-sm font-bold flex items-center justify-center gap-2 text-slate-700 hover:text-slate-900 border-slate-300">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>{{ __('Verify Only') }}</span>
                    </button>
                </div>
            </form>

            {{-- Terminal Safeguard Notice --}}
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-500">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span><strong>{{ __('Anti-Reuse Protection Active') }}</strong>: Once admitted, a ticket code cannot be used again.</span>
                </div>
                <div class="flex items-center gap-1.5 text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ __('Barcode scanners auto-submit') }}</span>
                </div>
            </div>
        </div>

        {{-- Supported Tier Guide for Venue Staff --}}
        <div class="surface p-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">{{ __('Venue Admission Tiers Reference') }}</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3 rounded-xl border border-amber-900/10 bg-amber-900/5">
                    <div class="flex items-center gap-1.5 font-bold text-xs text-amber-900">
                        <span>🥉</span>
                        <span>{{ __('Bronze Tier') }}</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">{{ __('Standard admission pass with general access.') }}</p>
                </div>
                <div class="p-3 rounded-xl border border-slate-300/80 bg-slate-100/60">
                    <div class="flex items-center gap-1.5 font-bold text-xs text-slate-700">
                        <span>🥈</span>
                        <span>{{ __('Silver Tier') }}</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">{{ __('Priority admission with standard venue seating.') }}</p>
                </div>
                <div class="p-3 rounded-xl border border-amber-400/30 bg-amber-400/10">
                    <div class="flex items-center gap-1.5 font-bold text-xs text-amber-800">
                        <span>🥇</span>
                        <span>{{ __('Gold Tier') }}</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">{{ __('VIP fast-track admission & reserved lounge.') }}</p>
                </div>
                <div class="p-3 rounded-xl border border-cyan-400/30 bg-cyan-400/10">
                    <div class="flex items-center gap-1.5 font-bold text-xs text-cyan-800">
                        <span>💎</span>
                        <span>{{ __('Diamond Tier') }}</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">{{ __('All-access VIP pass + backstage & complimentary drinks.') }}</p>
                </div>
            </div>
        </div>

    </div>
</x-crm-layout>
