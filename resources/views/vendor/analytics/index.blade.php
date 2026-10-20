<x-crm-layout :title="__('Event Reporting & Analytics')" role="vendor">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('vendor.dashboard') }}" class="hover:text-slate-800">{{ __('Vendor CRM') }}</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">{{ __('Reporting & Analytics') }}</span>
        </div>
    </x-slot>

    @push('styles')
    <style>
        .analytics-card-glow {
            box-shadow: 0 4px 20px -2px rgba(16, 185, 129, 0.08), 0 2px 6px -1px rgba(0, 0, 0, 0.04);
        }
        .chart-container-custom {
            position: relative;
            height: 320px;
            width: 100%;
        }
        .donut-container-custom {
            position: relative;
            height: 260px;
            width: 100%;
        }
    </style>
    @endpush

    <div class="space-y-6 max-w-7xl mx-auto" x-data="vendorAnalyticsDashboard()">

        {{-- Dynamic Progress / Loading Bar --}}
        <div x-show="isLoading"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 via-sky-400 to-purple-500 animate-pulse z-50">
        </div>

        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200/80 inline-flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ __('Financial & Ticket Intelligence') }}
                    </span>
                    <span x-show="filters.date_range !== 'all'"
                          x-text="filters.date_range.replace('_', ' ').toUpperCase()"
                          class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                        @if($filters['date_range'] !== 'all')
                            {{ ucfirst(str_replace('_', ' ', $filters['date_range'])) }}
                        @endif
                    </span>
                    <span class="text-[11px] text-slate-400 flex items-center gap-1 ml-1" x-show="lastUpdatedText">
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-text="lastUpdatedText">{{ __('Updated just now') }}</span>
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-950">
                    {{ __('Event Reporting & Analytics') }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
                    {{ __('Detailed sales summaries, revenue metrics after platform fees and refunds, ticket tier breakdowns, and report exports.') }}
                </p>
            </div>

            {{-- Action Buttons & Exports --}}
            <div class="flex flex-wrap items-center gap-2.5 shrink-0" x-data="{ exportOpen: false }">
                {{-- Live Auto-Refresh Toggle --}}
                <button type="button"
                        @click="toggleAutoRefresh()"
                        :class="isAutoRefresh ? 'bg-emerald-50 text-emerald-700 border-emerald-300 ring-2 ring-emerald-400/20' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                        class="text-xs py-2.5 px-3 flex items-center gap-2 rounded-xl border font-semibold transition-all shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span x-show="isAutoRefresh" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2" :class="isAutoRefresh ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                    </span>
                    <span x-text="isAutoRefresh ? '{{ __('Auto-Refresh (30s)') }}' : '{{ __('Live Updates') }}'">{{ __('Live Updates') }}</span>
                </button>

                {{-- Manual Refresh Button --}}
                <button type="button"
                        @click="fetchData(true)"
                        :disabled="isLoading"
                        class="btn-secondary text-xs py-2.5 px-3.5 flex items-center gap-1.5 rounded-xl shadow-sm disabled:opacity-50"
                        title="{{ __('Refresh') }}">
                    <svg class="w-3.5 h-3.5 text-slate-600 transition-transform duration-500" :class="isLoading ? 'animate-spin text-emerald-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span x-text="isLoading ? '{{ __('Refreshing...') }}' : '{{ __('Refresh') }}'">{{ __('Refresh') }}</span>
                </button>

                <div class="relative">
                    <button @click="exportOpen = !exportOpen"
                            @click.away="exportOpen = false"
                            type="button"
                            class="btn-primary text-xs py-2.5 px-4 flex items-center gap-2 shadow-sm rounded-xl">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="font-semibold">{{ __('Export Report') }}</span>
                        <svg class="w-3.5 h-3.5 opacity-70 transition-transform duration-200" :class="exportOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="exportOpen"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-2xl border border-slate-100 py-2 z-30 divide-y divide-slate-100"
                         style="display: none;">
                        <a :href="exportUrl('summary')"
                           href="{{ route('vendor.analytics.export', array_merge(request()->query(), ['type' => 'summary'])) }}"
                           class="flex items-center gap-3 px-4 py-3 text-xs text-slate-700 hover:bg-slate-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">{{ __('Event Sales Summary CSV') }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Event-wise totals & occupancy') }}</p>
                            </div>
                        </a>
                        <a :href="exportUrl('categories')"
                           href="{{ route('vendor.analytics.export', array_merge(request()->query(), ['type' => 'categories'])) }}"
                           class="flex items-center gap-3 px-4 py-3 text-xs text-slate-700 hover:bg-slate-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 group-hover:text-purple-700 transition-colors">{{ __('Ticket Categories CSV') }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Tier pricing & sold breakdown') }}</p>
                            </div>
                        </a>
                        <a :href="exportUrl('orders')"
                           href="{{ route('vendor.analytics.export', array_merge(request()->query(), ['type' => 'orders'])) }}"
                           class="flex items-center gap-3 px-4 py-3 text-xs text-slate-700 hover:bg-slate-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 group-hover:text-blue-700 transition-colors">{{ __('All Orders & Attendees CSV') }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Individual order rows with fees') }}</p>
                            </div>
                        </a>
                    </div>
                </div>

                <a href="{{ route('vendor.orders.index') }}" class="btn-secondary text-xs py-2.5 px-3.5 flex items-center gap-1.5 rounded-xl">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>{{ __('Orders List') }}</span>
                </a>
            </div>
        </div>

        {{-- Interactive Filter Toolbar Card with Instant AJAX Sync --}}
        <div class="surface p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-3.5">
            {{-- Quick Date Range Presets Pill Bar --}}
            <div class="flex flex-wrap items-center gap-1.5 pb-3 border-b border-slate-100">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-1">{{ __('Date Range') }}:</span>
                <button type="button" @click="setPreset('all')"
                        :class="filters.date_range === 'all' ? 'bg-emerald-600 text-white font-bold shadow-sm ring-1 ring-emerald-500' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-2.5 py-1 text-xs rounded-lg transition-all">{{ __('All Time') }}</button>
                <button type="button" @click="setPreset('today')"
                        :class="filters.date_range === 'today' ? 'bg-emerald-600 text-white font-bold shadow-sm ring-1 ring-emerald-500' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-2.5 py-1 text-xs rounded-lg transition-all">{{ __('Today') }}</button>
                <button type="button" @click="setPreset('last_7_days')"
                        :class="filters.date_range === 'last_7_days' ? 'bg-emerald-600 text-white font-bold shadow-sm ring-1 ring-emerald-500' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-2.5 py-1 text-xs rounded-lg transition-all">{{ __('Last 7 Days') }}</button>
                <button type="button" @click="setPreset('last_30_days')"
                        :class="filters.date_range === 'last_30_days' ? 'bg-emerald-600 text-white font-bold shadow-sm ring-1 ring-emerald-500' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-2.5 py-1 text-xs rounded-lg transition-all">{{ __('Last 30 Days') }}</button>
                <button type="button" @click="setPreset('this_month')"
                        :class="filters.date_range === 'this_month' ? 'bg-emerald-600 text-white font-bold shadow-sm ring-1 ring-emerald-500' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-2.5 py-1 text-xs rounded-lg transition-all">{{ __('This Month') }}</button>
                <button type="button" @click="setPreset('this_year')"
                        :class="filters.date_range === 'this_year' ? 'bg-emerald-600 text-white font-bold shadow-sm ring-1 ring-emerald-500' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-2.5 py-1 text-xs rounded-lg transition-all">{{ __('This Year') }}</button>
                <button type="button" @click="setPreset('custom')"
                        :class="filters.date_range === 'custom' ? 'bg-emerald-600 text-white font-bold shadow-sm ring-1 ring-emerald-500' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-2.5 py-1 text-xs rounded-lg transition-all">{{ __('Custom...') }}</button>

                {{-- Active Event Filter Pill Indicator --}}
                <div x-show="filters.event_id" x-cloak class="ml-auto inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold border border-blue-200/80">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    <span>{{ __('Filtered by Event') }}</span>
                    <button type="button" @click="clearEventFilter()" class="hover:text-blue-900 ml-1 text-blue-500 font-black" title="{{ __('Clear Event Filter') }}">✕</button>
                </div>
            </div>

            <form method="GET" action="{{ route('vendor.analytics.index') }}" @submit.prevent="fetchData(true)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                    {{-- Filter 1: Event Selector --}}
                    <div>
                        <label for="filter_event_id" class="block text-xs font-bold text-slate-700 mb-1">
                            {{ __('Filter by Event') }}
                        </label>
                        <select name="event_id" id="filter_event_id"
                                x-model="filters.event_id"
                                @change="fetchData(true)"
                                class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-white">
                            <option value="">{{ __('All Events') }} ({{ $all_events->count() }})</option>
                            @foreach($all_events as $ev)
                                <option value="{{ $ev->id }}" {{ (string)$filters['event_id'] === (string)$ev->id ? 'selected' : '' }}>
                                    {{ $ev->title }} @if($ev->event_date) ({{ $ev->event_date->format('M j, Y') }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter 2: Date Range Preset --}}
                    <div>
                        <label for="filter_date_range" class="block text-xs font-bold text-slate-700 mb-1">
                            {{ __('Date Range') }}
                        </label>
                        <select name="date_range" id="filter_date_range"
                                x-model="filters.date_range"
                                @change="if(filters.date_range !== 'custom') fetchData(true)"
                                class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-white">
                            <option value="all" {{ $filters['date_range'] === 'all' ? 'selected' : '' }}>{{ __('All Time') }}</option>
                            <option value="today" {{ $filters['date_range'] === 'today' ? 'selected' : '' }}>{{ __('Today') }}</option>
                            <option value="last_7_days" {{ $filters['date_range'] === 'last_7_days' ? 'selected' : '' }}>{{ __('Last 7 Days') }}</option>
                            <option value="last_30_days" {{ $filters['date_range'] === 'last_30_days' ? 'selected' : '' }}>{{ __('Last 30 Days') }}</option>
                            <option value="this_month" {{ $filters['date_range'] === 'this_month' ? 'selected' : '' }}>{{ __('This Month') }}</option>
                            <option value="last_month" {{ $filters['date_range'] === 'last_month' ? 'selected' : '' }}>{{ __('Last Month') }}</option>
                            <option value="this_year" {{ $filters['date_range'] === 'this_year' ? 'selected' : '' }}>{{ __('This Year') }}</option>
                            <option value="custom" {{ $filters['date_range'] === 'custom' ? 'selected' : '' }}>{{ __('Custom Date Range...') }}</option>
                        </select>
                    </div>

                    {{-- Filter 3: Order/Ticket Status --}}
                    <div>
                        <label for="filter_status" class="block text-xs font-bold text-slate-700 mb-1">
                            {{ __('Ticket Status') }}
                        </label>
                        <select name="status" id="filter_status"
                                x-model="filters.status"
                                @change="fetchData(true)"
                                class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-white">
                            <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>{{ __('All Tickets & Orders') }}</option>
                            <option value="paid" {{ $filters['status'] === 'paid' ? 'selected' : '' }}>{{ __('Paid Tickets Only') }}</option>
                            <option value="partially_refunded" {{ $filters['status'] === 'partially_refunded' ? 'selected' : '' }}>{{ __('Partially Refunded Orders') }}</option>
                            <option value="free" {{ $filters['status'] === 'free' ? 'selected' : '' }}>{{ __('Free Registrations Only') }}</option>
                            <option value="refunded" {{ $filters['status'] === 'refunded' ? 'selected' : '' }}>{{ __('Refunded Orders') }}</option>
                            <option value="cancelled" {{ $filters['status'] === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled Orders') }}</option>
                        </select>
                    </div>

                    {{-- Filter Buttons --}}
                    <div class="flex items-end gap-2">
                        <button type="submit"
                                :disabled="isLoading"
                                class="btn-emerald text-xs py-2.5 px-4 flex-1 justify-center rounded-xl shadow-sm disabled:opacity-50">
                            <span x-show="!isLoading">{{ __('Apply Filter') }}</span>
                            <span x-show="isLoading" class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>{{ __('Updating...') }}</span>
                        </button>
                        <button type="button"
                                @click="filters = { event_id: '', date_range: 'all', status: 'all', from_date: '', to_date: '' }; fetchData(true);"
                                class="btn-secondary text-xs py-2.5 px-3.5 text-slate-500 hover:text-slate-800 rounded-xl" title="{{ __('Reset filters') }}">
                            {{ __('Reset') }}
                        </button>
                    </div>
                </div>

                {{-- Custom Date Inputs (shown when date_range is custom) --}}
                <div id="custom_date_inputs"
                     x-show="filters.date_range === 'custom'"
                     class="{{ $filters['date_range'] === 'custom' ? '' : 'hidden' }} pt-3 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-lg">
                    <div>
                        <label for="from_date" class="block text-[11px] font-bold text-slate-600 mb-0.5">{{ __('From Date') }}</label>
                        <input type="date" name="from_date" id="from_date"
                               x-model="filters.from_date"
                               @change="fetchData(true)"
                               value="{{ $filters['from_date'] }}" class="w-full text-xs rounded-lg border-slate-200">
                    </div>
                    <div>
                        <label for="to_date" class="block text-[11px] font-bold text-slate-600 mb-0.5">{{ __('To Date') }}</label>
                        <input type="date" name="to_date" id="to_date"
                               x-model="filters.to_date"
                               @change="fetchData(true)"
                               value="{{ $filters['to_date'] }}" class="w-full text-xs rounded-lg border-slate-200">
                    </div>
                </div>
            </form>
        </div>

        {{-- Executive KPI Financial & Ticket Performance Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">

            @if($overall['currency'] === 'MULTI')
                <div class="col-span-full surface p-4 rounded-xl border border-amber-200 bg-amber-50">
                    <p class="font-bold text-amber-900">{{ __('Revenue is shown separately by currency') }}</p>
                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                        @foreach($overall['currency_totals'] as $currencyTotal)
                            <div class="rounded-lg bg-white p-3 border border-amber-100">
                                <p class="font-bold text-slate-900">{{ $currencyTotal['currency'] }}</p>
                                <p class="text-xs text-slate-600 mt-1">{{ __('Gross') }} {{ number_format($currencyTotal['gross_revenue'], 2) }} · {{ __('Refunds') }} {{ number_format($currencyTotal['refunded_amount'], 2) }}</p>
                                <p class="text-sm font-semibold text-emerald-700">{{ __('Net') }} {{ number_format($currencyTotal['net_revenue'], 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- KPI 1: Gross Ticket Revenue --}}
            <div class="surface p-5 rounded-2xl border-t-4 border-t-blue-500 hover:shadow-lg transition-all relative overflow-hidden group">
                <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider">
                    <span>{{ __('Gross Revenue') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 text-2xl font-black text-slate-950 tracking-tight" x-text="overall.gross_revenue === null ? '{{ __('See currency breakdown') }}' : overall.currency + ' ' + formatMoney(overall.gross_revenue)">
                    {{ $overall['currency'] }} {{ is_numeric($overall['gross_revenue']) ? number_format($overall['gross_revenue'], 2) : __('See breakdown') }}
                </div>
                <div class="mt-1.5 flex items-center justify-between text-[11px] text-slate-500">
                    <span>{{ __('Gross Sales') }}</span>
                    <span class="font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full"><span x-text="overall.paid_orders_count">{{ $overall['paid_orders_count'] }}</span> {{ __('paid') }}</span>
                </div>
            </div>

            {{-- KPI 2: Net Vendor Revenue (Gross - Refunds - Fees) --}}
            <div class="surface p-5 rounded-2xl border-t-4 border-t-emerald-500 bg-gradient-to-b from-emerald-50/20 to-transparent hover:shadow-lg transition-all relative overflow-hidden group">
                <div class="flex items-center justify-between text-emerald-800 text-xs font-bold uppercase tracking-wider">
                    <span>{{ __('Net Revenue') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 text-2xl font-black text-emerald-700 tracking-tight" x-text="overall.net_revenue === null ? '{{ __('See currency breakdown') }}' : overall.currency + ' ' + formatMoney(overall.net_revenue)">
                    {{ $overall['currency'] }} {{ is_numeric($overall['net_revenue']) ? number_format($overall['net_revenue'], 2) : __('See breakdown') }}
                </div>
                <div class="mt-1.5 flex items-center justify-between text-[11px] text-emerald-800 font-semibold">
                    <span>{{ __('Net Payout') }}</span>
                    <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">{{ __('After fees & refunds') }}</span>
                </div>
            </div>

            {{-- KPI 3: Total Tickets Sold (Paid + Free) --}}
            <div class="surface p-5 rounded-2xl border-t-4 border-t-purple-500 hover:shadow-lg transition-all relative overflow-hidden group">
                <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider">
                    <span>{{ __('Tickets Sold') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    </div>
                </div>
                <div class="mt-3 text-2xl font-black text-slate-950 tracking-tight" x-text="formatNumber(overall.total_tickets_sold)">
                    {{ number_format($overall['total_tickets_sold']) }}
                </div>
                <div class="mt-1.5 flex items-center justify-between text-[11px] text-slate-500">
                    <span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded"><span x-text="overall.paid_tickets_count">{{ $overall['paid_tickets_count'] }}</span> {{ __('paid') }}</span>
                    <span class="text-sky-700 font-bold bg-sky-50 px-1.5 py-0.5 rounded"><span x-text="overall.free_tickets_count">{{ $overall['free_tickets_count'] }}</span> {{ __('free') }}</span>
                </div>
            </div>

            {{-- KPI 4: Platform Commission Fees --}}
            <div class="surface p-5 rounded-2xl border-t-4 border-t-slate-500 hover:shadow-lg transition-all relative overflow-hidden group">
                <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider">
                    <span>{{ __('Platform Fees') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                </div>
                <div class="mt-3 text-2xl font-black text-slate-900 tracking-tight" x-text="'$' + formatMoney(overall.platform_fees)">
                    ${{ number_format($overall['platform_fees'], 2) }}
                </div>
                <div class="mt-1.5 flex items-center justify-between text-[11px] text-slate-500">
                    <span class="font-semibold"><span x-text="overall.platform_fee_percent">{{ $overall['platform_fee_percent'] }}</span>% {{ __('Rate') }}</span>
                    <span class="text-slate-400">{{ __('Deducted') }}</span>
                </div>
            </div>

            {{-- KPI 5: Refunds & Cancellations --}}
            <div class="surface p-5 rounded-2xl border-t-4 border-t-rose-500 hover:shadow-lg transition-all relative overflow-hidden group">
                <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider">
                    <span>{{ __('Refunds / Canc.') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                    </div>
                </div>
                <div class="mt-3 text-2xl font-black text-rose-600 tracking-tight" x-text="'$' + formatMoney(overall.refunded_amount)">
                    ${{ number_format($overall['refunded_amount'], 2) }}
                </div>
                <div class="mt-1.5 flex items-center justify-between text-[11px] text-slate-500 font-medium">
                    <span class="text-rose-700 font-semibold"><span x-text="overall.refunded_tickets_count">{{ $overall['refunded_tickets_count'] }}</span> {{ __('ref.') }}</span>
                    <span class="text-slate-500"><span x-text="overall.cancelled_tickets_count">{{ $overall['cancelled_tickets_count'] }}</span> {{ __('canc.') }}</span>
                </div>
            </div>

            {{-- KPI 6: Available Capacity / Remaining Tickets --}}
            <div class="surface p-5 rounded-2xl border-t-4 border-t-amber-500 hover:shadow-lg transition-all relative overflow-hidden group">
                <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider">
                    <span>{{ __('Available Seats') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                </div>
                <div class="mt-3 text-2xl font-black text-slate-950 tracking-tight" x-text="formatNumber(overall.available_tickets_count)">
                    {{ number_format($overall['available_tickets_count']) }}
                </div>
                <div class="mt-1.5 flex items-center justify-between text-[11px] text-slate-500">
                    <span class="font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded"><span x-text="overall.occupancy_rate">{{ $overall['occupancy_rate'] }}</span>% {{ __('Sold out') }}</span>
                    <span class="text-slate-400">/ <span x-text="formatNumber(overall.total_capacity)">{{ number_format($overall['total_capacity']) }}</span></span>
                </div>
            </div>
        </div>

        {{-- Performance Quick Insights Bar --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" x-show="topEvent || topCategory">
            {{-- Top Event --}}
            <div class="surface p-4 rounded-2xl flex items-center gap-3.5 border border-slate-200/80">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shrink-0 shadow-md shadow-emerald-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Top Performing Event') }}</p>
                    <p class="text-xs font-bold text-slate-900 truncate mt-0.5" x-text="topEvent ? topEvent.title : '{{ __('No Event Sales') }}'">{{ $top_event['title'] ?? 'N/A' }}</p>
                    <p class="text-[11px] text-emerald-700 font-semibold mt-0.5" x-text="topEvent ? ('$' + formatMoney(topEvent.gross_revenue) + ' • ' + topEvent.total_sold + ' {{ __('tickets sold') }}') : ''">
                        @if(!empty($top_event))
                            ${{ number_format($top_event['gross_revenue'] ?? 0, 2) }} • {{ $top_event['total_sold'] ?? 0 }} {{ __('tickets sold') }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- Top Category Tier --}}
            <div class="surface p-4 rounded-2xl flex items-center gap-3.5 border border-slate-200/80">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white shrink-0 shadow-md shadow-purple-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Top Ticket Tier') }}</p>
                    <p class="text-xs font-bold text-slate-900 truncate mt-0.5" x-text="topCategory ? topCategory.category_name : '{{ __('No Tier Sales') }}'">{{ $top_category['category_name'] ?? 'N/A' }}</p>
                    <p class="text-[11px] text-purple-700 font-semibold mt-0.5" x-text="topCategory ? ('$' + formatMoney(topCategory.gross_revenue) + ' • ' + topCategory.total_sold + ' {{ __('tickets sold') }}') : ''">
                        @if(!empty($top_category))
                            ${{ number_format($top_category['gross_revenue'] ?? 0, 2) }} • {{ $top_category['total_sold'] ?? 0 }} {{ __('tickets sold') }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- Average Order Value --}}
            <div class="surface p-4 rounded-2xl flex items-center gap-3.5 border border-slate-200/80">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center text-white shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Average Order Value') }}</p>
                    <p class="text-xs font-bold text-slate-900 mt-0.5" x-text="'$' + formatMoney(overall.average_order_value)">${{ number_format($overall['average_order_value'], 2) }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        <span x-text="overall.paid_orders_count">{{ $overall['paid_orders_count'] }}</span> {{ __('paid orders processed') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Primary Visual Charts Section: Trend & Category Share --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Interactive Trend Chart (Col Span 2) --}}
            <div class="surface p-6 rounded-2xl shadow-sm border border-slate-200/80 lg:col-span-2 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-950">{{ __('Revenue & Ticket Sales Trend') }}</h2>
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ __('Daily ticket sales and gross revenue over the selected period.') }}
                        </p>
                    </div>

                    {{-- Metric View Switcher Pills --}}
                    <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-semibold self-start sm:self-auto">
                        <button type="button"
                                @click="chartMetric = 'net_revenue'; updateTrendChart('net_revenue')"
                                :class="chartMetric === 'net_revenue' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1.5 rounded-lg transition-all">
                            {{ __('Net Revenue') }}
                        </button>
                        <button type="button"
                                @click="chartMetric = 'gross_revenue'; updateTrendChart('gross_revenue')"
                                :class="chartMetric === 'gross_revenue' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1.5 rounded-lg transition-all">
                            {{ __('Gross Sales') }}
                        </button>
                        <button type="button"
                                @click="chartMetric = 'tickets_sold'; updateTrendChart('tickets_sold')"
                                :class="chartMetric === 'tickets_sold' ? 'bg-white text-purple-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1.5 rounded-lg transition-all">
                            {{ __('Tickets Sold') }}
                        </button>
                    </div>
                </div>

                {{-- Chart Canvas --}}
                <div class="chart-container-custom">
                    <canvas id="vendorTrendChart"></canvas>
                </div>
            </div>

            {{-- Ticket Category Donut Chart (Col Span 1) --}}
            <div class="surface p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-950">{{ __('Category Revenue Share') }}</h2>
                            <p class="text-xs text-slate-500 mt-0.5">{{ __('Sales volume by ticket tier') }}</p>
                        </div>
                        <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full">
                            {{ count($cat_chart_data['labels']) }} {{ __('Tiers') }}
                        </span>
                    </div>

                    <div class="donut-container-custom mt-3">
                        <canvas id="categoryDonutChart"></canvas>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 text-center">
                    <p class="text-xs font-semibold text-slate-500">
                        {{ __('Top Tier') }}: <span class="font-bold text-slate-900">{{ $top_category['category_name'] ?? 'General Admission' }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Secondary Charts Row: Event Comparison & Inventory Fulfillment --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Event Comparison Bar Chart (Col Span 2) --}}
            <div class="surface p-6 rounded-2xl shadow-sm border border-slate-200/80 lg:col-span-2 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">{{ __('Event Sales Comparison') }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ __('Side-by-side performance across your scheduled listings.') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-semibold self-start sm:self-auto">
                        <button type="button"
                                @click="compareMetric = 'revenue'; updateCompareChart('revenue')"
                                :class="compareMetric === 'revenue' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1.5 rounded-lg transition-all">
                            {{ __('Revenue ($)') }}
                        </button>
                        <button type="button"
                                @click="compareMetric = 'tickets'; updateCompareChart('tickets')"
                                :class="compareMetric === 'tickets' ? 'bg-white text-purple-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1.5 rounded-lg transition-all">
                            {{ __('Tickets Sold (Qty)') }}
                        </button>
                    </div>
                </div>

                <div class="chart-container-custom">
                    <canvas id="eventCompareChart"></canvas>
                </div>
            </div>

            {{-- Inventory Status Distribution Donut (Col Span 1) --}}
            <div class="surface p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-950">{{ __('Inventory Fulfillment') }}</h2>
                            <p class="text-xs text-slate-500 mt-0.5">{{ __('Overall capacity allocation') }}</p>
                        </div>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                            {{ $overall['occupancy_rate'] }}% {{ __('Sold out') }}
                        </span>
                    </div>

                    <div class="donut-container-custom mt-3">
                        <canvas id="inventoryDonutChart"></canvas>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 text-center">
                    <div class="flex items-center justify-around text-xs">
                        <div>
                            <p class="text-slate-400 text-[10px] uppercase font-bold">{{ __('Sold') }}</p>
                            <p class="font-extrabold text-emerald-700">{{ number_format($overall['total_tickets_sold']) }}</p>
                        </div>
                        <div class="w-px h-6 bg-slate-200"></div>
                        <div>
                            <p class="text-slate-400 text-[10px] uppercase font-bold">{{ __('Available') }}</p>
                            <p class="font-extrabold text-slate-800">{{ number_format($overall['available_tickets_count']) }}</p>
                        </div>
                        <div class="w-px h-6 bg-slate-200"></div>
                        <div>
                            <p class="text-slate-400 text-[10px] uppercase font-bold">{{ __('Capacity') }}</p>
                            <p class="font-extrabold text-slate-600">{{ number_format($overall['total_capacity']) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Event-Wise Performance Summaries Table --}}
        <div class="surface p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">{{ __('Event-Wise Sales & Revenue Summaries') }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('Breakdown of tickets sold, revenue generated, platform fees, refunds, and ticket categories per event.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="relative w-full sm:w-56">
                        <input type="text"
                               x-model="eventSearch"
                               placeholder="{{ __('Search events by name or city...') }}"
                               class="w-full text-xs rounded-xl border-slate-200 pl-8 pr-2.5 py-1.5 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <div class="text-xs text-slate-500 font-semibold bg-slate-100 px-3 py-1.5 rounded-full whitespace-nowrap">
                        {{ __('Total Events') }}: <span class="font-black text-slate-900">{{ count($event_summaries) }}</span>
                    </div>
                </div>
            </div>

            @if(count($event_summaries) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="pb-3 pr-4">{{ __('Event Details') }}</th>
                                <th class="pb-3 pr-4">{{ __('Date & Status') }}</th>
                                <th class="pb-3 pr-4 text-center">{{ __('Tickets Sold (Paid / Free)') }}</th>
                                <th class="pb-3 pr-4 text-center">{{ __('Occupancy / Available') }}</th>
                                <th class="pb-3 pr-4 text-right">{{ __('Gross Sales') }}</th>
                                <th class="pb-3 pr-4 text-right">{{ __('Fees & Refunds') }}</th>
                                <th class="pb-3 pr-4 text-right">{{ __('Net Payout') }}</th>
                                <th class="pb-3 text-right">{{ __('Categories') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($event_summaries as $ev)
                                <tr class="hover:bg-slate-50/80 transition-colors"
                                    x-show="(!eventSearch || '{{ strtolower(addslashes($ev['title'] . ' ' . $ev['city'] . ' ' . $ev['venue_name'])) }}'.includes(eventSearch.toLowerCase())) && (!filters.event_id || filters.event_id == '{{ $ev['event_id'] }}')">
                                    {{-- Event Title & Venue --}}
                                    <td class="py-4 pr-4">
                                        <a href="{{ route('events.show', $ev['slug']) }}" target="_blank" class="font-bold text-slate-900 hover:text-emerald-700 transition-colors block max-w-xs truncate">
                                            {{ $ev['title'] }}
                                        </a>
                                        <div class="text-xs text-slate-400 mt-0.5 truncate max-w-xs">
                                            {{ $ev['venue_name'] ?: 'Venue TBA' }}{{ $ev['city'] ? ', ' . $ev['city'] : '' }}
                                        </div>
                                    </td>

                                    {{-- Date & Status --}}
                                    <td class="py-4 pr-4 whitespace-nowrap text-xs">
                                        <div class="font-medium text-slate-800">{{ $ev['event_date_formatted'] ?: __('Date TBA') }}</div>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            @if($ev['status'] === 'published')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                    {{ __('Published') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                                    {{ ucfirst($ev['status']) }}
                                                </span>
                                            @endif

                                            @if(!$ev['is_date_confirmed'])
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                    {{ __('Tentative') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Tickets Sold Breakdown --}}
                                    <td class="py-4 pr-4 text-center whitespace-nowrap">
                                        <div class="font-black text-slate-900 text-sm">
                                            {{ $ev['total_sold'] }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5 flex items-center justify-center gap-1.5">
                                            <span class="text-emerald-700 font-bold">{{ $ev['paid_tickets'] }} {{ __('paid') }}</span>
                                            <span>•</span>
                                            <span class="text-sky-700 font-bold">{{ $ev['free_tickets'] }} {{ __('free') }}</span>
                                        </div>
                                    </td>

                                    {{-- Occupancy / Available --}}
                                    <td class="py-4 pr-4 text-center whitespace-nowrap">
                                        <div class="text-xs font-bold text-slate-800">
                                            {{ $ev['occupancy_rate'] }}%
                                        </div>
                                        <div class="w-20 mx-auto bg-slate-100 rounded-full h-1.5 mt-1 overflow-hidden">
                                            <div class="bg-emerald-500 h-full" style="width: {{ min(100, $ev['occupancy_rate']) }}%"></div>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-1">
                                            {{ $ev['available_tickets'] }} {{ __('left of') }} {{ $ev['capacity'] }}
                                        </div>
                                    </td>

                                    {{-- Gross Sales --}}
                                    <td class="py-4 pr-4 text-right whitespace-nowrap font-bold text-slate-900">
                                        @if(count($ev['currency_totals']) === 1)
                                            {{ $ev['currency_totals'][0]['currency'] }} {{ number_format($ev['gross_revenue'], 2) }}
                                        @elseif(count($ev['currency_totals']) > 1)
                                            {{ __('See CSV currency breakdown') }}
                                        @else
                                            —
                                        @endif
                                        <div class="text-[10px] text-slate-400 font-normal">
                                            {{ $ev['orders_count'] }} {{ __('orders') }}
                                        </div>
                                    </td>

                                    {{-- Fees & Refunds --}}
                                    <td class="py-4 pr-4 text-right whitespace-nowrap text-xs text-slate-600">
                                        <div>
                                            @if(count($ev['currency_totals']) === 1)
                                                <span class="text-slate-400">{{ __('Fee') }}:</span> -{{ $ev['currency_totals'][0]['currency'] }} {{ number_format($ev['platform_fees'], 2) }}
                                            @else
                                                <span class="text-slate-400">{{ __('Fee') }}:</span> {{ __('See CSV currency breakdown') }}
                                            @endif
                                        </div>
                                        @if(is_numeric($ev['refunded_amount']) && $ev['refunded_amount'] > 0)
                                            <div class="text-rose-600 font-semibold mt-0.5">
                                                <span class="text-rose-400">{{ __('Ref') }}:</span> -{{ $ev['currency_totals'][0]['currency'] }} {{ number_format($ev['refunded_amount'], 2) }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Net Payout --}}
                                    <td class="py-4 pr-4 text-right whitespace-nowrap font-black text-emerald-700 text-sm">
                                        @if(count($ev['currency_totals']) === 1)
                                            {{ $ev['currency_totals'][0]['currency'] }} {{ number_format($ev['net_revenue'], 2) }}
                                        @elseif(count($ev['currency_totals']) > 1)
                                            {{ __('See CSV currency breakdown') }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- Categories Toggle Action --}}
                                    <td class="py-4 text-right whitespace-nowrap">
                                        @if(count($ev['categories']) > 0)
                                            <button @click="expandedEvents[{{ $ev['event_id'] }}] = !expandedEvents[{{ $ev['event_id'] }}]"
                                                    type="button"
                                                    class="btn-secondary text-xs py-1.5 px-3 rounded-lg inline-flex items-center gap-1 font-semibold">
                                                <span x-text="expandedEvents[{{ $ev['event_id'] }}] ? '{{ __('Hide Tiers') }}' : '{{ __('Tiers') }} ({{ count($ev['categories']) }})'"></span>
                                                <svg class="w-3.5 h-3.5 transition-transform duration-200"
                                                     :class="expandedEvents[{{ $ev['event_id'] }}] ? 'rotate-180' : ''"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-400">{{ __('Standard') }}</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Expanded Ticket Category Breakdown Row --}}
                                @if(count($ev['categories']) > 0)
                                    <tr x-show="expandedEvents[{{ $ev['event_id'] }}]" style="display: none;" class="bg-slate-50/70 border-b border-slate-200">
                                        <td colspan="8" class="p-4 sm:p-5">
                                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
                                                <div class="flex items-center justify-between text-xs font-bold text-slate-700 border-b border-slate-100 pb-2">
                                                    <span>{{ __('Ticket Categories Breakdown for') }} "{{ $ev['title'] }}"</span>
                                                    <span class="text-slate-400 text-[11px]">{{ count($ev['categories']) }} {{ __('ticket tiers configured') }}</span>
                                                </div>

                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full text-left text-xs">
                                                        <thead>
                                                            <tr class="border-b border-slate-100 text-slate-500 font-semibold uppercase tracking-wider">
                                                                <th class="pb-2">{{ __('Ticket Tier') }}</th>
                                                                <th class="pb-2">{{ __('Price') }}</th>
                                                                <th class="pb-2 text-center">{{ __('Allocated') }}</th>
                                                                <th class="pb-2 text-center">{{ __('Sold (Paid / Free)') }}</th>
                                                                <th class="pb-2 text-center">{{ __('Available') }}</th>
                                                                <th class="pb-2 text-right">{{ __('Gross Sales') }}</th>
                                                                <th class="pb-2 text-right">{{ __('Platform Fee') }}</th>
                                                                <th class="pb-2 text-right">{{ __('Net Revenue') }}</th>
                                                                <th class="pb-2 text-right">{{ __('Sell-Through') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-100">
                                                            @foreach($ev['categories'] as $cat)
                                                                <tr>
                                                                    <td class="py-2.5 font-bold text-slate-900">
                                                                        {{ $cat['name'] }}
                                                                    </td>
                                                                    <td class="py-2.5 text-slate-700 font-semibold">
                                                                        ${{ number_format($cat['price'], 2) }}
                                                                    </td>
                                                                    <td class="py-2.5 text-center text-slate-600">
                                                                        {{ $cat['allocated'] }}
                                                                    </td>
                                                                    <td class="py-2.5 text-center">
                                                                        <span class="font-bold text-slate-900">{{ $cat['total_sold'] }}</span>
                                                                        <span class="text-slate-400 text-[10px]">({{ $cat['paid_tickets'] }} / {{ $cat['free_tickets'] }})</span>
                                                                    </td>
                                                                    <td class="py-2.5 text-center font-medium {{ $cat['available'] > 0 ? 'text-slate-700' : 'text-rose-600 font-bold' }}">
                                                                        {{ $cat['available'] > 0 ? $cat['available'] : __('Sold Out') }}
                                                                    </td>
                                                                    <td class="py-2.5 text-right font-bold text-slate-900">
                                                                        ${{ number_format($cat['gross_revenue'], 2) }}
                                                                    </td>
                                                                    <td class="py-2.5 text-right text-slate-500">
                                                                        -${{ number_format($cat['platform_fees'], 2) }}
                                                                    </td>
                                                                    <td class="py-2.5 text-right font-black text-emerald-700">
                                                                        ${{ number_format($cat['net_revenue'], 2) }}
                                                                    </td>
                                                                    <td class="py-2.5 text-right font-mono font-medium text-slate-600">
                                                                        {{ $cat['occupancy_rate'] }}%
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-10 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                    <p class="text-sm font-semibold text-slate-700">{{ __('No events matching current filter.') }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ __('Try adjusting your date range or event filter.') }}</p>
                </div>
            @endif
        </div>

        {{-- Ticket Category Performance Matrix across all events --}}
        @if(!empty($category_breakdown) && count($category_breakdown) > 0)
            <div class="surface p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">{{ __('Ticket Category Performance Matrix') }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ __('Cross-event comparison of all ticket tiers, sales velocity, and gross vs net revenue.') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="relative w-full sm:w-56">
                            <input type="text"
                                   x-model="categorySearch"
                                   placeholder="{{ __('Search ticket tiers or events...') }}"
                                   class="w-full text-xs rounded-xl border-slate-200 pl-8 pr-2.5 py-1.5 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <a :href="exportUrl('categories')"
                           href="{{ route('vendor.analytics.export', array_merge(request()->query(), ['type' => 'categories'])) }}"
                           class="btn-secondary text-xs py-2 px-3.5 flex items-center gap-1.5 rounded-xl whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ __('Export Categories CSV') }}
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                                <th class="pb-3 pr-4">{{ __('Event Title') }}</th>
                                <th class="pb-3 pr-4">{{ __('Category / Tier') }}</th>
                                <th class="pb-3 pr-4">{{ __('Price') }}</th>
                                <th class="pb-3 pr-4 text-center">{{ __('Allocated') }}</th>
                                <th class="pb-3 pr-4 text-center">{{ __('Sold (Paid / Free)') }}</th>
                                <th class="pb-3 pr-4 text-center">{{ __('Available') }}</th>
                                <th class="pb-3 pr-4 text-right">{{ __('Gross Sales') }}</th>
                                <th class="pb-3 pr-4 text-right">{{ __('Platform Fee') }}</th>
                                <th class="pb-3 pr-4 text-right">{{ __('Net Revenue') }}</th>
                                <th class="pb-3 text-right">{{ __('Sell-Through') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($category_breakdown as $cat)
                                <tr class="hover:bg-slate-50/70 transition-colors"
                                    x-show="(!categorySearch || '{{ strtolower(addslashes($cat['category_name'] . ' ' . $cat['event_title'])) }}'.includes(categorySearch.toLowerCase())) && (!filters.event_id || filters.event_id == '{{ $cat['event_id'] }}')">
                                    <td class="py-3 pr-4 font-bold text-slate-900 max-w-xs truncate">
                                        {{ $cat['event_title'] }}
                                    </td>
                                    <td class="py-3 pr-4 text-slate-800 font-medium">
                                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-bold">
                                            {{ $cat['category_name'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 pr-4 text-slate-700 whitespace-nowrap font-semibold">
                                        {{ $cat['currency'] }} {{ number_format($cat['price'], 2) }}
                                    </td>
                                    <td class="py-3 pr-4 text-center text-slate-600">
                                        {{ $cat['allocated'] }}
                                    </td>
                                    <td class="py-3 pr-4 text-center whitespace-nowrap">
                                        <span class="font-bold text-slate-900">{{ $cat['total_sold'] }}</span>
                                        <span class="text-slate-400 text-[10px]">({{ $cat['paid_tickets'] }} / {{ $cat['free_tickets'] }})</span>
                                    </td>
                                    <td class="py-3 pr-4 text-center whitespace-nowrap font-medium {{ $cat['available'] > 0 ? 'text-slate-700' : 'text-rose-600 font-bold' }}">
                                        {{ $cat['available'] > 0 ? $cat['available'] : __('Sold Out') }}
                                    </td>
                                    <td class="py-3 pr-4 text-right font-bold text-slate-900 whitespace-nowrap">
                                        {{ $cat['currency'] }} {{ number_format($cat['gross_revenue'], 2) }}
                                    </td>
                                    <td class="py-3 pr-4 text-right text-slate-500 whitespace-nowrap">
                                        -{{ $cat['currency'] }} {{ number_format($cat['platform_fees'], 2) }}
                                    </td>
                                    <td class="py-3 pr-4 text-right font-black text-emerald-700 whitespace-nowrap">
                                        {{ $cat['currency'] }} {{ number_format($cat['net_revenue'], 2) }}
                                    </td>
                                    <td class="py-3 text-right font-mono font-medium text-slate-600">
                                        {{ $cat['occupancy_rate'] }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Filtered Ticket Orders & Transactions Table --}}
        <div class="surface p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">{{ __('Recent Ticket Orders & Transactions') }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('Individual ticket purchases with attendee identity, platform fee deduction, and net vendor payout.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative w-full sm:w-56">
                        <input type="text"
                               x-model="orderSearch"
                               placeholder="{{ __('Search orders by attendee, ID, or event...') }}"
                               class="w-full text-xs rounded-xl border-slate-200 pl-8 pr-2.5 py-1.5 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <a :href="exportUrl('orders')"
                       href="{{ route('vendor.analytics.export', array_merge(request()->query(), ['type' => 'orders'])) }}"
                       class="btn-secondary text-xs py-2 px-3.5 flex items-center gap-1.5 rounded-xl whitespace-nowrap">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ __('Export Orders CSV') }}
                    </a>
                </div>
            </div>

            @if($recent_orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="pb-3 pr-4">{{ __('Order ID') }}</th>
                                <th class="pb-3 pr-4">{{ __('Event') }}</th>
                                <th class="pb-3 pr-4">{{ __('Tier') }}</th>
                                <th class="pb-3 pr-4">{{ __('Attendee') }}</th>
                                <th class="pb-3 pr-4 text-center">{{ __('Qty') }}</th>
                                <th class="pb-3 pr-4 text-right">{{ __('Gross') }}</th>
                                <th class="pb-3 pr-4 text-right">{{ __('Fee') }}</th>
                                <th class="pb-3 pr-4 text-right">{{ __('Net') }}</th>
                                <th class="pb-3 pr-4">{{ __('Status') }}</th>
                                <th class="pb-3 text-right">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($recent_orders as $order)
                                @php
                                    $refund = (float) $order->refunded_amount > 0 ? (float) $order->refunded_amount : ($order->status === 'refunded' ? (float) $order->total : 0);
                                    $fee = (float) $order->total > 0 ? (float) $order->platform_fee * max(0, ((float) $order->total - $refund) / (float) $order->total) : 0;
                                    $net = in_array($order->status, ['paid', 'partially_refunded', 'refunded'], true) ? max(0, (float) $order->total - $refund - $fee) : 0.00;
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors"
                                    x-show="(!orderSearch || '{{ strtolower(addslashes('#' . str_pad($order->id, 5, '0', STR_PAD_LEFT) . ' ' . ($order->event?->title ?? '') . ' ' . ($order->user?->name ?? '') . ' ' . ($order->user?->email ?? '') . ' ' . ($order->items->first()?->ticketType?->name ?? ''))) }}'.includes(orderSearch.toLowerCase())) && (!filters.event_id || filters.event_id == '{{ $order->event_id }}')">
                                    <td class="py-3.5 pr-4 font-mono text-xs font-bold text-slate-600">
                                        #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="py-3.5 pr-4 max-w-[180px] truncate">
                                        @if($order->event)
                                            <a href="{{ route('events.show', $order->event->slug) }}" target="_blank" class="font-bold text-slate-900 hover:text-emerald-700 transition-colors truncate block">
                                                {{ $order->event->title }}
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 pr-4 text-xs text-slate-700">
                                        <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-800 font-medium">
                                            {{ $order->items->first()?->ticketType?->name ?? __('General Admission') }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-xs">
                                        <div class="font-bold text-slate-900">{{ $order->user?->name ?? __('Guest Buyer') }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $order->user?->email }}</div>
                                    </td>
                                    <td class="py-3.5 pr-4 text-center font-bold text-xs text-slate-900">
                                        {{ $order->items->sum(fn ($item) => max(0, (int) $item->quantity - ((int) $item->refunded_quantity ?: ($order->status === 'refunded' ? (int) $item->quantity : 0)))) }}
                                    </td>
                                    <td class="py-3.5 pr-4 text-right whitespace-nowrap text-xs font-bold text-slate-950">
                                        {{ strtoupper($order->currency ?: 'USD') }} {{ number_format($order->total, 2) }}
                                    </td>
                                    <td class="py-3.5 pr-4 text-right whitespace-nowrap text-xs text-slate-500">
                                        -{{ strtoupper($order->currency ?: 'USD') }} {{ number_format($order->platform_fee, 2) }}
                                    </td>
                                    <td class="py-3.5 pr-4 text-right whitespace-nowrap text-xs font-black text-emerald-700">
                                        {{ strtoupper($order->currency ?: 'USD') }} {{ number_format($net, 2) }}
                                    </td>
                                    <td class="py-3.5 pr-4 whitespace-nowrap text-xs">
                                        @if($order->status === 'partially_refunded')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 text-[11px] font-bold border border-amber-200">{{ __('Partially Refunded') }}</span>
                                        @elseif($order->status === 'paid')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 text-[11px] font-bold border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ __('Paid') }}
                                            </span>
                                        @elseif($order->status === 'refunded')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 text-[11px] font-bold border border-amber-200">
                                                {{ __('Refunded') }}
                                            </span>
                                        @elseif($order->status === 'cancelled')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-red-50 text-red-800 text-[11px] font-bold border border-red-200">
                                                {{ __('Cancelled') }}
                                            </span>
                                        @else
                                            <span class="badge text-[11px]">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 text-right whitespace-nowrap text-xs text-slate-500">
                                        {{ $order->created_at?->translatedFormat('M j, Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-slate-400 text-xs">
                    {{ __('No orders matching current filter criteria.') }}
                </div>
            @endif
        </div>

    </div>

    @push('scripts')
    {{-- Locally bundled Chart.js (Zero CDN fragility) --}}
    <script src="{{ asset('js/chart.umd.js') }}"></script>

    <script>
        window.vendorAnalyticsDashboard = function () {
            return {
                isLoading: false,
                isAutoRefresh: false,
                refreshTimer: null,
                lastUpdatedText: '{{ __('Updated just now') }}',
                chartMetric: 'net_revenue',
                compareMetric: 'revenue',
                eventSearch: '',
                categorySearch: '',
                orderSearch: '',
                expandedEvents: {},

                filters: {
                    event_id: '{{ $filters['event_id'] ?? '' }}',
                    date_range: '{{ $filters['date_range'] ?? 'all' }}',
                    status: '{{ $filters['status'] ?? 'all' }}',
                    from_date: '{{ $filters['from_date'] ?? '' }}',
                    to_date: '{{ $filters['to_date'] ?? '' }}'
                },

                overall: @json($overall),
                topEvent: @json($top_event),
                topCategory: @json($top_category),
                trendData: @json($trend_data),
                catChartData: @json($cat_chart_data),
                inventoryData: @json($inventory_chart_data),
                eventCompareData: @json($event_chart_data),

                // Chart instances
                trendChart: null,
                catChart: null,
                compareChart: null,
                invChart: null,

                init() {
                    const self = this;
                    window.currentAnalyticsDashboard = self;
                    window.updateTrendChart = function (metricKey) { self.updateTrendChart(metricKey); };
                    window.updateCompareChart = function (mode) { self.updateCompareChart(mode); };

                    this.$nextTick(() => {
                        self.initCharts();
                    });
                },

                initCharts() {
                    const self = this;
                    const modernPalette = [
                        '#10b981', '#06b6d4', '#6366f1', '#8b5cf6',
                        '#ec4899', '#f59e0b', '#3b82f6', '#14b8a6'
                    ];

                    // 1. Trend Chart
                    const trendCtx = document.getElementById('vendorTrendChart');
                    if (trendCtx && typeof Chart !== 'undefined') {
                        const ctx = trendCtx.getContext('2d');
                        const emeraldGradient = ctx.createLinearGradient(0, 0, 0, 300);
                        emeraldGradient.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
                        emeraldGradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

                        this.trendChart = new Chart(trendCtx, {
                            type: 'line',
                            data: {
                                labels: (this.trendData || []).map(d => d.label),
                                datasets: [this.getTrendDataset('net_revenue', emeraldGradient)]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: '#0f172a',
                                        titleFont: { size: 12, weight: 'bold' },
                                        bodyFont: { size: 11 },
                                        padding: 12,
                                        cornerRadius: 10,
                                        callbacks: {
                                            label: function (context) {
                                                const val = context.parsed.y;
                                                if (self.chartMetric !== 'tickets_sold') {
                                                    return ' ' + context.dataset.label + ': $' + Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
                                                }
                                                return ' ' + context.dataset.label + ': ' + val + ' tickets';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: { color: '#f1f5f9' },
                                        ticks: {
                                            font: { size: 11 },
                                            color: '#94a3b8',
                                            callback: function (val) {
                                                return self.chartMetric === 'tickets_sold' ? val : '$' + val;
                                            }
                                        }
                                    },
                                    x: {
                                        grid: { display: false },
                                        ticks: { font: { size: 10 }, color: '#94a3b8' }
                                    }
                                }
                            }
                        });
                    }

                    // 2. Category Donut Chart
                    const catCtx = document.getElementById('categoryDonutChart');
                    if (catCtx && typeof Chart !== 'undefined') {
                        this.catChart = new Chart(catCtx, {
                            type: 'doughnut',
                            data: {
                                labels: (this.catChartData?.labels || []),
                                datasets: [{
                                    data: (this.catChartData?.gross_revenue || []),
                                    backgroundColor: modernPalette,
                                    borderWidth: 2,
                                    borderColor: '#ffffff',
                                    hoverOffset: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '68%',
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            boxWidth: 10,
                                            font: { size: 10, weight: 600 },
                                            color: '#475569',
                                            padding: 10
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: '#0f172a',
                                        padding: 10,
                                        cornerRadius: 8,
                                        callbacks: {
                                            label: function (ctx) {
                                                const sum = (ctx.dataset.data || []).reduce((a, b) => a + Number(b || 0), 0);
                                                const pct = sum > 0 ? Math.round((ctx.parsed / sum) * 100) : 0;
                                                return ' ' + ctx.label + ': $' + Number(ctx.parsed || 0).toFixed(2) + ' (' + pct + '%)';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // 3. Event Comparison Bar Chart with Drill-down Click
                    const eventCompCtx = document.getElementById('eventCompareChart');
                    if (eventCompCtx && typeof Chart !== 'undefined') {
                        this.compareChart = new Chart(eventCompCtx, {
                            type: 'bar',
                            data: {
                                labels: (this.eventCompareData?.labels || []),
                                datasets: this.getCompareDatasets('revenue')
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                onClick: function (evt, elements) {
                                    if (elements && elements.length > 0) {
                                        const index = elements[0].index;
                                        const evId = self.eventCompareData?.event_ids?.[index];
                                        if (evId) {
                                            self.selectEvent(evId);
                                        }
                                    }
                                },
                                onHover: function (evt, chartElement) {
                                    if (evt.native && evt.native.target) {
                                        evt.native.target.style.cursor = (chartElement && chartElement.length > 0) ? 'pointer' : 'default';
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: {
                                            boxWidth: 12,
                                            font: { size: 11, weight: 'bold' },
                                            color: '#334155'
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: '#0f172a',
                                        padding: 12,
                                        cornerRadius: 10,
                                        callbacks: {
                                            title: function (ctx) {
                                                const idx = ctx[0].dataIndex;
                                                return self.eventCompareData?.full_labels?.[idx] || ctx[0].label;
                                            },
                                            label: function (ctx) {
                                                if (self.compareMetric === 'tickets') {
                                                    return ' ' + ctx.dataset.label + ': ' + ctx.parsed.y + ' tickets';
                                                }
                                                return ' ' + ctx.dataset.label + ': $' + Number(ctx.parsed.y || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
                                            },
                                            afterBody: function () {
                                                return ['\n👉 {{ __('Click any event bar to filter dashboard') }}'];
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: { color: '#f1f5f9' },
                                        ticks: {
                                            color: '#94a3b8',
                                            callback: function (v) {
                                                return self.compareMetric === 'tickets' ? v : '$' + v;
                                            }
                                        }
                                    },
                                    x: {
                                        grid: { display: false },
                                        ticks: {
                                            color: '#475569',
                                            font: { size: 10, weight: 600 }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // 4. Inventory Donut Chart
                    const invCtx = document.getElementById('inventoryDonutChart');
                    if (invCtx && typeof Chart !== 'undefined') {
                        this.invChart = new Chart(invCtx, {
                            type: 'doughnut',
                            data: {
                                labels: (this.inventoryData?.labels || []),
                                datasets: [{
                                    data: (this.inventoryData?.counts || []),
                                    backgroundColor: ['#10b981', '#0ea5e9', '#cbd5e1', '#f59e0b', '#ef4444'],
                                    borderWidth: 2,
                                    borderColor: '#ffffff',
                                    hoverOffset: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '72%',
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            boxWidth: 10,
                                            font: { size: 10, weight: 600 },
                                            color: '#475569',
                                            padding: 8
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: '#0f172a',
                                        padding: 10,
                                        cornerRadius: 8
                                    }
                                }
                            }
                        });
                    }
                },

                getTrendDataset(metricKey, gradient) {
                    const data = this.trendData || [];
                    if (metricKey === 'tickets_sold') {
                        return {
                            label: "{{ __('Tickets Sold') }}",
                            data: data.map(d => d.tickets_sold),
                            borderColor: '#8b5cf6',
                            backgroundColor: 'rgba(139, 92, 246, 0.15)',
                            borderWidth: 3,
                            pointBackgroundColor: '#8b5cf6',
                            pointHoverRadius: 6,
                            tension: 0.35,
                            fill: true
                        };
                    } else if (metricKey === 'gross_revenue') {
                        return {
                            label: "{{ __('Gross Sales') }} ($)",
                            data: data.map(d => d.gross_revenue),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.15)',
                            borderWidth: 3,
                            pointBackgroundColor: '#3b82f6',
                            pointHoverRadius: 6,
                            tension: 0.35,
                            fill: true
                        };
                    }
                    // Default: net_revenue
                    return {
                        label: "{{ __('Net Revenue') }} ($)",
                        data: data.map(d => d.net_revenue),
                        borderColor: '#10b981',
                        backgroundColor: gradient || 'rgba(16, 185, 129, 0.15)',
                        borderWidth: 3,
                        pointBackgroundColor: '#10b981',
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true
                    };
                },

                getCompareDatasets(mode) {
                    const data = this.eventCompareData || { gross_revenue: [], net_revenue: [], tickets_sold: [] };
                    if (mode === 'tickets') {
                        return [{
                            label: "{{ __('Tickets Sold') }} (Qty)",
                            data: data.tickets_sold || [],
                            backgroundColor: '#8b5cf6',
                            borderRadius: 8,
                            maxBarThickness: 40
                        }];
                    }
                    return [
                        {
                            label: "{{ __('Gross Revenue') }} ($)",
                            data: data.gross_revenue || [],
                            backgroundColor: '#3b82f6',
                            borderRadius: 8,
                            maxBarThickness: 34
                        },
                        {
                            label: "{{ __('Net Payout') }} ($)",
                            data: data.net_revenue || [],
                            backgroundColor: '#10b981',
                            borderRadius: 8,
                            maxBarThickness: 34
                        }
                    ];
                },

                updateTrendChart(metricKey) {
                    this.chartMetric = metricKey;
                    if (!this.trendChart) return;
                    this.trendChart.data.datasets = [this.getTrendDataset(metricKey)];
                    this.trendChart.options.scales.y.ticks.callback = function (v) {
                        return metricKey === 'tickets_sold' ? v : '$' + v;
                    };
                    this.trendChart.update();
                },

                updateCompareChart(mode) {
                    this.compareMetric = mode;
                    if (!this.compareChart) return;
                    this.compareChart.data.datasets = this.getCompareDatasets(mode);
                    this.compareChart.options.scales.y.ticks.callback = function (v) {
                        return mode === 'tickets' ? v : '$' + v;
                    };
                    this.compareChart.update();
                },

                updateAllCharts() {
                    if (this.trendChart) {
                        this.trendChart.data.labels = (this.trendData || []).map(d => d.label);
                        this.trendChart.data.datasets = [this.getTrendDataset(this.chartMetric)];
                        this.trendChart.update();
                    }
                    if (this.catChart) {
                        this.catChart.data.labels = (this.catChartData?.labels || []);
                        if (this.catChart.data.datasets[0]) {
                            this.catChart.data.datasets[0].data = (this.catChartData?.gross_revenue || []);
                        }
                        this.catChart.update();
                    }
                    if (this.compareChart) {
                        this.compareChart.data.labels = (this.eventCompareData?.labels || []);
                        this.compareChart.data.datasets = this.getCompareDatasets(this.compareMetric);
                        this.compareChart.update();
                    }
                    if (this.invChart) {
                        this.invChart.data.labels = (this.inventoryData?.labels || []);
                        if (this.invChart.data.datasets[0]) {
                            this.invChart.data.datasets[0].data = (this.inventoryData?.counts || []);
                        }
                        this.invChart.update();
                    }
                },

                async fetchData(showLoader = true) {
                    if (showLoader) this.isLoading = true;
                    try {
                        const params = new URLSearchParams({
                            format: 'json',
                            date_range: this.filters.date_range,
                            event_id: this.filters.event_id || '',
                            status: this.filters.status,
                            from_date: this.filters.from_date || '',
                            to_date: this.filters.to_date || ''
                        });

                        // Keep browser address bar query synced
                        const urlParams = new URLSearchParams(params);
                        urlParams.delete('format');
                        const newUrl = window.location.pathname + '?' + urlParams.toString();
                        window.history.replaceState({}, '', newUrl);

                        const response = await fetch('{{ route('vendor.analytics.index') }}?' + params.toString(), {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) throw new Error('Network response failed');
                        const data = await response.json();

                        // Reactive state updates
                        this.overall = data.overall || this.overall;
                        this.topEvent = data.top_event;
                        this.topCategory = data.top_category;
                        this.trendData = data.trend_data || [];
                        this.catChartData = data.cat_chart_data || { labels: [], gross_revenue: [] };
                        this.inventoryData = data.inventory_chart_data || { labels: [], counts: [] };
                        this.eventCompareData = data.event_chart_data || { labels: [], gross_revenue: [], net_revenue: [], tickets_sold: [] };

                        // Re-render and animate all 4 charts
                        this.updateAllCharts();

                        const now = new Date();
                        this.lastUpdatedText = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    } catch (err) {
                        console.error('Failed to update analytics:', err);
                    } finally {
                        if (showLoader) {
                            setTimeout(() => { this.isLoading = false; }, 200);
                        }
                    }
                },

                setPreset(range) {
                    this.filters.date_range = range;
                    if (range !== 'custom') {
                        this.filters.from_date = '';
                        this.filters.to_date = '';
                        this.fetchData(true);
                    }
                },

                selectEvent(id) {
                    this.filters.event_id = String(id);
                    this.fetchData(true);
                },

                clearEventFilter() {
                    this.filters.event_id = '';
                    this.fetchData(true);
                },

                toggleAutoRefresh() {
                    this.isAutoRefresh = !this.isAutoRefresh;
                    if (this.isAutoRefresh) {
                        this.refreshTimer = setInterval(() => {
                            this.fetchData(false);
                        }, 30000);
                    } else {
                        if (this.refreshTimer) {
                            clearInterval(this.refreshTimer);
                            this.refreshTimer = null;
                        }
                    }
                },

                exportUrl(type) {
                    const params = new URLSearchParams({
                        type: type,
                        date_range: this.filters.date_range,
                        event_id: this.filters.event_id || '',
                        status: this.filters.status,
                        from_date: this.filters.from_date || '',
                        to_date: this.filters.to_date || ''
                    });
                    return '{{ route('vendor.analytics.export') }}?' + params.toString();
                },

                formatMoney(val) {
                    return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                formatNumber(val) {
                    return Number(val || 0).toLocaleString('en-US');
                }
            };
        };
    </script>
    @endpush
</x-crm-layout>
