<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventStoreRequest;
use App\Models\Event;
use App\Repositories\VendorRepository;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorEventController extends Controller
{
    public function __construct(private EventService $service, private VendorRepository $vendorRepo)
    {
    }

    public function create(Request $request)
    {
        Gate::authorize('vendor');
        return view('vendor.events.create');
    }

    public function store(EventStoreRequest $request)
    {
        Gate::authorize('vendor');
        $vendor = $this->vendorRepo->findByUser($request->user());
        if (!$vendor) {
            return back()->withErrors(['profile' => 'Create vendor profile first.']);
        }
        $mediaFiles = $request->file('media', []);
        $event = $this->service->createEvent($vendor, $request->validated(), $mediaFiles);
        return redirect()->route('events.show', $event->slug)->with('status', 'Event created');
    }

    public function edit(Event $event)
    {
        Gate::authorize('update', $event);
        return view('vendor.events.edit', compact('event'));
    }
}
