<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_blocked',
        'blocked_at',
        'blocked_reason',
        'blocked_by_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
            'blocked_at' => 'datetime',
        ];
    }

    public function vendorProfile()
    {
        return $this->hasOne(VendorProfile::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function savedEvents()
    {
        return $this->belongsToMany(Event::class, 'event_saves');
    }

    public function followedVendors()
    {
        return $this->belongsToMany(VendorProfile::class, 'vendor_follows');
    }

    public function eventReminders()
    {
        return $this->hasMany(EventReminder::class);
    }

    public function referredOrders()
    {
        return $this->hasMany(Order::class, 'referred_by_user_id');
    }

    public function eventReviews()
    {
        return $this->hasMany(EventReview::class);
    }

    public function vendorReviews()
    {
        return $this->hasMany(VendorReview::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    public function blockRecords()
    {
        return $this->hasMany(UserBlockRecord::class)->latest();
    }

    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by_id');
    }

    public function isBlocked(): bool
    {
        return (bool) $this->is_blocked;
    }

    public function isActive(): bool
    {
        return ! $this->is_blocked;
    }

    public function canReceiveNotifications(): bool
    {
        return ! $this->is_blocked;
    }

    public function statusBadge(): array
    {
        if ($this->is_blocked) {
            return [
                'status' => 'blocked',
                'label' => __('Blocked'),
                'badge_class' => 'bg-rose-50 text-rose-800 border-rose-200',
                'dot_class' => 'bg-rose-600',
                'icon' => '⊘',
            ];
        }

        return [
            'status' => 'active',
            'label' => __('Active'),
            'badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'dot_class' => 'bg-emerald-600',
            'icon' => '●',
        ];
    }
}
