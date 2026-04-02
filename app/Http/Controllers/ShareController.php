<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventShareClick;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function redirect(Request $request, string $slug, string $channel)
    {
        $event = Event::query()->firstWhere('slug', $slug);
        abort_unless($event, 404);
        $this->authorize('view', $event);

        abort_unless(in_array($channel, EventShareClick::CHANNELS, true), 404);

        $refId = $request->query('ref');
        if (!$refId && $request->user()) {
            $refId = $request->user()->id;
        }

        $shareableUrl = route('events.show', [
            'slug' => $event->slug,
            'ref' => $refId ?: null,
        ]);

        $targetUrl = match ($channel) {
            'whatsapp' => 'https://wa.me/?text=' . urlencode($event->title . ' ' . $shareableUrl),
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($shareableUrl),
            'x' => 'https://twitter.com/intent/tweet?text=' . urlencode($event->title) . '&url=' . urlencode($shareableUrl),
            'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($shareableUrl),
            'copy' => $shareableUrl,
            default => $shareableUrl,
        };

        EventShareClick::create([
            'event_id' => $event->id,
            'user_id' => $request->user()?->id,
            'referrer_user_id' => is_numeric($refId) ? (int) $refId : null,
            'channel' => $channel,
            'target_url' => $targetUrl,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        if ($channel === 'copy') {
            return response()->json([
                'url' => $shareableUrl,
            ]);
        }

        return redirect()->away($targetUrl);
    }
}
