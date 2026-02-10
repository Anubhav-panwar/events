<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchEventsRequest;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(SearchEventsRequest $request)
    {
        $validated = $request->validated();
        $query = Event::query()
            ->where('status', 'published')
            ->with('vendorProfile');

        if (!empty($validated['q'])) {
            $q = $validated['q'];
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%$q%")
                  ->orWhere('description', 'like', "%$q%")
                  ->orWhere('address', 'like', "%$q%");
            });
        }

        if (!empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (!empty($validated['start_date'])) {
            $query->whereDate('event_date', '>=', $validated['start_date']);
        }
        if (!empty($validated['end_date'])) {
            $query->whereDate('event_date', '<=', $validated['end_date']);
        }

        if (!empty($validated['city'])) {
            $query->where('address', 'like', '%'.$validated['city'].'%');
        }

        $lat = $validated['lat'] ?? null;
        $lng = $validated['lng'] ?? null;
        $radius = $validated['radius_km'] ?? null;

        if ($lat !== null && $lng !== null && $radius !== null) {
            $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))";
            $query->select('*')
                ->selectRaw("$haversine AS distance", [$lat, $lng, $lat])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->having('distance', '<=', $radius)
                ->orderBy('distance');
        } else {
            $query->orderBy('event_date');
        }

        $events = $query->paginate(12)->appends($validated);
        return view('search.index', compact('events'));
    }
}
