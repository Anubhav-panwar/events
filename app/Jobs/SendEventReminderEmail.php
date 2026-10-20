<?php

namespace App\Jobs;

use App\Mail\EventDatesAnnouncedMail;
use App\Models\EventReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendEventReminderEmail implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 600];

    public function __construct(public int $reminderId, public string $correlationId)
    {
        $this->afterCommit();
    }

    public function uniqueId(): string
    {
        return 'event-reminder-' . $this->reminderId;
    }

    public int $uniqueFor = 3600;

    public function handle(): void
    {
        $claimed = DB::transaction(function () {
            $reminder = EventReminder::query()->lockForUpdate()->find($this->reminderId);
            if (!$reminder || $reminder->status !== EventReminder::STATUS_ACTIVE ||
                !in_array($reminder->notification_status, [EventReminder::STATUS_QUEUED, EventReminder::STATUS_ATTEMPTED, EventReminder::STATUS_FAILED], true)) {
                return null;
            }

            $reminder->load(['user', 'event.media', 'event.ticketTypes', 'event.vendorProfile']);
            if (!$reminder->user || !$reminder->user->email || !$reminder->event ||
                $reminder->event->status !== 'published' || !$reminder->event->isDateConfirmed()) {
                $reminder->update([
                    'notification_status' => EventReminder::STATUS_FAILED,
                    'notification_error' => 'Recipient or confirmed event is unavailable.',
                ]);
                return null;
            }

            $reminder->update([
                'notification_status' => EventReminder::STATUS_ATTEMPTED,
                'attempted_at' => now(),
                'retry_count' => (int) $reminder->retry_count + 1,
                'notification_error' => null,
            ]);

            return $reminder;
        });

        if (!$claimed) {
            return;
        }

        try {
            Mail::to($claimed->user->email)->send(new EventDatesAnnouncedMail($claimed->event, $claimed->user, $claimed));
            $claimed->update([
                'notification_status' => EventReminder::STATUS_PROVIDER_ACCEPTED,
                'provider_accepted_at' => now(),
                'notified_at' => now(),
                'last_sent_at' => now(),
                'notification_error' => null,
            ]);
            Log::info('Event reminder accepted by configured mail transport', [
                'event_id' => $claimed->event_id,
                'subscription_id' => $claimed->id,
                'correlation_id' => $this->correlationId,
                'mailer' => config('mail.default'),
            ]);
        } catch (\Throwable $exception) {
            $claimed->update([
                'notification_error' => $this->safeError($exception),
            ]);
            throw $exception;
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $reminder = EventReminder::find($this->reminderId);
        if (!$reminder || $reminder->notification_status === EventReminder::STATUS_PROVIDER_ACCEPTED) {
            return;
        }

        $reminder->update([
            'notification_status' => EventReminder::STATUS_FAILED,
            'notification_error' => $exception ? $this->safeError($exception) : 'Queue attempts exhausted.',
        ]);
        Log::error('Event reminder job exhausted queue attempts', [
            'event_id' => $reminder->event_id,
            'subscription_id' => $reminder->id,
            'correlation_id' => $this->correlationId,
        ]);
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
