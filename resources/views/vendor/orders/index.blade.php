<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl">Orders</h2>
            <a href="{{ route('vendor.dashboard') }}" class="btn-secondary">Back to Dashboard</a>
        </div>
    </x-slot>
    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="card p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left border-b">
                                <th class="py-2 pe-4">Buyer</th>
                                <th class="py-2 pe-4">Event</th>
                                <th class="py-2 pe-4">Ticket</th>
                                <th class="py-2 pe-4">Qty</th>
                                <th class="py-2 pe-4">Total</th>
                                <th class="py-2 pe-4">Status</th>
                                <th class="py-2 pe-4">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                @foreach($order->items as $item)
                                    @php
                                        $tt = $item->ticketType;
                                        $ev = $tt?->event;
                                    @endphp
                                    <tr class="border-b">
                                        <td class="py-2 pe-4">
                                            <div class="font-semibold">{{ $order->user?->name }}</div>
                                            <div class="text-slate-600">{{ $order->user?->email }}</div>
                                        </td>
                                        <td class="py-2 pe-4">
                                            @if($ev)
                                                <a class="text-blue-700" href="{{ route('events.show', $ev->slug) }}">{{ $ev->title }}</a>
                                            @endif
                                        </td>
                                        <td class="py-2 pe-4">
                                            {{ $tt?->name }}<br>
                                            <span class="text-slate-600">{{ number_format($item->unit_price, 2) }} {{ $tt?->currency }}</span>
                                        </td>
                                        <td class="py-2 pe-4">{{ $item->quantity }}</td>
                                        <td class="py-2 pe-4">{{ number_format($order->total, 2) }}</td>
                                        <td class="py-2 pe-4">{{ ucfirst($order->status) }}</td>
                                        <td class="py-2 pe-4">{{ $order->paid_at?->format('Y-m-d H:i') ?? $order->reserved_at?->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-slate-600">No orders yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
