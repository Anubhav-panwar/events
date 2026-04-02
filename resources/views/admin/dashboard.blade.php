<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Admin Dashboard</h2>
            <p class="page-subtitle">Platform-wide overview and management tools.</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content max-w-6xl space-y-6">
            @php
                $totalUsers = \App\Models\User::count();
                $totalVendors = \App\Models\VendorProfile::count();
                $totalEvents = \App\Models\Event::count();
                $publishedEvents = \App\Models\Event::where('status', 'published')->count();
                $totalOrders = \App\Models\Order::count();
                $paidOrders = \App\Models\Order::whereIn('status', ['paid'])->count();
                $totalRevenue = \App\Models\Order::whereIn('status', ['paid'])->sum('total');
            @endphp

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="surface p-5 text-center">
                    <div class="text-4xl font-bold text-slate-900">{{ number_format($totalUsers) }}</div>
                    <div class="text-sm text-slate-500 mt-1">Total Users</div>
                </div>
                <div class="surface p-5 text-center">
                    <div class="text-4xl font-bold text-emerald-600">{{ number_format($totalVendors) }}</div>
                    <div class="text-sm text-slate-500 mt-1">Vendors</div>
                </div>
                <div class="surface p-5 text-center">
                    <div class="text-4xl font-bold text-sky-600">{{ number_format($totalEvents) }}</div>
                    <div class="text-sm text-slate-500 mt-1">Events ({{ $publishedEvents }} live)</div>
                </div>
                <div class="surface p-5 text-center">
                    <div class="text-4xl font-bold text-purple-600">{{ number_format($paidOrders) }}</div>
                    <div class="text-sm text-slate-500 mt-1">Tickets Sold</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="surface p-5 md:col-span-2">
                    <h3 class="font-bold text-slate-900 mb-4">Total Revenue</h3>
                    <div class="text-4xl font-bold text-slate-900">{{ number_format($totalRevenue, 2) }} USD</div>
                    <p class="text-sm text-slate-500 mt-2">From {{ $paidOrders }} paid orders</p>
                </div>
                <div class="surface p-5">
                    <h3 class="font-bold text-slate-900 mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('vendors.index') }}" class="flex items-center gap-3 p-3 border border-slate-200 rounded hover:bg-slate-50 text-slate-700 font-bold transition-colors">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Browse Vendors
                        </a>
                        <a href="{{ route('events.index') }}" class="flex items-center gap-3 p-3 border border-slate-200 rounded hover:bg-slate-50 text-slate-700 font-bold transition-colors">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Browse Events
                        </a>
                    </div>
                </div>
            </div>

            {{-- Recent Events --}}
            <div class="surface p-6">
                <h3 class="font-bold text-slate-900 mb-4">Recent Events</h3>
                @php $recentEvents = \App\Models\Event::with('vendorProfile')->latest()->take(10)->get(); @endphp
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="pb-3 pr-4">Title</th>
                                <th class="pb-3 pr-4">Vendor</th>
                                <th class="pb-3 pr-4">Date</th>
                                <th class="pb-3 pr-4">Status</th>
                                <th class="pb-3">Type</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentEvents as $event)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 pr-4">
                                        <a href="{{ route('events.show', $event->slug) }}" class="font-medium text-emerald-700 hover:underline">
                                            {{ \Illuminate\Support\Str::limit($event->title, 40) }}
                                        </a>
                                    </td>
                                    <td class="py-3 pr-4 text-slate-600">{{ $event->vendorProfile?->business_name ?? '—' }}</td>
                                    <td class="py-3 pr-4 text-slate-500">{{ $event->event_date?->format('M j, Y') }}</td>
                                    <td class="py-3 pr-4">
                                        @if($event->status === 'published')
                                            <span class="badge">Published</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">Draft</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-slate-600 uppercase text-xs font-medium">{{ $event->event_type }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-slate-500">No events yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
