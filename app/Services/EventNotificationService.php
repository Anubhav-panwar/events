<?php

namespace App\Services;

use App\Mail\EventDatesAnnouncedMail;
use App\Models\Event;
use App\Models\EventReminder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EventNotificationService
{
    /**
     * Notify all eligible subscribed users that event dates and details have been confirmed.
     *
     * @param Event $event
     * @return int Number of emails successfully delivered
     */
    public function notifySubscribersOfAnnouncedDates(Event $event): int
    {
        // 1. Must be published with valid confirmed schedule
        if ($event->status !== 'published' || !$event->isDateConfirmed()) {
            return 0;
        }

        // 2. Prevent duplicate notifications if already announced
        if (!empty($event->dates_announced_at)) {
            return 0;
        }

        $announcementTime = now();
        $event->dates_announced_at = $announcementTime;
        $event->saveQuietly();

        // 3. Retrieve all active reminder subscriptions created before or at announcement
        $subscriptions = EventReminder::query()
            ->where('event_id', $event->id)
            ->where('status', 'active')
            ->where('notification_status', '!=', 'sent')
            ->where('created_at', '<=', $announcementTime)
            ->with(['user', 'event.media', 'event.ticketTypes', 'event.vendorProfile'])
            ->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $sentCount = 0;

        foreach ($subscriptions as $reminder) {
            $user = $reminder->user;
            if (!$user || empty($user->email)) {
                continue;
            }

            try {
                Mail::to($user->email)->send(new EventDatesAnnouncedMail($event, $user, $reminder));

                $reminder->update([
                    'notification_status' => 'sent',
                    'notified_at' => now(),
                    'last_sent_at' => now(),
                    'notification_error' => null,
                ]);

                $sentCount++;
            } catch (\Throwable $e) {
                $reminder->update([
                    'notification_status' => 'failed',
                    'notification_error' => Str::limit($e->getMessage(), 500),
                    'retry_count' => ($reminder->retry_count ?? 0) + 1,
                ]);

                Log::error("Failed to send date announcement email to {$user->email} for event {$event->id}: " . $e->getMessage(), [
                    'exception' => $e,
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                ]);
            }
        }

        return $sentCount;
    }

    /**
     * Retry sending failed notifications for an event.
     *
     * @param Event $event
     * @return int Number of successfully delivered retries
     */
    public function retryFailedNotifications(Event $event): int
    {
        if ($event->status !== 'published' || !$event->isDateConfirmed()) {
            return 0;
        }

        $failedSubscriptions = EventReminder::query()
            ->where('event_id', $event->id)
            ->where('status', 'active')
            ->where('notification_status', 'failed')
            ->with(['user', 'event.media', 'event.ticketTypes', 'event.vendorProfile'])
            ->get();

        $successCount = 0;

        foreach ($failedSubscriptions as $reminder) {
            $user = $reminder->user;
            if (!$user || empty($user->email)) {
                continue;
            }

            try {
                Mail::to($user->email)->send(new EventDatesAnnouncedMail($event, $user, $reminder));

                $reminder->update([
                    'notification_status' => 'sent',
                    'notified_at' => now(),
                    'last_sent_at' => now(),
                    'notification_error' => null,
                    'retry_count' => ($reminder->retry_count ?? 0) + 1,
                ]);

                $successCount++;
            } catch (\Throwable $e) {
                $reminder->update([
                    'notification_status' => 'failed',
                    'notification_error' => Str::limit($e->getMessage(), 500),
                    'retry_count' => ($reminder->retry_count ?? 0) + 1,
                ]);

                Log::error("Retry failed for date announcement email to {$user->email} for event {$event->id}: " . $e->getMessage());
            }
        }

        return $successCount;
    }
}
