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
        'total',
        'status',
        'reserved_at',
        'paid_at',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'paid_at' => 'datetime',
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

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
