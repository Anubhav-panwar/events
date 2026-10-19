<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Repositories\VendorRepository;
use App\Services\VendorAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorAnalyticsController extends Controller
{
    public function __construct(
        private VendorRepository $vendorRepo,
        private VendorAnalyticsService $analyticsService
    ) {
    }

    public function index(Request $request)
    {
        Gate::authorize('vendor');
        $vendor = $this->vendorRepo->findByUser($request->user());
        abort_unless($vendor, 403);

        $filters = [
            'date_range' => $request->query('date_range', 'all'),
            'event_id' => $request->query('event_id'),
            'status' => $request->query('status', 'all'),
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
        ];

        $data = $this->analyticsService->getAnalytics($vendor, $filters);

        if ($request->wantsJson() || $request->ajax() || $request->query('format') === 'json') {
            return response()->json($data);
        }

        return view('vendor.analytics.index', $data);
    }

    public function export(Request $request)
    {
        Gate::authorize('vendor');
        $vendor = $this->vendorRepo->findByUser($request->user());
        abort_unless($vendor, 403);

        $type = $request->query('type', 'summary');
        $filters = [
            'date_range' => $request->query('date_range', 'all'),
            'event_id' => $request->query('event_id'),
            'status' => $request->query('status', 'all'),
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
        ];

        return match ($type) {
            'orders' => $this->analyticsService->exportOrdersCsv($vendor, $filters),
            'categories' => $this->analyticsService->exportCategoriesCsv($vendor, $filters),
            default => $this->analyticsService->exportSummaryCsv($vendor, $filters),
        };
    }
}
