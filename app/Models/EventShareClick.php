<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventShareClick extends Model
{
    use HasFactory;

    public const CHANNELS = ['whatsapp', 'facebook', 'x', 'linkedin', 'copy'];

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'user_id',
        'referrer_user_id',
        'channel',
        'target_url',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }
}
