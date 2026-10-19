<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\VendorProfile;
use App\Models\VendorReview;
use Illuminate\Http\Request;

class VendorReviewController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $vendor = VendorProfile::where('slug', $slug)->firstOrFail();
        $user = $request->user();

        // 1. Prevent vendor from reviewing own profile
        if ((int) $vendor->user_id === (int) $user->id) {
            return back()->with('error', __('Vendors cannot review their own profile.'));
        }

        // 2. Verified customer check: user must have booked an event hosted by this vendor
        $vendorEventIds = $vendor->events()->pluck('id');
        $order = Order::where('user_id', $user->id)
            ->where(function ($q) use ($vendor, $vendorEventIds) {
                $q->where('vendor_profile_id', $vendor->id)
                  ->orWhereIn('event_id', $vendorEventIds);
            })
            ->where(function ($q) {
                $q->whereIn('status', ['paid', 'completed', 'active', 'confirmed', 'free'])
                  ->orWhereNull('status');
            })
            ->latest()
            ->first();

        if (!$order) {
            return back()->with('error', __('Only verified customers who have booked an event with this organizer can leave a review.'));
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['nullable', 'string', 'max:1500'],
        ]);

        VendorReview::updateOrCreate(
            [
                'vendor_profile_id' => $vendor->id,
                'user_id' => $user->id,
            ],
            [
                'order_id' => $order->id,
                'rating' => (int) $validated['rating'],
                'review' => !empty($validated['review']) ? trim($validated['review']) : null,
                'is_verified_customer' => true,
            ]
        );

        return back()->with('status', __('Thank you! Your verified organizer review has been published.'));
    }

    public function destroy(Request $request, string $slug, VendorReview $review)
    {
        $user = $request->user();
        abort_unless($review->user_id === $user->id || $user->isAdmin(), 403);

        $review->delete();

        return back()->with('status', __('Your review has been removed.'));
    }
}
