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

    public function getQrImageUrlAttribute(): string
    {
        $payload = urlencode($this->qr_data ?: $this->code);
        return "https://api.qrserver.com/v1/create-qr-code/?size=280x280&data={$payload}";
    }
}
