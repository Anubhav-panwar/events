<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'code',
        'attendee_name',
        'qr_data',
        'issued_at',
        'checked_in_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'checked_in_at' => 'datetime',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function getOrderAttribute(): ?Order
    {
        return $this->orderItem?->order;
    }

    public function getEventAttribute(): ?Event
    {
        return $this->orderItem?->ticketType?->event ?: $this->orderItem?->order?->event;
    }

    public function getTicketTypeAttribute(): ?TicketType
    {
        return $this->orderItem?->ticketType;
    }

    public function isValid(): bool
    {
        // An active valid ticket must belong to a paid order and not be checked in yet
        $order = $this->orderItem?->order;
        if (!$order || $order->status !== 'paid') {
            return false;
        }

        return $this->checked_in_at === null;
    }

    public function isAlreadyUsed(): bool
    {
        return $this->checked_in_at !== null;
    }

    public static function generateUniqueCode(?int $eventId = null, ?string $tier = null): string
    {
        $prefix = 'TKT';
        if ($eventId) {
            $prefix .= '-EV' . $eventId;
        }
        if ($tier && in_array(strtolower($tier), ['bronze', 'silver', 'gold', 'diamond'])) {
            $prefix .= '-' . strtoupper(substr($tier, 0, 1));
        }

        do {
            $randomPart = strtoupper(\Illuminate\Support\Str::random(8));
            $candidate = $prefix . '-' . $randomPart;
        } while (static::query()->where('code', $candidate)->exists());

        return $candidate;
    }

    public function getQrImageUrlAttribute(): string
    {
        $payload = urlencode($this->qr_data ?: $this->code);
        return "https://api.qrserver.com/v1/create-qr-code/?size=280x280&data={$payload}";
    }
}
