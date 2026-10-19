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
    public const STATUS_SENT = 'sent';
    public const STATUS_NOTIFIED = 'sent';
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
        'notification_error',
        'retry_count',
    ];

    protected $casts = [
        'minutes_before' => 'integer',
        'notified_at' => 'datetime',
        'last_sent_at' => 'datetime',
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
        return $this->notification_status === 'sent';
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
