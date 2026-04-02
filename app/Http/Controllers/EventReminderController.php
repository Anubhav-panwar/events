<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventReminder;
use Illuminate\Http\Request;

class EventReminderController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $event = Event::query()->firstWhere('slug', $slug);
        abort_unless($event, 404);
        $this->authorize('view', $event);

        $data = $request->validate([
            'minutes_before' => ['nullable', 'integer', 'min:15', 'max:10080'],
            'channel' => ['nullable', 'in:calendar,email,push'],
        ]);

        EventReminder::updateOrCreate(
            ['user_id' => $request->user()->id, 'event_id' => $event->id],
            [
                'minutes_before' => $data['minutes_before'] ?? 1440,
                'channel' => $data['channel'] ?? 'calendar',
                'status' => 'active',
            ]
        );

        return back()->with('status', 'Reminder preference saved.');
    }

    public function destroy(Request $request, string $slug)
    {
        $event = Event::query()->firstWhere('slug', $slug);
        abort_unless($event, 404);

        EventReminder::query()
            ->where('user_id', $request->user()->id)
            ->where('event_id', $event->id)
            ->delete();

        return back()->with('status', 'Reminder removed.');
    }
}
