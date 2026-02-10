<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorTicketController extends Controller
{
    public function create(Event $event)
    {
        Gate::authorize('update', $event);
        return view('vendor.tickets.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        Gate::authorize('update', $event);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'quantity' => ['required', 'integer', 'min:0'],
            'sales_start' => ['nullable', 'date'],
            'sales_end' => ['nullable', 'date', 'after_or_equal:sales_start'],
        ]);
        $data['event_id'] = $event->id;
        TicketType::create($data);
        return redirect()->route('vendor.events.edit', $event)->with('status', 'Ticket type created');
    }
}
