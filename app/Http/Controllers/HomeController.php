<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\VendorProfile;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $upcomingQuery = Event::query()
            ->where('status', 'published')
            ->whereDate('event_date', '>=', now()->toDateString())
            ->with(['media', 'vendorProfile', 'ticketTypes'])
            ->orderBy('event_date')
            ->limit(9)
            ;

        $featuredQuery = Event::query()
            ->where('status', 'published')
            ->where('is_featured', true)
            ->with(['media', 'vendorProfile', 'ticketTypes'])
            ->latest('event_date')
            ->limit(3);

        if (auth()->check()) {
            $upcomingQuery->withExists([
                'saves as is_saved' => fn ($q) => $q->where('users.id', auth()->id()),
            ]);
            $featuredQuery->withExists([
                'saves as is_saved' => fn ($q) => $q->where('users.id', auth()->id()),
            ]);
        }

        $upcoming = $upcomingQuery->get();

        $featured = $featuredQuery->get();

        $popularCities = VendorProfile::query()
            ->where('is_approved', true)
            ->whereNotNull('city')
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->limit(8)
            ->pluck('city');

        $stats = [
            'vendors' => VendorProfile::query()->where('is_approved', true)->count(),
            'events' => Event::query()->where('status', 'published')->count(),
            'cities' => $popularCities->count(),
        ];

        return view('home.index', compact('categories', 'upcoming', 'featured', 'popularCities', 'stats'));
    }
}
