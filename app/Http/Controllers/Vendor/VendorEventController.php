<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventStoreRequest;
use App\Models\Category;
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
        $categories = Category::query()->orderBy('name')->get();
        return view('vendor.events.create', compact('categories'));
    }

    public function store(EventStoreRequest $request)
    {
        Gate::authorize('vendor');
        $vendor = $this->vendorRepo->findByUser($request->user());
        if (!$vendor) {
            return back()->withErrors(['profile' => 'Create vendor profile first.']);
        }
        $mediaFiles = $request->file('media', []);
        $event = $this->service->createEvent($vendor, $this->normalizePayload($request->validated()), $mediaFiles);
        return redirect()->route('events.show', $event->slug)->with('status', 'Event created');
    }

    public function edit(Event $event)
    {
        Gate::authorize('update', $event);
        $event->load(['media', 'ticketTypes']);
        $categories = Category::query()->orderBy('name')->get();
        return view('vendor.events.edit', compact('event', 'categories'));
    }

    public function update(EventStoreRequest $request, Event $event)
    {
        Gate::authorize('update', $event);
        $mediaFiles = $request->file('media', []);
        $this->service->updateEvent($event, $this->normalizePayload($request->validated()), $mediaFiles);

        return redirect()->route('vendor.events.edit', $event)->with('status', 'Event updated');
    }

    public function destroy(Event $event)
    {
        Gate::authorize('update', $event);
        $event->delete();

        return redirect()->route('vendor.dashboard')->with('status', 'Event deleted');
    }

    public function publish(Event $event)
    {
        Gate::authorize('update', $event);
        $event->update(['status' => 'published']);

        return back()->with('status', 'Event published');
    }

    public function unpublish(Event $event)
    {
        Gate::authorize('update', $event);
        $event->update(['status' => 'draft']);

        return back()->with('status', 'Event moved to draft');
    }

    private function normalizePayload(array $data): array
    {
        unset($data['media']);

        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $data['base_price'] = isset($data['base_price']) ? (float) $data['base_price'] : null;

        foreach (['tags', 'audience'] as $field) {
            $value = $data[$field] ?? null;
            if (is_string($value)) {
                $data[$field] = array_values(array_filter(array_map(
                    fn ($part) => trim($part),
                    preg_split('/[,\\n]+/', $value) ?: []
                )));
            }
        }

        if (($data['event_type'] ?? 'free') === 'free') {
            $data['base_price'] = 0;
        }

        return $data;
    }
}
