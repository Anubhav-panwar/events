<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorProfileRequest;
use App\Models\Category;
use App\Repositories\VendorRepository;
use App\Services\VendorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorProfileController extends Controller
{
    public function __construct(private VendorService $service, private VendorRepository $repo)
    {
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        Gate::authorize('vendor');
        $profile = $this->repo->findByUser($user);
        $categories = Category::all();
        return view('vendor.profile.edit', compact('profile', 'categories'));
    }

    public function update(VendorProfileRequest $request)
    {
        $user = $request->user();
        Gate::authorize('vendor');
        $profile = $this->service->getOrCreateProfile($user, $request->validated());
        return redirect()->route('vendor.profile.edit')->with('status', 'Profile saved');
    }
}
