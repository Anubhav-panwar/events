<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventReferral;
use Illuminate\Http\Request;

class ReferralService
{
    public function captureFromRequest(Request $request, Event $event): ?int
    {
        $viewerId = $request->user()?->id;
        $referrerId = $this->extractReferrerId($request, $viewerId);

        if (!$referrerId) {
            $referrerId = (int) $request->session()->get($this->sessionKey($event), 0);
        }

        if (!$referrerId) {
            return null;
        }

        $request->session()->put($this->sessionKey($event), $referrerId);

        EventReferral::create([
            'event_id' => $event->id,
            'user_id' => $viewerId,
            'referrer_user_id' => $referrerId,
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return $referrerId;
    }

    public function getReferrerForEvent(Request $request, Event $event): ?int
    {
        $referrerId = (int) $request->session()->get($this->sessionKey($event), 0);
        return $referrerId > 0 ? $referrerId : null;
    }

    private function extractReferrerId(Request $request, ?int $viewerId): ?int
    {
        $refParam = $request->query('ref');
        if (!$refParam || !is_numeric($refParam)) {
            return null;
        }

        $referrerId = (int) $refParam;

        if ($referrerId <= 0 || ($viewerId && $viewerId === $referrerId)) {
            return null;
        }

        return $referrerId;
    }

    private function sessionKey(Event $event): string
    {
        return 'referral.event.' . $event->id;
    }
}
