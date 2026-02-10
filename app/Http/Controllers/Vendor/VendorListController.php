<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\VendorService;

class VendorListController extends Controller
{
    public function __construct(private VendorService $service)
    {
    }

    public function index()
    {
        $vendors = $this->service->getApprovedList(12);
        return view('vendors.index', compact('vendors'));
    }
}
