<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    public const AUDIENCE_KIDS = 'Kids';
    public const AUDIENCE_TEENS = 'Teens';
    public const AUDIENCE_ADULTS = 'Adults';
    public const AUDIENCE_ALL_AGES = 'All Ages';

    public const AUDIENCE_CATEGORIES = [
        self::AUDIENCE_KIDS,
        self::AUDIENCE_TEENS,
        self::AUDIENCE_ADULTS,
        self::AUDIENCE_ALL_AGES,
    ];

    public static function normalizeAudienceCategory(string $value): ?string
    {
        $normalized = strtolower(trim($value));
        $map = [
            'kids' => self::AUDIENCE_KIDS,
            'children' => self::AUDIENCE_KIDS,
            'teens' => self::AUDIENCE_TEENS,
            'teenagers' => self::AUDIENCE_TEENS,
            'adults' => self::AUDIENCE_ADULTS,
            'all ages' => self::AUDIENCE_ALL_AGES,
            'all-ages' => self::AUDIENCE_ALL_AGES,
            'all_ages' => self::AUDIENCE_ALL_AGES,
            'all' => self::AUDIENCE_ALL_AGES,
        ];

        return $map[$normalized] ?? null;
    }

    protected $fillable = [
        'vendor_profile_id',
        'title',
        'description',
        'venue_name',
        'event_date',
        'start_time',
        'end_time',
        'address',
        'city',
        'country',
        'latitude',
        'longitude',
        'tags',
        'audience',
        'category_id',
        'capacity',
        'status',
        'is_featured',
        'event_type',
        'base_price',
        'is_date_confirmed',
        'dates_announced_at',
        'slug',
        'og_meta',
    ];

    protected $casts = [
        'event_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'latitude' => 'float',
        'longitude' => 'float',
        'tags' => 'array',
        'audience' => 'array',
        'is_featured' => 'boolean',
        'is_date_confirmed' => 'boolean',
        'dates_announced_at' => 'datetime',
        'base_price' => 'decimal:2',
        'og_meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title.'-'.Str::random(6));
            }
        });
    }

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function media()
    {
        return $this->hasMany(EventMedia::class);
    }

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }

    public function saves()
    {
        return $this->belongsToMany(User::class, 'event_saves');
    }

    public function shareClicks()
    {
        return $this->hasMany(EventShareClick::class);
    }

    public function referrals()
    {
        return $this->hasMany(EventReferral::class);
    }

    public function reminders()
    {
        return $this->hasMany(EventReminder::class);
    }

    public function reviews()
    {
        return $this->hasMany(EventReview::class);
    }

    public function averageRating(): float
    {
        return (float) round($this->reviews()->avg('rating') ?? 0, 1);
    }

    public function reviewsCount(): int
    {
        return (int) $this->reviews()->count();
    }

    public function getTitleAttribute($value): ?string
    {
        return $value ? __($value) : $value;
    }

    public function getDescriptionAttribute($value): ?string
    {
        return $value ? __($value) : $value;
    }

    public function getVenueNameAttribute($value): ?string
    {
        return $value ? __($value) : $value;
    }

    public function getAddressAttribute($value): ?string
    {
        return $value ? __($value) : $value;
    }

    public function getCityAttribute($value): ?string
    {
        return $value ? __($value) : $value;
    }

    public function getCountryAttribute($value): ?string
    {
        return $value ? __($value) : $value;
    }

    public function isDateConfirmed(): bool
    {
        if (array_key_exists('is_date_confirmed', $this->attributes) && !$this->is_date_confirmed) {
            return false;
        }

        if (empty($this->event_date) || empty($this->start_time)) {
            return false;
        }

        return true;
    }

    public function isDatesTba(): bool
    {
        return !$this->isDateConfirmed();
    }

    public function isBookingEligible(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if (!$this->isDateConfirmed()) {
            return false;
        }

        // Must have an explicit admission type configured
        if (empty($this->event_type)) {
            return false;
        }

        // For free events, registration is eligible once dates and timing are confirmed
        if ($this->event_type === 'free') {
            return true;
        }

        // For paid events, must have either positive base_price OR at least one active ticket type with price
        $hasBasePrice = $this->base_price !== null && (float) $this->base_price > 0;
        $hasActiveTicket = $this->ticketTypes()->where('is_active', true)->where('price', '>', 0)->exists()
            || ($this->relationLoaded('ticketTypes') && $this->ticketTypes->where('is_active', true)->where('price', '>', 0)->isNotEmpty());

        return $hasBasePrice || $hasActiveTicket;
    }

    public function getPublicationStatusLabelAttribute(): string
    {
        if ($this->status === 'draft') {
            return __('Draft');
        }

        if ($this->isDatesTba()) {
            return __('Published – Dates To Be Announced');
        }

        if ($this->isBookingEligible()) {
            return __('Booking Open');
        }

        return __('Published – Details Confirmed');
    }

    public function getBookingStatusLabelAttribute(): string
    {
        if (!$this->isDateConfirmed()) {
            return __('Booking: Not Available');
        }

        if (!$this->isBookingEligible()) {
            return __('Booking: Incomplete Details');
        }

        return __('Booking: Open');
    }

    public function isCalendarEligible(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        return $this->isDateConfirmed();
    }

    public function googleCalendarUrl(): ?string
    {
        if (!$this->isCalendarEligible()) {
            return null;
        }

        $eventDate = \Carbon\Carbon::parse($this->event_date);
        $startTimeStr = $this->start_time instanceof \Carbon\Carbon ? $this->start_time->format('H:i') : (string)$this->start_time;
        $endTimeStr = $this->end_time ? ($this->end_time instanceof \Carbon\Carbon ? $this->end_time->format('H:i') : (string)$this->end_time) : null;
        $startCarbon = (clone $eventDate)->setTimeFromTimeString($startTimeStr);
        $endCarbon = $endTimeStr ? (clone $eventDate)->setTimeFromTimeString($endTimeStr) : (clone $startCarbon)->addHours(2);

        $startUtc = $startCarbon->utc()->format('Ymd\THis\Z');
        $endUtc = $endCarbon->utc()->format('Ymd\THis\Z');

        $location = implode(', ', array_filter([$this->venue_name, $this->address, $this->city, $this->country]));
        $details = strip_tags($this->description ?? '') . "\n\n" . route('events.show', $this->slug);

        return 'https://calendar.google.com/calendar/render?' . http_build_query([
            'action' => 'TEMPLATE',
            'text' => $this->title,
            'dates' => "{$startUtc}/{$endUtc}",
            'details' => $details,
            'location' => $location,
        ]);
    }

    public function outlookCalendarUrl(): ?string
    {
        if (!$this->isCalendarEligible()) {
            return null;
        }

        $eventDate = \Carbon\Carbon::parse($this->event_date);
        $startTimeStr = $this->start_time instanceof \Carbon\Carbon ? $this->start_time->format('H:i') : (string)$this->start_time;
        $endTimeStr = $this->end_time ? ($this->end_time instanceof \Carbon\Carbon ? $this->end_time->format('H:i') : (string)$this->end_time) : null;
        $startCarbon = (clone $eventDate)->setTimeFromTimeString($startTimeStr);
        $endCarbon = $endTimeStr ? (clone $eventDate)->setTimeFromTimeString($endTimeStr) : (clone $startCarbon)->addHours(2);

        $location = implode(', ', array_filter([$this->venue_name, $this->address, $this->city, $this->country]));
        $details = strip_tags($this->description ?? '') . "\n\n" . route('events.show', $this->slug);

        return 'https://outlook.live.com/calendar/0/deeplink/compose?' . http_build_query([
            'path' => '/calendar/action/compose',
            'rru' => 'addevent',
            'subject' => $this->title,
            'startdt' => $startCarbon->toIso8601String(),
            'enddt' => $endCarbon->toIso8601String(),
            'body' => $details,
            'location' => $location,
        ]);
    }

    public function yahooCalendarUrl(): ?string
    {
        if (!$this->isCalendarEligible()) {
            return null;
        }

        $eventDate = \Carbon\Carbon::parse($this->event_date);
        $startTimeStr = $this->start_time instanceof \Carbon\Carbon ? $this->start_time->format('H:i') : (string)$this->start_time;
        $endTimeStr = $this->end_time ? ($this->end_time instanceof \Carbon\Carbon ? $this->end_time->format('H:i') : (string)$this->end_time) : null;
        $startCarbon = (clone $eventDate)->setTimeFromTimeString($startTimeStr);
        $endCarbon = $endTimeStr ? (clone $eventDate)->setTimeFromTimeString($endTimeStr) : (clone $startCarbon)->addHours(2);

        $location = implode(', ', array_filter([$this->venue_name, $this->address, $this->city, $this->country]));
        $details = strip_tags($this->description ?? '') . "\n\n" . route('events.show', $this->slug);

        return 'https://calendar.yahoo.com/?' . http_build_query([
            'v' => 60,
            'title' => $this->title,
            'st' => $startCarbon->utc()->format('Ymd\THis\Z'),
            'et' => $endCarbon->utc()->format('Ymd\THis\Z'),
            'desc' => $details,
            'in_loc' => $location,
        ]);
    }
}
