<?php

namespace App\Http\Controllers;

use App\Repositories\EventRepository;
use App\Services\EventRecommendationService;
use App\Services\ReferralService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private EventRepository $repo,
        private ReferralService $referralService,
        private EventRecommendationService $recommendationService
    ) {
    }

    public function show(Request $request, string $slug)
    {
        $event = $this->repo->findBySlug($slug);
        abort_unless($event, 404);
        $this->authorize('view', $event);

        $this->referralService->captureFromRequest($request, $event);

        $isSaved = false;
        if ($request->user()) {
            $isSaved = $request->user()->savedEvents()->where('event_id', $event->id)->exists();
        }

        $shareCounts = $event->shareClicks()
            ->selectRaw('channel, COUNT(*) as clicks')
            ->groupBy('channel')
            ->pluck('clicks', 'channel');

        $recommendedEvents = $this->recommendationService->getRecommendations($event, $request->user(), 4);

        if ($request->wantsJson() && !$request->hasHeader('X-Inertia')) {
            return response()->json([
                'event' => $event,
                'recommendations' => $recommendedEvents,
            ]);
        }

        $event->load(['reviews.user']);

        $user = $request->user();
        $userReview = $user ? $event->reviews->firstWhere('user_id', $user->id) : null;

        $canReview = false;
        $reviewEligibilityMessage = null;

        if ($user) {
            if ($user->vendorProfile && (int) $event->vendor_profile_id === (int) $user->vendorProfile->id) {
                $canReview = false;
                $reviewEligibilityMessage = __('Organizers cannot review their own events.');
            } else {
                $hasOrder = \App\Models\Order::where('user_id', $user->id)
                    ->where('event_id', $event->id)
                    ->where(function ($q) {
                        $q->whereIn('status', ['paid', 'completed', 'active', 'confirmed', 'free'])
                          ->orWhereNull('status');
                    })
                    ->exists();

                $hasTicket = \App\Models\Ticket::whereHas('orderItem', function ($q) use ($user, $event) {
                    $q->whereHas('order', fn ($oq) => $oq->where('user_id', $user->id))
                      ->whereHas('ticketType', fn ($tq) => $tq->where('event_id', $event->id));
                })->exists();

                if ($hasOrder || $hasTicket) {
                    $canReview = true;
                } else {
                    $reviewEligibilityMessage = __('Only verified attendees who booked tickets for this event can leave a review.');
                }
            }
        } else {
            $reviewEligibilityMessage = __('Sign in with your ticket-booking account to leave a review.');
        }

        $reviewsCount = $event->reviews->count();
        $averageRating = $reviewsCount > 0 ? (float) round($event->reviews->avg('rating'), 1) : 0.0;

        $ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($event->reviews as $r) {
            if (isset($ratingCounts[$r->rating])) {
                $ratingCounts[$r->rating]++;
            }
        }

        return view('events.show', compact(
            'event',
            'isSaved',
            'shareCounts',
            'recommendedEvents',
            'userReview',
            'canReview',
            'reviewEligibilityMessage',
            'averageRating',
            'reviewsCount',
            'ratingCounts'
        ));
    }

    public function recommendations(Request $request, string $slug)
    {
        $event = $this->repo->findBySlug($slug);
        abort_unless($event, 404);
        $this->authorize('view', $event);

        $limit = max(1, min((int) $request->input('limit', 4), 12));
        $recommendedEvents = $this->recommendationService->getRecommendations($event, $request->user(), $limit);

        return response()->json([
            'event' => [
                'id' => $event->id,
                'slug' => $event->slug,
                'title' => $event->title,
            ],
            'count' => $recommendedEvents->count(),
            'recommendations' => $recommendedEvents->map(fn ($rec) => [
                'id' => $rec->id,
                'slug' => $rec->slug,
                'title' => $rec->title,
                'category' => $rec->category?->name,
                'event_date' => $rec->event_date?->toDateString(),
                'start_time' => $rec->start_time instanceof \Carbon\Carbon ? $rec->start_time->format('H:i') : (string) $rec->start_time,
                'city' => $rec->city,
                'address' => $rec->address,
                'base_price' => $rec->base_price,
                'event_type' => $rec->event_type,
                'vendor_name' => $rec->vendorProfile?->business_name,
                'image_url' => $rec->media->where('type', 'image')->first()?->url,
                'url' => route('events.show', $rec->slug),
                'score' => $rec->recommendation_score ?? 0,
                'reasons' => $rec->recommendation_reasons ?? [],
            ]),
        ]);
    }
}
