<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'tier',
        'description',
        'price',
        'currency',
        'quantity',
        'sold',
        'sales_start',
        'sales_end',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sales_start' => 'datetime',
        'sales_end' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function remainingQuantity(): int
    {
        return max(0, (int) $this->quantity - (int) $this->sold);
    }

    public function isAvailable(): bool
    {
        if ($this->is_active === false) {
            return false;
        }

        if ($this->remainingQuantity() <= 0) {
            return false;
        }

        if ($this->sales_start && now()->lt($this->sales_start)) {
            return false;
        }

        if ($this->sales_end && now()->gt($this->sales_end)) {
            return false;
        }

        return true;
    }

    public function availabilityStatus(): string
    {
        if ($this->is_active === false) {
            return 'inactive';
        }
        if ($this->sales_start && now()->lt($this->sales_start)) {
            return 'upcoming';
        }
        if ($this->sales_end && now()->gt($this->sales_end)) {
            return 'ended';
        }
        if ($this->remainingQuantity() <= 0) {
            return 'sold_out';
        }
        return 'available';
    }

    public function tierMeta(): array
    {
        $normalizedTier = strtolower($this->tier ?: 'custom');
        
        // Auto-detect tier from name if tier is custom or null
        if ($normalizedTier === 'custom' || empty($normalizedTier)) {
            $lowerName = strtolower($this->getRawOriginal('name') ?: $this->name ?: '');
            if (str_contains($lowerName, 'diamond')) {
                $normalizedTier = 'diamond';
            } elseif (str_contains($lowerName, 'gold')) {
                $normalizedTier = 'gold';
            } elseif (str_contains($lowerName, 'silver')) {
                $normalizedTier = 'silver';
            } elseif (str_contains($lowerName, 'bronze')) {
                $normalizedTier = 'bronze';
            }
        }

        return match ($normalizedTier) {
            'bronze' => [
                'tier' => 'bronze',
                'label' => __('Bronze'),
                'icon' => '🥉',
                'bg_badge' => 'bg-amber-100 text-amber-900 border-amber-300',
                'card_border' => 'border-amber-300 hover:border-amber-500',
                'ring' => 'focus:ring-amber-500 ring-amber-400',
                'gradient' => 'from-amber-700 to-amber-900',
            ],
            'silver' => [
                'tier' => 'silver',
                'label' => __('Silver'),
                'icon' => '🥈',
                'bg_badge' => 'bg-slate-200 text-slate-800 border-slate-300',
                'card_border' => 'border-slate-300 hover:border-slate-500',
                'ring' => 'focus:ring-slate-500 ring-slate-400',
                'gradient' => 'from-slate-500 to-slate-700',
            ],
            'gold' => [
                'tier' => 'gold',
                'label' => __('Gold'),
                'icon' => '🥇',
                'bg_badge' => 'bg-yellow-100 text-yellow-900 border-yellow-400',
                'card_border' => 'border-yellow-400 hover:border-yellow-500',
                'ring' => 'focus:ring-yellow-500 ring-yellow-400',
                'gradient' => 'from-yellow-500 to-amber-600',
            ],
            'diamond' => [
                'tier' => 'diamond',
                'label' => __('Diamond'),
                'icon' => '💎',
                'bg_badge' => 'bg-cyan-100 text-cyan-900 border-cyan-400',
                'card_border' => 'border-cyan-400 hover:border-cyan-500',
                'ring' => 'focus:ring-cyan-500 ring-cyan-400',
                'gradient' => 'from-cyan-500 to-blue-600',
            ],
            default => [
                'tier' => 'custom',
                'label' => __('Standard'),
                'icon' => '🎟️',
                'bg_badge' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                'card_border' => 'border-emerald-300 hover:border-emerald-500',
                'ring' => 'focus:ring-emerald-500 ring-emerald-400',
                'gradient' => 'from-emerald-500 to-teal-700',
            ],
        };
    }

    public function getNameAttribute($value): ?string
    {
        return $value ? __($value) : $value;
    }

    public function getDescriptionAttribute($value): ?string
    {
        return $value ? __($value) : $value;
    }
}

