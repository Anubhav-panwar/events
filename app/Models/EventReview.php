<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'order_id',
        'rating',
        'review',
        'is_verified_attendee',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_attendee' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
