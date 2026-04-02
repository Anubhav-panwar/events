<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private TicketService $ticketService)
    {
    }

    public function createOrder(
        User $user,
        Event $event,
        int $ticketTypeId,
        int $quantity,
        ?int $referredByUserId = null,
        ?string $referralSource = null,
        ?string $paymentProvider = null,
        string $status = 'pending'
    ): Order {
        return DB::transaction(function () use (
            $user,
            $event,
            $ticketTypeId,
            $quantity,
            $referredByUserId,
            $referralSource,
            $paymentProvider,
            $status
        ) {
            $ticketType = TicketType::query()
                ->where('event_id', $event->id)
                ->lockForUpdate()
                ->findOrFail($ticketTypeId);

            $this->ensureSalesWindow($ticketType);
            $this->ensureAvailability($event, $ticketType, $quantity);

            $total = $quantity * (float) $ticketType->price;

            $order = Order::create([
                'user_id' => $user->id,
                'vendor_profile_id' => $event->vendorProfile->id,
                'event_id' => $event->id,
                'total' => $total,
                'currency' => $ticketType->currency,
                'status' => $status,
                'payment_provider' => $paymentProvider,
                'referred_by_user_id' => $referredByUserId,
                'referral_source' => $referralSource,
                'reserved_at' => now(),
                'paid_at' => $status === 'paid' ? now() : null,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'ticket_type_id' => $ticketType->id,
                'quantity' => $quantity,
                'unit_price' => $ticketType->price,
            ]);

            $ticketType->increment('sold', $quantity);

            if ($status === 'paid') {
                $this->ticketService->issueForOrder($order);
            }

            return $order->fresh('items.ticketType');
        });
    }

    public function markPaid(Order $order, ?string $paymentIntentId = null): Order
    {
        if ($order->status === 'paid') {
            return $order;
        }

        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_intent_id' => $paymentIntentId ?: $order->payment_intent_id,
            'failed_reason' => null,
        ]);

        $this->ticketService->issueForOrder($order->fresh('items.ticketType.event'));

        return $order->fresh('items.ticketType.event');
    }

    public function cancel(Order $order, ?string $failedReason = null): Order
    {
        if (in_array($order->status, ['cancelled', 'refunded'], true)) {
            return $order;
        }

        DB::transaction(function () use ($order, $failedReason) {
            $order->loadMissing('items.ticketType');

            if ($order->status !== 'paid') {
                foreach ($order->items as $item) {
                    if ($item->ticketType) {
                        $item->ticketType->decrement('sold', $item->quantity);
                    }
                }
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'failed_reason' => $failedReason,
            ]);
        });

        return $order->fresh('items.ticketType.event');
    }

    private function ensureSalesWindow(TicketType $ticketType): void
    {
        $now = now();

        if ($ticketType->sales_start && $now->lt($ticketType->sales_start)) {
            throw ValidationException::withMessages([
                'ticket_type_id' => 'Ticket sales have not started yet for this ticket type.',
            ]);
        }

        if ($ticketType->sales_end && $now->gt($ticketType->sales_end)) {
            throw ValidationException::withMessages([
                'ticket_type_id' => 'Ticket sales have ended for this ticket type.',
            ]);
        }
    }

    private function ensureAvailability(Event $event, TicketType $ticketType, int $quantity): void
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be at least 1.',
            ]);
        }

        $availableType = $ticketType->quantity - $ticketType->sold;
        $totalSold = TicketType::query()->where('event_id', $event->id)->sum('sold');
        $configuredQuantity = TicketType::query()->where('event_id', $event->id)->sum('quantity');
        $totalCapacity = $event->capacity ?? $configuredQuantity;
        $remainingEvent = max($totalCapacity - $totalSold, 0);

        if ($availableType < $quantity || $remainingEvent < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Event is full or not enough tickets are available.',
            ]);
        }
    }
}
