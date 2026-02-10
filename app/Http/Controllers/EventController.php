<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Repositories\EventRepository;

class EventController extends Controller
{
    public function __construct(private EventRepository $repo)
    {
    }

    public function show(string $slug)
    {
        $event = $this->repo->findBySlug($slug);
        abort_unless($event, 404);
        $this->authorize('view', $event);
        return view('events.show', compact('event'));
    }
}
