<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventReminder extends Model
{
    use HasFactory;

    public const CHANNEL_EMAIL = 'email';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DISABLED = 'disabled';

    public const STATUS_PENDING = 'pending';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_ATTEMPTED = 'attempted';
    public const STATUS_PROVIDER_ACCEPTED = 'provider_accepted';
    public const STATUS_SENT = self::STATUS_PROVIDER_ACCEPTED;
    public const STATUS_NOTIFIED = self::STATUS_PROVIDER_ACCEPTED;
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'event_id',
        'minutes_before',
        'channel',
        'status',
        'notification_status',
        'notified_at',
        'last_sent_at',
        'queued_at',
        'attempted_at',
        'provider_accepted_at',
        'notification_error',
        'retry_count',
    ];

    protected $casts = [
        'minutes_before' => 'integer',
        'notified_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'queued_at' => 'datetime',
        'attempted_at' => 'datetime',
        'provider_accepted_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isNotified(): bool
    {
        return in_array($this->notification_status, [self::STATUS_PROVIDER_ACCEPTED, 'sent'], true);
    }

    public function isFailed(): bool
    {
        return $this->notification_status === 'failed';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('notification_status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('notification_status', 'failed');
    }
}
