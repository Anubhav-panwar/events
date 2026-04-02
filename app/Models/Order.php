<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendor_profile_id',
        'event_id',
        'total',
        'currency',
        'status',
        'payment_provider',
        'payment_intent_id',
        'checkout_session_id',
        'referred_by_user_id',
        'referral_source',
        'reserved_at',
        'paid_at',
        'cancelled_at',
        'failed_reason',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
