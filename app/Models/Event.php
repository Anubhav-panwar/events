<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

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
}
