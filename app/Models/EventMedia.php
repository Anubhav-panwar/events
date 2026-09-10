<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'disk',
        'path',
        'type',
        'original_name',
        'size',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getUrlAttribute(): string
    {
        return self::resolveUrl($this->disk, $this->path);
    }

    public static function resolveUrl(?string $disk, ?string $path): string
    {
        if (empty($path)) {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }
        if (str_starts_with($clean, 'app/public/')) {
            $clean = substr($clean, 11);
        }
        return asset('storage/' . $clean);
    }
}
