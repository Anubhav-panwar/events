<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventReview;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\Request;

class EventReviewController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $user = $request->user();
        if ($user && $user->isBlocked()) {
            abort(403, __('Your account has been blocked by administrators.'));
        }

        // 1. Prevent host vendor from reviewing own event
        if ($user->vendorProfile && (int) $event->vendor_profile_id === (int) $user->vendorProfile->id) {
            return back()->with('error', __('Organizers cannot review their own events.'));
        }

        // 2. Verified attendee check
        $order = Order::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->where(function ($q) {
                $q->whereIn('status', ['paid', 'completed', 'active', 'confirmed', 'free'])
                  ->orWhereNull('status');
            })
            ->latest()
            ->first();

        $hasTicket = Ticket::whereHas('orderItem', function ($q) use ($user, $event) {
            $q->whereHas('order', fn ($oq) => $oq->where('user_id', $user->id))
              ->whereHas('ticketType', fn ($tq) => $tq->where('event_id', $event->id));
        })->exists();

        if (!$order && !$hasTicket) {
            return back()->with('error', __('Only verified attendees who booked tickets for this event can leave a review.'));
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['nullable', 'string', 'max:1500'],
        ]);

        EventReview::updateOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $user->id,
            ],
            [
                'order_id' => $order?->id,
                'rating' => (int) $validated['rating'],
                'review' => !empty($validated['review']) ? trim($validated['review']) : null,
                'is_verified_attendee' => true,
            ]
        );

        return back()->with('status', __('Thank you! Your verified review has been published.'));
    }

    public function destroy(Request $request, string $slug, EventReview $review)
    {
        $user = $request->user();
        abort_unless($review->user_id === $user->id || $user->isAdmin(), 403);

        $review->delete();

        return back()->with('status', __('Your review has been removed.'));
    }
}
