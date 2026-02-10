<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CheckInController extends Controller
{
    public function form()
    {
        Gate::authorize('vendor');
        return view('vendor.checkin.form');
    }

    public function validateTicket(Request $request)
    {
        Gate::authorize('vendor');
        $request->validate(['code' => ['required', 'string']]);
        $ticket = Ticket::with('orderItem.order')->where('code', $request->input('code'))->first();
        if (!$ticket) {
            return back()->withErrors(['code' => 'Ticket not found']);
        }
        $vendorId = auth()->user()->vendorProfile?->id;
        if (!$vendorId || $ticket->orderItem->order->vendor_profile_id !== $vendorId) {
            return back()->withErrors(['code' => 'Ticket does not belong to your vendor']);
        }
        if ($ticket->checked_in_at) {
            return back()->withErrors(['code' => 'Ticket already checked in']);
        }
        $ticket->update(['checked_in_at' => now()]);
        return back()->with('status', 'Ticket valid. Checked in.');
    }
}
