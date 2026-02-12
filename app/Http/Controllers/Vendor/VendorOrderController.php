<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\VendorRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorOrderController extends Controller
{
    public function __construct(private VendorRepository $vendorRepo)
    {
    }

    public function index(Request $request)
    {
        Gate::authorize('vendor');
        $vendor = $this->vendorRepo->findByUser($request->user());
        abort_unless($vendor, 403);
        $orders = Order::query()
            ->where('vendor_profile_id', $vendor->id)
            ->with(['user', 'items.ticketType.event'])
            ->latest()
            ->paginate(20);
        return view('vendor.orders.index', compact('orders', 'vendor'));
    }
}
