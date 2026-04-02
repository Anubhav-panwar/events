<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    protected static function booted(): void
    {
        static::saving(function (VendorProfile $profile) {
            if (filled($profile->slug)) {
                return;
            }

            $base = Str::slug($profile->business_name ?: 'vendor');
            $slug = $base;
            $counter = 1;
            while (static::query()->where('slug', $slug)->when($profile->exists, fn($q) => $q->where('id', '!=', $profile->id))->exists()) {
                $counter++;
                $slug = "{$base}-{$counter}";
            }
            $profile->slug = $slug;
        });
    }

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

    public function media()
    {
        return $this->hasMany(VendorMedia::class);
    }
}
