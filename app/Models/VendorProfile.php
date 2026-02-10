<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'slug',
        'description',
        'phone',
        'website',
        'instagram',
        'facebook',
        'twitter',
        'address',
        'city',
        'country',
        'latitude',
        'longitude',
        'opening_hours',
        'is_approved',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'is_approved' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_vendor_profile');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'vendor_follows');
    }
}
