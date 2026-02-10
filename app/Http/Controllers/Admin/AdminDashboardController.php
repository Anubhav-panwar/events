<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Support\Facades\Gate;

class AdminDashboardController extends Controller
{
    public function index()
    {
        Gate::authorize('admin');
        $kpis = [
            'total_vendors' => VendorProfile::count(),
            'total_events' => Event::count(),
            'total_orders' => Order::count(),
            'revenue' => Order::where('status', 'paid')->sum('total'),
            'active_users' => User::whereNotNull('email_verified_at')->count(),
        ];
        return view('admin.dashboard', compact('kpis'));
    }
}
