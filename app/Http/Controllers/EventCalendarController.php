<?php

namespace App\Http\Controllers;

use App\Repositories\EventRepository;
use Carbon\Carbon;
use Illuminate\Http\Response;

class EventCalendarController extends Controller
{
    public function __construct(private EventRepository $repo)
    {
    }

    public function ics(string $slug)
    {
        $event = $this->repo->findBySlug($slug);
        abort_unless($event, 404);
        $this->authorize('view', $event);

        if (!$event->isCalendarEligible()) {
            abort(422, __('Calendar integration is unavailable for events whose dates are not yet confirmed.'));
        }

        $eventDate = Carbon::parse($event->event_date);
        $startTimeStr = $event->start_time instanceof Carbon ? $event->start_time->format('H:i') : (string)$event->start_time;
        $endTimeStr = $event->end_time ? ($event->end_time instanceof Carbon ? $event->end_time->format('H:i') : (string)$event->end_time) : null;
        $start = (clone $eventDate)->setTimeFromTimeString($startTimeStr)->utc();
        $end = $endTimeStr ? (clone $eventDate)->setTimeFromTimeString($endTimeStr)->utc() : (clone $start)->addHours(2);

        $location = implode(', ', array_filter([$event->venue_name, $event->address, $event->city, $event->country]));
        $description = trim(strip_tags($event->description ?? ''))."\n\n".route('events.show', $event->slug);

        $ics = "BEGIN:VCALENDAR\r\n".
            "VERSION:2.0\r\n".
            "PRODID:-//Huddle Events//EN\r\n".
            "CALSCALE:GREGORIAN\r\n".
            "METHOD:PUBLISH\r\n".
            "BEGIN:VEVENT\r\n".
            "UID:event-{$event->id}-{$event->slug}@huddle.events\r\n".
            "DTSTAMP:".now()->utc()->format('Ymd\THis\Z')."\r\n".
            "DTSTART:".$start->format('Ymd\THis\Z')."\r\n".
            "DTEND:".$end->format('Ymd\THis\Z')."\r\n".
            "SUMMARY:".addcslashes($event->title, ",;")."\r\n".
            "DESCRIPTION:".addcslashes($description, ",;")."\r\n".
            "LOCATION:".addcslashes($location, ",;")."\r\n".
            "URL:".route('events.show', $event->slug)."\r\n".
            "STATUS:CONFIRMED\r\n".
            "END:VEVENT\r\n".
            "END:VCALENDAR\r\n";

        return new Response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$event->slug.'.ics"',
        ]);
    }
}
