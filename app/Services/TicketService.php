<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use Illuminate\Support\Str;

class TicketService
{
    public function issueForOrder(Order $order): void
    {
        $order->loadMissing('items.ticketType.event');

        foreach ($order->items as $item) {
            $this->issueForItem($order, $item);
        }
    }

    private function issueForItem(Order $order, OrderItem $item): void
    {
        $existing = $item->tickets()->count();
        $missing = max($item->quantity - $existing, 0);

        if ($missing === 0) {
            return;
        }

        for ($i = 0; $i < $missing; $i++) {
            $code = $this->generateUniqueCode($order->event_id ?: $item->ticketType?->event_id);

            Ticket::create([
                'order_item_id' => $item->id,
                'code' => $code,
                'attendee_name' => $order->user?->name,
                'qr_data' => $this->buildQrPayload($order, $item, $code),
                'issued_at' => now(),
            ]);
        }
    }

    private function generateUniqueCode(?int $eventId): string
    {
        do {
            $prefix = $eventId ? 'EV' . $eventId : 'EVX';
            $code = $prefix . '-' . strtoupper(Str::random(10));
        } while (Ticket::query()->where('code', $code)->exists());

        return $code;
    }

    private function buildQrPayload(Order $order, OrderItem $item, string $code): string
    {
        return json_encode([
            'ticket_code' => $code,
            'order_id' => $order->id,
            'event_id' => $order->event_id ?: $item->ticketType?->event_id,
            'issued_at' => now()->toIso8601String(),
        ], JSON_UNESCAPED_SLASHES);
    }
}
