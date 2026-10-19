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
        'platform_fee',
        'refunded_amount',
        'currency',
        'status',
        'payment_provider',
        'payment_intent_id',
        'checkout_session_id',
        'referred_by_user_id',
        'referral_source',
        'reserved_at',
        'paid_at',
        'refunded_at',
        'cancelled_at',
        'failed_reason',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
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

    public function getGrossRevenueAttribute(): float
    {
        if ($this->status === 'paid') {
            return (float) $this->total;
        }

        return 0.0;
    }

    public function getNetRevenueAttribute(): float
    {
        if ($this->status === 'paid') {
            $gross = (float) $this->total;
            $refunded = (float) $this->refunded_amount;
            $fee = (float) $this->platform_fee;

            return max(0.0, round($gross - $refunded - $fee, 2));
        }

        return 0.0;
    }

    public function getIsFreeAttribute(): bool
    {
        return (float) $this->total <= 0 || $this->payment_provider === 'free';
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeFree($query)
    {
        return $query->where('status', 'paid')
            ->where(function ($q) {
                $q->where('total', '<=', 0)
                    ->orWhere('payment_provider', 'free');
            });
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
