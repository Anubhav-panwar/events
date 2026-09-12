<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('admin');

        $tab = $request->query('tab', 'overview');
        if (!in_array($tab, ['overview', 'users', 'categories', 'events', 'vendors', 'financials'])) {
            $tab = 'overview';
        }

        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'all');
        $roleFilter = $request->query('role', 'all');
        $categoryId = $request->query('category_id');

        // Platform KPIs
        $totalUsers = User::count();
        $totalVendors = VendorProfile::count();
        $approvedVendors = VendorProfile::where('is_approved', true)->count();
        $totalEvents = Event::count();
        $publishedEvents = Event::where('status', 'published')->count();
        $draftEvents = Event::where('status', 'draft')->count();
        $totalOrders = Order::count();
        $paidOrders = Order::whereIn('status', ['paid'])->count();
        $totalRevenue = Order::whereIn('status', ['paid'])->sum('total');

        $kpis = [
            'total_users' => $totalUsers,
            'admin_users' => User::where('role', 'admin')->count(),
            'vendor_users' => User::where('role', 'vendor')->count(),
            'customer_users' => User::where('role', 'user')->count(),
            'total_vendors' => $totalVendors,
            'approved_vendors' => $approvedVendors,
            'total_events' => $totalEvents,
            'published_events' => $publishedEvents,
            'draft_events' => $draftEvents,
            'total_orders' => $totalOrders,
            'paid_orders' => $paidOrders,
            'revenue' => $totalRevenue,
            'total_categories' => Category::count(),
            'active_users' => User::whereNotNull('email_verified_at')->count(),
        ];

        // Users Query for 'users' tab
        $usersQuery = User::with(['vendorProfile'])->latest();
        if ($search !== '' && $tab === 'users') {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('vendorProfile', function ($vq) use ($search) {
                      $vq->where('business_name', 'like', "%{$search}%")
                         ->orWhere('city', 'like', "%{$search}%");
                  });
            });
        }
        if ($roleFilter !== 'all' && in_array($roleFilter, ['user', 'vendor', 'admin'])) {
            $usersQuery->where('role', $roleFilter);
        }
        $allUsers = $usersQuery->paginate(15)->withQueryString();
        $recentUsers = User::with('vendorProfile')->latest()->take(6)->get();

        // Events Query for 'events' or 'overview' tab
        $eventsQuery = Event::with(['vendorProfile', 'media', 'category'])->latest();

        if ($search !== '' && in_array($tab, ['overview', 'events'])) {
            $eventsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('venue_name', 'like', "%{$search}%")
                  ->orWhereHas('vendorProfile', function ($vq) use ($search) {
                      $vq->where('business_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status !== 'all') {
            $eventsQuery->where('status', $status);
        }

        if (!empty($categoryId)) {
            $eventsQuery->where('category_id', $categoryId);
        }

        $allEvents = $eventsQuery->paginate(12)->withQueryString();
        $recentEvents = Event::with(['vendorProfile', 'media'])->latest()->take(8)->get();

        // Vendors Query for 'vendors' tab
        $vendorsQuery = VendorProfile::with(['user'])->latest();
        if ($search !== '' && $tab === 'vendors') {
            $vendorsQuery->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        $allVendors = $vendorsQuery->paginate(15)->withQueryString();
        $recentVendors = VendorProfile::with('user')->latest()->take(6)->get();

        // Orders Query for 'financials' tab
        $ordersQuery = Order::with(['event', 'user'])->latest();
        $allOrders = $ordersQuery->paginate(15)->withQueryString();

        // Categories Query for 'categories' tab
        $categoriesQuery = Category::withCount(['events', 'vendorProfiles'])->orderBy('name');
        if ($search !== '' && $tab === 'categories') {
            $categoriesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }
        $allCategories = $categoriesQuery->paginate(15)->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('admin.dashboard', compact(
            'tab',
            'search',
            'status',
            'roleFilter',
            'categoryId',
            'kpis',
            'allUsers',
            'recentUsers',
            'allEvents',
            'recentEvents',
            'allVendors',
            'recentVendors',
            'allOrders',
            'categories',
            'allCategories'
        ));
    }
}
