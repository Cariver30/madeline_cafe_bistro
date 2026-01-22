<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'slug',
        'description',
        'capacity',
        'available_slots',
        'price_per_person',
        'flat_price',
        'layout_coordinates',
        'is_active',
    ];

    protected $casts = [
        'layout_coordinates' => 'array',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets()
    {
        return $this->hasMany(EventTicket::class);
    }
}
