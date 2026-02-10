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
        'price',
        'currency',
        'quantity',
        'sold',
        'sales_start',
        'sales_end',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sales_start' => 'datetime',
        'sales_end' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
