<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;

class VendorPublicController extends Controller
{
    public function show(string $slug)
    {
        $vendor = VendorProfile::with(['events.media', 'categories', 'media'])->where('slug', $slug)->firstOrFail();
        $this->authorize('view', $vendor);

        $events = $vendor->events()
            ->where('status', 'published')
            ->whereDate('event_date', '>=', now()->toDateString())
            ->with(['media', 'ticketTypes', 'vendorProfile'])
            ->orderBy('event_date')
            ->paginate(9);

        if (auth()->check()) {
            $saveIds = auth()->user()->savedEvents()->pluck('events.id')->all();
            $events->getCollection()->transform(function ($event) use ($saveIds) {
                $event->is_saved = in_array($event->id, $saveIds, true);
                return $event;
            });
        }

        $vendor->load(['reviews.user']);

        $user = auth()->user();
        $userVendorReview = $user ? $vendor->reviews->firstWhere('user_id', $user->id) : null;

        $canReviewVendor = false;
        $vendorReviewEligibilityMessage = null;

        if ($user) {
            if ((int) $vendor->user_id === (int) $user->id) {
                $canReviewVendor = false;
                $vendorReviewEligibilityMessage = __('Organizers cannot review their own profile.');
            } else {
                $vendorEventIds = $vendor->events()->pluck('id');
                $hasBooked = \App\Models\Order::where('user_id', $user->id)
                    ->where(function ($q) use ($vendor, $vendorEventIds) {
                        $q->where('vendor_profile_id', $vendor->id)
                          ->orWhereIn('event_id', $vendorEventIds);
                    })
                    ->where(function ($q) {
                        $q->whereIn('status', ['paid', 'completed', 'active', 'confirmed', 'free'])
                          ->orWhereNull('status');
                    })
                    ->exists();

                if ($hasBooked) {
                    $canReviewVendor = true;
                } else {
                    $vendorReviewEligibilityMessage = __('Only verified customers who have booked an event with this organizer can leave a review.');
                }
            }
        } else {
            $vendorReviewEligibilityMessage = __('Sign in to leave a verified review for this organizer.');
        }

        $vendorReviewsCount = $vendor->reviews->count();
        $vendorAverageRating = $vendorReviewsCount > 0 ? (float) round($vendor->reviews->avg('rating'), 1) : 0.0;

        $vendorRatingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($vendor->reviews as $r) {
            if (isset($vendorRatingCounts[$r->rating])) {
                $vendorRatingCounts[$r->rating]++;
            }
        }

        $shareCounts = $vendor->shareClicks()
            ->selectRaw('channel, count(*) as cnt')
            ->groupBy('channel')
            ->pluck('cnt', 'channel')
            ->all();

        return view('vendors.show', compact(
            'vendor',
            'events',
            'userVendorReview',
            'canReviewVendor',
            'vendorReviewEligibilityMessage',
            'vendorAverageRating',
            'vendorReviewsCount',
            'vendorRatingCounts',
            'shareCounts'
        ));
    }
}
