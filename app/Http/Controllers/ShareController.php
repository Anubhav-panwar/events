<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventShareClick;
use App\Models\VendorProfile;
use App\Models\VendorShareClick;
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

        $shareText = __('Check out :title on Huddle', ['title' => $event->title]);

        $targetUrl = match ($channel) {
            'whatsapp' => 'https://wa.me/?text=' . urlencode($event->title . ' ' . $shareableUrl),
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($shareableUrl),
            'x' => 'https://twitter.com/intent/tweet?text=' . urlencode($event->title) . '&url=' . urlencode($shareableUrl),
            'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($shareableUrl),
            'telegram' => 'https://t.me/share/url?url=' . urlencode($shareableUrl) . '&text=' . urlencode($event->title),
            'email' => 'mailto:?subject=' . rawurlencode($event->title) . '&body=' . rawurlencode($shareText . "\n" . $shareableUrl),
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

    public function vendorRedirect(Request $request, string $slug, string $channel)
    {
        $vendor = VendorProfile::query()->where('slug', $slug)->first();
        abort_unless($vendor, 404);

        abort_unless(in_array($channel, VendorShareClick::CHANNELS, true), 404);

        $shareableUrl = route('vendors.show', $vendor->slug);
        $shareText = __('Check out :vendor on Huddle', ['vendor' => $vendor->business_name]);

        $targetUrl = match ($channel) {
            'whatsapp' => 'https://wa.me/?text=' . urlencode($shareText . ' ' . $shareableUrl),
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($shareableUrl),
            'x' => 'https://twitter.com/intent/tweet?text=' . urlencode($shareText) . '&url=' . urlencode($shareableUrl),
            'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($shareableUrl),
            'telegram' => 'https://t.me/share/url?url=' . urlencode($shareableUrl) . '&text=' . urlencode($shareText),
            'email' => 'mailto:?subject=' . rawurlencode($shareText) . '&body=' . rawurlencode($shareText . "\n" . $shareableUrl),
            'copy' => $shareableUrl,
            default => $shareableUrl,
        };

        VendorShareClick::create([
            'vendor_profile_id' => $vendor->id,
            'user_id' => $request->user()?->id,
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
