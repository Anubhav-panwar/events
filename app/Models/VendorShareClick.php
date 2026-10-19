<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorShareClick extends Model
{
    use HasFactory;

    public const CHANNELS = ['whatsapp', 'facebook', 'x', 'linkedin', 'telegram', 'email', 'copy'];

    public $timestamps = false;

    protected $fillable = [
        'vendor_profile_id',
        'user_id',
        'channel',
        'target_url',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
