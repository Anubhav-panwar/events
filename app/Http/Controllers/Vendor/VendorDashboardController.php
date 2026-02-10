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
        $profile = $this->vendorRepo->findByUser(auth()->user());
        $events = $profile?->events()->latest()->paginate(10);
        return view('vendor.dashboard', compact('profile', 'events'));
    }
}
