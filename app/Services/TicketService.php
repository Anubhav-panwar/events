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
        // Enforce: ticket codes are generated ONLY after successful payment verification and booking confirmation
        if ($order->status !== 'paid') {
            return;
        }

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

        $eventId = $order->event_id ?: $item->ticketType?->event_id;
        $tier = $item->ticketType?->tier;

        for ($i = 0; $i < $missing; $i++) {
            $code = Ticket::generateUniqueCode($eventId, $tier);

            Ticket::create([
                'order_item_id' => $item->id,
                'code' => $code,
                'attendee_name' => $order->user?->name ?? __('Guest Attendee'),
                'qr_data' => $this->buildQrPayload($order, $item, $code),
                'issued_at' => now(),
            ]);
        }
    }

    private function generateUniqueCode(?int $eventId): string
    {
        return Ticket::generateUniqueCode($eventId);
    }

    private function buildQrPayload(Order $order, OrderItem $item, string $code): string
    {
        // QR must carry only the opaque code. Validation resolves ownership and
        // ticket details server-side after the code is submitted.
        return $code;
    }
}
