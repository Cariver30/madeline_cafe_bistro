<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'start_at',
        'end_at',
        'hero_image',
        'map_image',
        'is_active',
        'additional_info',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_active' => 'boolean',
        'additional_info' => 'array',
    ];

    public function sections()
    {
        return $this->hasMany(EventSection::class);
    }

    public function tickets()
    {
        return $this->hasMany(EventTicket::class);
    }
}
