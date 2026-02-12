<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use App\Repositories\EventRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(private EventRepository $repo)
    {
    }

    public function buy(Request $request, string $slug)
    {
        $request->validate([
            'ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }
        $event = $this->repo->findBySlug($slug);
        abort_unless($event, 404);
        $ticketType = TicketType::where('event_id', $event->id)->findOrFail($request->integer('ticket_type_id'));

        try {
            DB::transaction(function () use ($user, $event, $ticketType, $request) {
                $qty = $request->integer('quantity');
                $availableType = $ticketType->quantity - $ticketType->sold;
                $totalSold = $event->ticketTypes->sum('sold');
                $totalCapacity = $event->capacity ?? $event->ticketTypes->sum('quantity');
                $remainingEvent = max($totalCapacity - $totalSold, 0);
                if ($availableType < $qty || $remainingEvent < $qty) {
                    abort(422, 'Event full or not enough tickets available');
                }
                $order = Order::create([
                    'user_id' => $user->id,
                    'vendor_profile_id' => $event->vendorProfile->id,
                    'total' => $qty * $ticketType->price,
                    'status' => 'paid',
                    'reserved_at' => now(),
                    'paid_at' => now(),
                ]);
                $item = OrderItem::create([
                    'order_id' => $order->id,
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => $qty,
                    'unit_price' => $ticketType->price,
                ]);
                $ticketType->increment('sold', $qty);
                for ($i = 0; $i < $qty; $i++) {
                    \App\Models\Ticket::create([
                        'order_item_id' => $item->id,
                        'code' => 'EV'.$event->id.'-'.strtoupper(\Illuminate\Support\Str::random(8)),
                        'issued_at' => now(),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['buy' => $e->getMessage()]);
        }

        return redirect()->route('events.show', $event->slug)->with('status', 'Tickets purchased successfully');
    }
}
