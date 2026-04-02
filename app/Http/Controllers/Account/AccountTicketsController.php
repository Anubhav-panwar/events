<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class AccountTicketsController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::query()
            ->whereHas('orderItem.order', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id)
                    ->where('status', 'paid');
            })
            ->with(['orderItem.ticketType.event.vendorProfile', 'orderItem.order'])
            ->latest('issued_at')
            ->paginate(20);

        return view('account.tickets.index', compact('tickets'));
    }

    public function show(Request $request, Ticket $ticket)
    {
        $ticket->loadMissing(['orderItem.ticketType.event.vendorProfile', 'orderItem.order.user']);
        abort_if($ticket->orderItem?->order?->user_id !== $request->user()->id, 403);

        return view('account.tickets.show', compact('ticket'));
    }
}
