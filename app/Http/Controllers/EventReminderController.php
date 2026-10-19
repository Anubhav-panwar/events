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

        if ($request->user() && $request->user()->isBlocked()) {
            abort(403, __('Your account has been blocked by administrators. Setting reminders is restricted.'));
        }

        $data = $request->validate([
            'minutes_before' => ['nullable', 'integer', 'min:15', 'max:10080'],
            'channel' => ['nullable', 'in:calendar,email,push'],
        ]);

        $defaultChannel = !$event->isDateConfirmed() ? 'email' : 'calendar';

        $reminder = EventReminder::updateOrCreate(
            ['user_id' => $request->user()->id, 'event_id' => $event->id],
            [
                'minutes_before' => $data['minutes_before'] ?? 1440,
                'channel' => $data['channel'] ?? $defaultChannel,
                'status' => 'active',
                'notification_status' => 'pending',
                'notification_error' => null,
            ]
        );

        $isTba = !$event->isDateConfirmed();
        $message = $isTba
            ? __("You're subscribed! We'll email you when the event details are announced.")
            : __('Reminder preference saved.');

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'subscribed',
                'button_text' => __('Reminder Enabled'),
                'message' => $message,
                'reminder' => $reminder,
            ]);
        }

        return back()->with('status', $message);
    }

    public function destroy(Request $request, string $slug)
    {
        $event = Event::query()->firstWhere('slug', $slug);
        abort_unless($event, 404);

        EventReminder::query()
            ->where('user_id', $request->user()->id)
            ->where('event_id', $event->id)
            ->delete();

        $message = __('Reminder cancelled.');

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'unsubscribed',
                'button_text' => __('Notify Me'),
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }
}
