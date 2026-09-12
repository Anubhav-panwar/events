<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Repositories\VendorRepository;
use Illuminate\Support\Facades\Gate;

class VendorDashboardController extends Controller
{
    public function __construct(private VendorRepository $vendorRepo)
    {
    }

    public function index()
    {
        Gate::authorize('vendor');
        $user = auth()->user();
        $profile = $this->vendorRepo->findByUser($user);

        if (!$profile) {
            $baseSlug = \Illuminate\Support\Str::slug($user->name ?: 'vendor-' . $user->id);
            $slug = $baseSlug;
            $c = 1;
            while (\App\Models\VendorProfile::where('slug', $slug)->exists()) {
                $c++;
                $slug = "{$baseSlug}-{$c}";
            }

            $profile = \App\Models\VendorProfile::create([
                'user_id' => $user->id,
                'business_name' => $user->name . ' Events',
                'slug' => $slug,
                'is_approved' => true,
            ]);
        }

        // Strictly fetch events belonging ONLY to this vendor profile
        $events = $profile->events()
            ->where('vendor_profile_id', $profile->id)
            ->with(['media', 'ticketTypes', 'category'])
            ->latest()
            ->paginate(10);

        return view('vendor.dashboard', compact('profile', 'events'));
    }
}
