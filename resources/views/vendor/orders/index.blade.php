<x-crm-layout title="Ticket Orders" role="vendor">
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('vendor.dashboard') }}" class="hover:text-slate-800">Vendor CRM</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">Ticket Orders</span>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6">

        {{-- Orders Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950">Ticket Orders &amp; Sales</h1>
                <p class="text-xs text-slate-500 mt-1">Review ticket purchases, attendee booking details, and payment statuses.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('vendor.checkin.form') }}" class="btn-secondary text-xs py-2 px-3.5 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    QR Ticket Validator
                </a>
            </div>
        </div>

        {{-- Orders Table Card --}}
        <div class="surface p-6">
            @if($orders && $orders->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="pb-3 pr-4">Order ID</th>
                                <th class="pb-3 pr-4">Event</th>
                                <th class="pb-3 pr-4">Ticket Tier</th>
                                <th class="pb-3 pr-4">Buyer / Attendee</th>
                                <th class="pb-3 pr-4 text-center">Qty</th>
                                <th class="pb-3 pr-4">Total Amount</th>
                                <th class="pb-3 pr-4">Payment Status</th>
                                <th class="pb-3 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($orders as $order)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 pr-4 font-mono text-xs font-bold text-slate-600">
                                        #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        @if($order->event)
                                            <a href="{{ route('events.show', $order->event->slug) }}" target="_blank" class="font-bold text-slate-900 hover:text-emerald-700 transition-colors truncate block max-w-[200px]">
                                                {{ $order->event->title }}
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 pr-4 text-xs text-slate-700">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-medium">
                                            {{ $order->items->first()?->ticketType?->name ?? 'General Admission' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 pr-4 text-xs">
                                        <div class="font-bold text-slate-900">{{ $order->user?->name ?? 'Guest Buyer' }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $order->user?->email }}</div>
                                    </td>
                                    <td class="py-3.5 pr-4 text-center font-bold text-xs text-slate-900">
                                        {{ $order->items->sum('quantity') }}
                                    </td>
                                    <td class="py-3.5 pr-4 whitespace-nowrap text-xs font-bold text-slate-950">
                                        ${{ number_format($order->total, 2) }} <span class="text-[10px] text-slate-400 font-normal uppercase">{{ $order->currency ?: 'USD' }}</span>
                                    </td>
                                    <td class="py-3.5 pr-4 whitespace-nowrap">
                                        @if($order->status === 'paid')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 text-[11px] font-bold border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Paid
                                            </span>
                                        @elseif($order->status === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 text-[11px] font-bold border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                            </span>
                                        @elseif($order->status === 'cancelled')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-red-50 text-red-800 text-[11px] font-bold border border-red-200">
                                                Cancelled
                                            </span>
                                        @else
                                            <span class="badge text-[11px]">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 text-right whitespace-nowrap text-xs text-slate-500">
                                        {{ $order->created_at?->format('M j, Y') }}
                                        <p class="text-[10px] text-slate-400">{{ $order->created_at?->format('g:i A') }}</p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto text-slate-400 mb-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">No ticket orders placed yet</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Attendee purchases for paid tickets or free registrations will be recorded here in real-time.</p>
                </div>
            @endif
        </div>

    </div>
</x-crm-layout>
