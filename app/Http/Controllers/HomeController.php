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
        $upcoming = Event::query()
            ->where('status', 'published')
            ->whereDate('event_date', '>=', now()->toDateString())
            ->with(['media', 'vendorProfile'])
            ->orderBy('event_date')
            ->limit(9)
            ->get();

        $featured = Event::query()
            ->where('status', 'published')
            ->with(['media', 'vendorProfile'])
            ->latest('event_date')
            ->limit(3)
            ->get();

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
