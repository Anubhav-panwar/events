<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchEventsRequest;
use App\Models\Category;
use App\Models\Event;
use App\Services\GeocodingService;

class SearchController extends Controller
{
    public function __construct(private GeocodingService $geocodingService)
    {
    }

    public function index(SearchEventsRequest $request)
    {
        $validated = $request->validated();
        $resolvedPlace = null;

        if (
            empty($validated['lat']) &&
            empty($validated['lng']) &&
            !empty($validated['place'])
        ) {
            $resolvedPlace = $this->geocodingService->geocode($validated['place']);
            if ($resolvedPlace) {
                $validated['lat'] = $resolvedPlace['lat'];
                $validated['lng'] = $resolvedPlace['lng'];
                $validated['radius_km'] = $validated['radius_km'] ?? 25;
            }
        }

        $priceExpr = '(CASE WHEN events.base_price IS NOT NULL THEN events.base_price ELSE (SELECT MIN(tt.price) FROM ticket_types tt WHERE tt.event_id = events.id) END)';

        $query = Event::query()
            ->select('events.*')
            ->where('status', 'published')
            ->with(['vendorProfile', 'ticketTypes', 'media', 'category']);

        if ($request->user()) {
            $query->withExists([
                'saves as is_saved' => fn ($saveQuery) => $saveQuery->where('users.id', $request->user()->id),
            ]);
        }

        if (!empty($validated['q'])) {
            $q = $validated['q'];
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%$q%")
                  ->orWhere('description', 'like', "%$q%")
                  ->orWhere('venue_name', 'like', "%$q%")
                  ->orWhere('address', 'like', "%$q%")
                  ->orWhere('city', 'like', "%$q%")
                  ->orWhere('tags', 'like', "%$q%")
                  ->orWhere('audience', 'like', "%$q%");
            });
        }

        if (!empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (!empty($validated['event_type'])) {
            $query->where('event_type', $validated['event_type']);
        }

        if (isset($validated['min_price'])) {
            $query->whereRaw("COALESCE({$priceExpr}, 0) >= ?", [(float) $validated['min_price']]);
        }

        if (isset($validated['max_price'])) {
            $query->whereRaw("COALESCE({$priceExpr}, 0) <= ?", [(float) $validated['max_price']]);
        }

        if (!empty($validated['start_date'])) {
            $query->whereDate('event_date', '>=', $validated['start_date']);
        }
        if (!empty($validated['end_date'])) {
            $query->whereDate('event_date', '<=', $validated['end_date']);
        }

        if (!empty($validated['city'])) {
            $query->where(function ($w) use ($validated) {
                $w->where('city', 'like', '%' . $validated['city'] . '%')
                    ->orWhere('address', 'like', '%' . $validated['city'] . '%');
            });
        }

        $lat = $validated['lat'] ?? null;
        $lng = $validated['lng'] ?? null;
        $radius = $validated['radius_km'] ?? null;
        $sort = $validated['sort'] ?? null;
        $hasDistanceSort = false;

        if ($lat !== null && $lng !== null && $radius !== null) {
            $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))";
            $query->select('events.*')
                ->selectRaw("$haversine AS distance", [$lat, $lng, $lat])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->having('distance', '<=', $radius);
            $hasDistanceSort = true;
        }

        switch ($sort) {
            case 'date_desc':
                $query->orderByDesc('event_date')->orderByDesc('start_time');
                break;
            case 'featured':
                $query->orderByDesc('is_featured')->orderBy('event_date');
                break;
            case 'price_asc':
                $query->orderByRaw("COALESCE({$priceExpr}, 0) asc");
                break;
            case 'price_desc':
                $query->orderByRaw("COALESCE({$priceExpr}, 0) desc");
                break;
            case 'distance':
                if ($hasDistanceSort) {
                    $query->orderBy('distance');
                    break;
                }
                $query->orderBy('event_date');
                break;
            case 'date_asc':
            default:
                if ($hasDistanceSort) {
                    $query->orderBy('distance');
                } else {
                    $query->orderBy('event_date')->orderBy('start_time');
                }
                break;
        }

        $events = $query->paginate(12)->appends($validated);
        $categories = Category::query()->orderBy('name')->get();

        return view('search.index', compact('events', 'resolvedPlace', 'categories'));
    }
}
