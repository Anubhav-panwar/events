<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\EventReminder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventDatesAnnouncedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public User $user,
        public ?EventReminder $reminder = null
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Event Dates Announced – ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.events.dates-announced',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
