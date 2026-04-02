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
            ->with(['media', 'ticketTypes'])
            ->orderBy('event_date')
            ->paginate(9);

        if (auth()->check()) {
            $saveIds = auth()->user()->savedEvents()->pluck('events.id')->all();
            $events->getCollection()->transform(function ($event) use ($saveIds) {
                $event->is_saved = in_array($event->id, $saveIds, true);
                return $event;
            });
        }

        return view('vendors.show', compact('vendor', 'events'));
    }
}
