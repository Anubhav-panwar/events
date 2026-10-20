<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventStoreRequest;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventMedia;
use App\Repositories\VendorRepository;
use App\Services\EventService;
use App\Services\GeocodingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorEventController extends Controller
{
    public function __construct(
        private EventService $service,
        private VendorRepository $vendorRepo,
        private GeocodingService $geocodingService
    ) {
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

    public function destroyMedia(Event $event, EventMedia $media)
    {
        Gate::authorize('update', $event);
        abort_unless((int) $media->event_id === (int) $event->id, 404);

        $this->service->deleteMedia($event, $media);

        return back()->with('status', __('Media file deleted successfully.'));
    }

    public function publish(Event $event)
    {
        Gate::authorize('update', $event);
        $wasAnnounced = !empty($event->dates_announced_at);
        $event->update(['status' => 'published']);
        $event->refresh();

        if (!$wasAnnounced && $event->isDateConfirmed()) {
            app(\App\Services\EventNotificationService::class)->notifySubscribersOfAnnouncedDates($event);
        }

        return back()->with('status', __('Event published'));
    }

    public function unpublish(Event $event)
    {
        Gate::authorize('update', $event);
        $event->update(['status' => 'draft']);

        return back()->with('status', __('Event moved to draft'));
    }

    public function retryReminders(Event $event)
    {
        Gate::authorize('update', $event);
        $count = app(\App\Services\EventNotificationService::class)->retryFailedNotifications($event);

        return back()->with('status', __(':count reminder notification(s) queued for retry.', ['count' => $count]));
    }

    private function normalizePayload(array $data): array
    {
        unset($data['media']);

        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $hasDate = !empty($data['event_date']) && !empty($data['start_time']);
        $confirmedInput = isset($data['is_date_confirmed']) ? (bool) $data['is_date_confirmed'] : false;
        $data['is_date_confirmed'] = $hasDate && $confirmedInput;

        $data['event_date'] = !empty($data['event_date']) ? $data['event_date'] : null;
        $data['start_time'] = !empty($data['start_time']) ? $data['start_time'] : null;
        $data['end_time'] = !empty($data['end_time']) ? $data['end_time'] : null;
        $data['event_type'] = $data['event_type'] ?? 'free';
        $data['base_price'] = isset($data['base_price']) && $data['base_price'] !== '' ? (float) $data['base_price'] : null;

        if (isset($data['tags'])) {
            $value = $data['tags'];
            if (is_string($value)) {
                $data['tags'] = array_values(array_filter(array_map(
                    fn ($part) => trim($part),
                    preg_split('/[,\\n]+/', $value) ?: []
                )));
            }
        }

        if (isset($data['audience'])) {
            $aud = $data['audience'];
            if (is_string($aud)) {
                $aud = preg_split('/[,\\n]+/', $aud) ?: [];
            }
            if (is_array($aud)) {
                $cleaned = [];
                foreach ($aud as $item) {
                    $item = trim((string) $item);
                    $canonical = Event::normalizeAudienceCategory($item) ?? $item;
                    if ($canonical !== '') {
                        $cleaned[] = $canonical;
                    }
                }
                $data['audience'] = array_values(array_unique($cleaned));
            }
        }

        if (($data['event_type'] ?? 'free') === 'free') {
            $data['base_price'] = 0;
        }

        if (empty($data['latitude']) || empty($data['longitude'])) {
            $parts = array_filter([
                $data['address'] ?? null,
                $data['city'] ?? null,
                $data['country'] ?? null,
            ]);
            if (!empty($parts)) {
                $geo = $this->geocodingService->geocode(implode(', ', $parts));
                if ($geo) {
                    $data['latitude'] = $geo['lat'];
                    $data['longitude'] = $geo['lng'];
                }
            }
        }

        return $data;
    }
}
