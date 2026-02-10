<?php

namespace App\Http\Controllers;

use App\Models\VendorProfile;
use Illuminate\Http\Request;

class FollowVendorController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $vendor = VendorProfile::firstWhere('slug', $slug);
        abort_unless($vendor, 404);
        $request->user()->followedVendors()->syncWithoutDetaching([$vendor->id]);
        return back()->with('status', 'Followed vendor');
    }

    public function destroy(Request $request, string $slug)
    {
        $vendor = VendorProfile::firstWhere('slug', $slug);
        abort_unless($vendor, 404);
        $request->user()->followedVendors()->detach($vendor->id);
        return back()->with('status', 'Unfollowed vendor');
    }
}
