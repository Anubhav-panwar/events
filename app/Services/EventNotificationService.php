<?php

namespace App\Services;

use App\Mail\EventDatesAnnouncedMail;
use App\Models\Event;
use App\Models\EventReminder;
use Illuminate\Support\Facades\DB;
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
    public function notifySubscribersOfAnnouncedDates(Event $event, ?string $correlationId = null): int
    {
        $correlationId ??= (string) Str::uuid();

        // 1. Must be published with valid confirmed schedule
        if ($event->status !== 'published' || !$event->isDateConfirmed()) {
            Log::info('Event date announcement skipped', [
                'event_id' => $event->id,
                'correlation_id' => $correlationId,
                'reason' => $event->status !== 'published' ? 'event_not_published' : 'schedule_not_confirmed',
            ]);
            return 0;
        }

        // Claim this initial announcement under a row lock so concurrent vendor saves cannot both send it.
        $claim = DB::transaction(function () use ($event) {
            $lockedEvent = Event::query()->lockForUpdate()->find($event->id);

            if (!$lockedEvent) {
                return ['claimed' => false, 'reason' => 'event_missing'];
            }
            if ($lockedEvent->status !== 'published') {
                return ['claimed' => false, 'reason' => 'event_not_published'];
            }
            if (!$lockedEvent->isDateConfirmed()) {
                return ['claimed' => false, 'reason' => 'schedule_not_confirmed'];
            }
            if (!empty($lockedEvent->dates_announced_at)) {
                return ['claimed' => false, 'reason' => 'already_announced'];
            }

            $lockedEvent->dates_announced_at = now();
            $lockedEvent->saveQuietly();

            return ['claimed' => true, 'announced_at' => $lockedEvent->dates_announced_at];
        });

        if (!$claim['claimed']) {
            Log::info('Event date announcement skipped', [
                'event_id' => $event->id,
                'correlation_id' => $correlationId,
                'reason' => $claim['reason'],
            ]);
            return 0;
        }

        $announcementTime = $claim['announced_at'];
        $event->refresh();

        // 3. Retrieve all active reminder subscriptions created before or at announcement
        $subscriptions = EventReminder::query()
            ->where('event_id', $event->id)
            ->where('status', EventReminder::STATUS_ACTIVE)
            ->where('channel', EventReminder::CHANNEL_EMAIL)
            ->whereIn('notification_status', [EventReminder::STATUS_PENDING, EventReminder::STATUS_FAILED])
            ->where('created_at', '<=', $announcementTime)
            ->with(['user', 'event.media', 'event.ticketTypes', 'event.vendorProfile'])
            ->get();

        Log::info('Event date announcement subscribers selected', [
            'event_id' => $event->id,
            'correlation_id' => $correlationId,
            'subscription_count' => $subscriptions->count(),
        ]);

        if ($subscriptions->isEmpty()) {
            Log::info('Event date announcement completed without recipients', [
                'event_id' => $event->id,
                'correlation_id' => $correlationId,
            ]);
            return 0;
        }

        $sentCount = 0;

        foreach ($subscriptions as $reminder) {
            $user = $reminder->user;
            if (!$user || empty($user->email)) {
                Log::warning('Event reminder skipped because recipient email is unavailable', [
                    'event_id' => $event->id,
                    'subscription_id' => $reminder->id,
                    'correlation_id' => $correlationId,
                ]);
                continue;
            }

            try {
                Log::info('Event reminder email send started', [
                    'event_id' => $event->id,
                    'subscription_id' => $reminder->id,
                    'correlation_id' => $correlationId,
                    'mailer' => config('mail.default'),
                ]);
                Mail::to($user->email)->send(new EventDatesAnnouncedMail($event, $user, $reminder));

                $reminder->update([
                    'notification_status' => EventReminder::STATUS_SENT,
                    'notified_at' => now(),
                    'last_sent_at' => now(),
                    'notification_error' => null,
                ]);

                $sentCount++;
                Log::info('Event reminder email accepted by mail transport', [
                    'event_id' => $event->id,
                    'subscription_id' => $reminder->id,
                    'correlation_id' => $correlationId,
                    'mailer' => config('mail.default'),
                ]);
            } catch (\Throwable $e) {
                $safeError = $this->safeError($e);
                $reminder->update([
                    'notification_status' => EventReminder::STATUS_FAILED,
                    'notification_error' => $safeError,
                    'retry_count' => ($reminder->retry_count ?? 0) + 1,
                ]);

                Log::error('Event reminder email send failed', [
                    'event_id' => $event->id,
                    'subscription_id' => $reminder->id,
                    'correlation_id' => $correlationId,
                    'error' => $safeError,
                ]);
            }
        }

        Log::info('Event date announcement processing completed', [
            'event_id' => $event->id,
            'correlation_id' => $correlationId,
            'accepted_count' => $sentCount,
            'recipient_count' => $subscriptions->count(),
        ]);

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
            Log::info('Event reminder retry skipped', [
                'event_id' => $event->id,
                'reason' => $event->status !== 'published' ? 'event_not_published' : 'schedule_not_confirmed',
            ]);
            return 0;
        }

        $failedSubscriptions = EventReminder::query()
            ->where('event_id', $event->id)
            ->where('status', EventReminder::STATUS_ACTIVE)
            ->where('channel', EventReminder::CHANNEL_EMAIL)
            ->where('notification_status', EventReminder::STATUS_FAILED)
            ->with(['user', 'event.media', 'event.ticketTypes', 'event.vendorProfile'])
            ->get();

        $successCount = 0;

        foreach ($failedSubscriptions as $reminder) {
            $user = $reminder->user;
            if (!$user || empty($user->email)) {
                continue;
            }

            try {
                Log::info('Event reminder retry send started', [
                    'event_id' => $event->id,
                    'subscription_id' => $reminder->id,
                ]);
                Mail::to($user->email)->send(new EventDatesAnnouncedMail($event, $user, $reminder));

                $reminder->update([
                    'notification_status' => EventReminder::STATUS_SENT,
                    'notified_at' => now(),
                    'last_sent_at' => now(),
                    'notification_error' => null,
                    'retry_count' => ($reminder->retry_count ?? 0) + 1,
                ]);

                $successCount++;
                Log::info('Event reminder retry accepted by mail transport', [
                    'event_id' => $event->id,
                    'subscription_id' => $reminder->id,
                ]);
            } catch (\Throwable $e) {
                $safeError = $this->safeError($e);
                $reminder->update([
                    'notification_status' => EventReminder::STATUS_FAILED,
                    'notification_error' => $safeError,
                    'retry_count' => ($reminder->retry_count ?? 0) + 1,
                ]);

                Log::error('Event reminder retry failed', [
                    'event_id' => $event->id,
                    'subscription_id' => $reminder->id,
                    'error' => $safeError,
                ]);
            }
        }

        return $successCount;
    }

    private function safeError(\Throwable $exception): string
    {
        $message = preg_replace('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', '[email]', $exception->getMessage()) ?? '';
        $message = preg_replace('/\b(password|passwd|secret|token|api[_-]?key)\s*[:=]\s*[^\s,;]+/i', '$1=[redacted]', $message) ?? '';
        $message = preg_replace('/\b(AUTH\s+(?:PLAIN|LOGIN)\s+)\S+/i', '$1[redacted]', $message) ?? '';
        $message = preg_replace('/(\b[a-z][a-z0-9+.-]*:\/\/)[^\s\/@:]+:[^\s\/@]+@/i', '$1[redacted]@', $message) ?? '';

        return Str::limit(class_basename($exception) . ($message !== '' ? ': ' . $message : ''), 500);
    }
}
