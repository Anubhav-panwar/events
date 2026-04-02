<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Orders</h2>
            <p class="page-subtitle">Ticket orders placed for your events.</p>
        </div>
    </x-slot>

    <section class="page-section">
        <div class="app-content max-w-6xl">
            <div class="surface p-6">
                @if($orders->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    <th class="pb-3 pr-4">Order #</th>
                                    <th class="pb-3 pr-4">Event</th>
                                    <th class="pb-3 pr-4">Ticket Type</th>
                                    <th class="pb-3 pr-4">Buyer</th>
                                    <th class="pb-3 pr-4">Qty</th>
                                    <th class="pb-3 pr-4">Amount</th>
                                    <th class="pb-3 pr-4">Status</th>
                                    <th class="pb-3">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($orders as $order)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 pr-4 font-mono text-xs text-slate-500">#{{ $order->id }}</td>
                                        <td class="py-3 pr-4">
                                            @if($order->event)
                                                <a href="{{ route('events.show', $order->event->slug) }}" class="font-semibold text-emerald-700 hover:underline">
                                                    {{ \Illuminate\Support\Str::limit($order->event->title, 35) }}
                                                </a>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 pr-4 text-slate-700">
                                            {{ $order->items->first()?->ticketType?->name ?? '—' }}
                                        </td>
                                        <td class="py-3 pr-4">
                                            <div class="text-slate-900 font-medium">{{ $order->user?->name ?? '—' }}</div>
                                            <div class="text-xs text-slate-400">{{ $order->user?->email }}</div>
                                        </td>
                                        <td class="py-3 pr-4 text-slate-700">{{ $order->items->sum('quantity') }}</td>
                                        <td class="py-3 pr-4 font-semibold text-slate-900">
                                            {{ number_format($order->total, 2) }} {{ strtoupper($order->currency ?: 'USD') }}
                                        </td>
                                        <td class="py-3 pr-4">
                                            @if($order->status === 'paid')
                                                <span class="badge">✅ Paid</span>
                                            @elseif($order->status === 'pending')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">⏳ Pending</span>
                                            @elseif($order->status === 'cancelled')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-xs font-medium">❌ Cancelled</span>
                                            @else
                                                <span class="badge bg-slate-100 text-slate-600">{{ ucfirst($order->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-xs text-slate-500">{{ $order->created_at?->format('M j, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-5">{{ $orders->links() }}</div>
                @else
                    <div class="text-center py-12">
                        <div class="text-5xl mb-4">📋</div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">No orders yet</h3>
                        <p class="text-slate-600 mb-5">Ticket orders for your events will appear here once users start purchasing.</p>
                        <a href="{{ route('vendor.events.create') }}" class="btn-primary">Create an Event</a>
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-app-layout>
