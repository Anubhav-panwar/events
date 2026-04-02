<?php

namespace App\Http\Controllers;

use App\Repositories\EventRepository;
use App\Services\ReferralService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(private EventRepository $repo, private ReferralService $referralService)
    {
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

        return view('events.show', compact('event', 'isSaved', 'shareCounts'));
    }
}
