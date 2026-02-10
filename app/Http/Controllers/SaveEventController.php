<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class SaveEventController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $event = Event::firstWhere('slug', $slug);
        abort_unless($event, 404);
        $request->user()->savedEvents()->syncWithoutDetaching([$event->id]);
        return back()->with('status', 'Event saved');
    }

    public function destroy(Request $request, string $slug)
    {
        $event = Event::firstWhere('slug', $slug);
        abort_unless($event, 404);
        $request->user()->savedEvents()->detach($event->id);
        return back()->with('status', 'Event removed from saved');
    }
}
