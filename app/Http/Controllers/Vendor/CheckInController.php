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
        $request->validate([
            'code' => ['required', 'string'],
            'mode' => ['nullable', 'string', 'in:checkin,verify'],
        ]);

        $code = trim($request->input('code'));
        $mode = $request->input('mode', 'checkin');

        $ticket = Ticket::with(['orderItem.order.user', 'orderItem.ticketType.event'])
            ->where('code', $code)
            ->first();

        if (!$ticket) {
            if ($request->wantsJson()) {
                return response()->json([
                    'valid' => false,
                    'status' => 'not_found',
                    'message' => 'Ticket code not found',
                ], 404);
            }
            return back()->withErrors(['code' => 'Ticket not found']);
        }

        $vendorId = auth()->user()->vendorProfile?->id;
        $order = $ticket->orderItem?->order;
        $orderVendorId = $order?->vendor_profile_id;

        if (!$vendorId || $orderVendorId !== $vendorId) {
            if ($request->wantsJson()) {
                return response()->json([
                    'valid' => false,
                    'status' => 'unauthorized',
                    'message' => 'Ticket does not belong to your vendor',
                ], 403);
            }
            return back()->withErrors(['code' => 'Ticket does not belong to your vendor']);
        }

        $ticketType = $ticket->ticketType;
        $event = $ticket->event;
        $attendeeName = $ticket->attendee_name ?: ($ticket->orderItem?->attendee_name ?: $order?->user?->name);

        $details = [
            'code' => $ticket->code,
            'attendee_name' => $attendeeName,
            'event_title' => $event?->title ?? 'Event',
            'tier' => $ticketType?->tier,
            'tier_meta' => $ticketType?->tierMeta(),
            'ticket_type_name' => $ticketType?->name ?? 'General Admission',
            'order_id' => $order?->id,
            'order_status' => $order?->status,
            'checked_in_at' => $ticket->checked_in_at?->format('M d, Y h:i A'),
        ];

        // 1. Payment Verification Check - ticket codes are valid only after payment confirmation
        if (!$order || !in_array($order->status, ['paid', 'partially_refunded'], true) || $ticket->refunded_at) {
            $statusMsg = "Ticket invalid. Order status is " . ($order ? strtoupper($order->status) : 'UNKNOWN') . ". Payment confirmation required.";
            if ($request->wantsJson()) {
                return response()->json([
                    'valid' => false,
                    'status' => 'invalid_payment',
                    'message' => $statusMsg,
                    'details' => $details,
                ], 422);
            }
            return back()->withErrors(['code' => $statusMsg])
                ->with('scanned_result', [
                    'valid' => false,
                    'type' => 'invalid_order',
                    'title' => 'Payment Unverified / Invalid Order',
                    'message' => $statusMsg,
                    'details' => $details,
                ]);
        }

        // 2. Anti-Reuse Check (Already checked in)
        if ($ticket->checked_in_at) {
            $usedTime = $ticket->checked_in_at->format('M d, Y \a\t h:i A');
            $statusMsg = "Ticket already used on {$usedTime}. Cannot reuse ticket.";
            if ($request->wantsJson()) {
                return response()->json([
                    'valid' => false,
                    'status' => 'already_used',
                    'message' => $statusMsg,
                    'details' => $details,
                ], 409);
            }
            return back()->withErrors(['code' => 'Ticket already checked in'])
                ->with('scanned_result', [
                    'valid' => false,
                    'type' => 'already_used',
                    'title' => 'Ticket Already Used (Anti-Reuse Safeguard)',
                    'message' => $statusMsg,
                    'details' => $details,
                ]);
        }

        // 3. Verify-only mode (Inspect ticket without consuming admission)
        if ($mode === 'verify') {
            $statusMsg = "Ticket verified: Valid and ready for admission.";
            if ($request->wantsJson()) {
                return response()->json([
                    'valid' => true,
                    'status' => 'verified_unconsumed',
                    'message' => $statusMsg,
                    'details' => $details,
                ]);
            }
            return back()->with('status', $statusMsg)
                ->with('scanned_result', [
                    'valid' => true,
                    'type' => 'verified_only',
                    'title' => 'Ticket Verified — Valid for Admission',
                    'message' => $statusMsg,
                    'details' => $details,
                ]);
        }

        // 4. Check-in & Admit
        $now = now();
        $claimed = Ticket::query()
            ->whereKey($ticket->id)
            ->whereNull('checked_in_at')
            ->whereNull('refunded_at')
            ->update(['checked_in_at' => $now]);
        if ($claimed !== 1) {
            $ticket->refresh();
            $usedTime = $ticket->checked_in_at?->format('M d, Y \\a\\t h:i A') ?? 'another scan';
            if ($request->wantsJson()) {
                return response()->json([
                    'valid' => false,
                    'status' => $ticket->refunded_at ? 'refunded' : 'already_used',
                    'message' => $ticket->refunded_at ? 'Ticket has been refunded.' : "Ticket already used on {$usedTime}. Cannot reuse ticket.",
                    'details' => $details,
                ], 409);
            }
            return back()->withErrors(['code' => $ticket->refunded_at ? 'Ticket has been refunded' : 'Ticket already checked in'])
                ->with('scanned_result', [
                    'valid' => false,
                    'type' => $ticket->refunded_at ? 'refunded' : 'already_used',
                    'title' => $ticket->refunded_at ? 'Ticket Refunded' : 'Ticket Already Used',
                    'message' => $ticket->refunded_at ? 'Ticket has been refunded.' : "Ticket already used on {$usedTime}. Cannot reuse ticket.",
                    'details' => $details,
                ]);
        }
        $details['checked_in_at'] = $now->format('M d, Y h:i A');

        $statusMsg = 'Ticket valid. Checked in.';
        if ($request->wantsJson()) {
            return response()->json([
                'valid' => true,
                'status' => 'checked_in',
                'message' => $statusMsg,
                'details' => $details,
            ]);
        }

        return back()->with('status', $statusMsg)
            ->with('scanned_result', [
                'valid' => true,
                'type' => 'checked_in',
                'title' => 'Admission Confirmed — Checked In',
                'message' => "Ticket {$ticket->code} verified and marked as admitted at " . $now->format('h:i:s A') . ".",
                'details' => $details,
            ]);
    }
}
