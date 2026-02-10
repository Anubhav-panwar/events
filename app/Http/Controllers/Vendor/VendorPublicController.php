<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;

class VendorPublicController extends Controller
{
    public function show(string $slug)
    {
        $vendor = VendorProfile::with(['events.media', 'categories'])->where('slug', $slug)->firstOrFail();
        $events = $vendor->events()->where('status', 'published')->orderBy('event_date')->paginate(9);
        return view('vendors.show', compact('vendor', 'events'));
    }
}
