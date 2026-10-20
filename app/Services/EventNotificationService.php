<?php

namespace App\Services;

use App\Jobs\SendEventReminderEmail;
use App\Models\Event;
use App\Models\EventReminder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EventNotificationService
{
    /** Claim the first announcement and queue one idempotent job per eligible subscriber. */
    public function notifySubscribersOfAnnouncedDates(Event $event, ?string $correlationId = null): int
    {
        $correlationId ??= (string) Str::uuid();

        return DB::transaction(function () use ($event, $correlationId) {
            $lockedEvent = Event::query()->lockForUpdate()->find($event->id);
            if (!$lockedEvent || $lockedEvent->status !== 'published' || !$lockedEvent->isDateConfirmed() || $lockedEvent->dates_announced_at) {
                Log::info('Event date announcement skipped', [
                    'event_id' => $event->id,
                    'correlation_id' => $correlationId,
                    'reason' => !$lockedEvent ? 'event_missing' : ($lockedEvent->dates_announced_at ? 'already_announced' : 'event_not_eligible'),
                ]);
                return 0;
            }

            $lockedEvent->dates_announced_at = now();
            $lockedEvent->saveQuietly();

            $reminders = EventReminder::query()
                ->where('event_id', $event->id)
                ->where('status', EventReminder::STATUS_ACTIVE)
                ->where('channel', EventReminder::CHANNEL_EMAIL)
                ->whereIn('notification_status', [EventReminder::STATUS_PENDING, EventReminder::STATUS_FAILED])
                ->where('created_at', '<=', $lockedEvent->dates_announced_at)
                ->lockForUpdate()
                ->get();

            foreach ($reminders as $reminder) {
                $reminder->update([
                    'notification_status' => EventReminder::STATUS_QUEUED,
                    'queued_at' => now(),
                    'notification_error' => null,
                ]);
                SendEventReminderEmail::dispatch($reminder->id, $correlationId)->afterCommit();
            }

            Log::info('Event date announcement jobs queued', [
                'event_id' => $event->id,
                'correlation_id' => $correlationId,
                'queued_count' => $reminders->count(),
            ]);

            return $reminders->count();
        });
    }

    /** Queue failed recipients again; the job records attempts and provider acceptance. */
    public function retryFailedNotifications(Event $event): int
    {
        if ($event->status !== 'published' || !$event->isDateConfirmed()) {
            return 0;
        }

        $correlationId = (string) Str::uuid();
        $queuedCount = 0;
        $reminders = EventReminder::query()
            ->where('event_id', $event->id)
            ->where('status', EventReminder::STATUS_ACTIVE)
            ->where('channel', EventReminder::CHANNEL_EMAIL)
            ->where('notification_status', EventReminder::STATUS_FAILED)
            ->get();

        foreach ($reminders as $reminder) {
            $updated = EventReminder::query()
                ->whereKey($reminder->id)
                ->where('notification_status', EventReminder::STATUS_FAILED)
                ->update([
                    'notification_status' => EventReminder::STATUS_QUEUED,
                    'queued_at' => now(),
                    'notification_error' => null,
                ]);
            if ($updated) {
                SendEventReminderEmail::dispatch($reminder->id, $correlationId)->afterCommit();
                $queuedCount++;
            }
        }

        return $queuedCount;
    }
}
