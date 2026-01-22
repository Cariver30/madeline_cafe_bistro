<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'email',
        'confirmed',
        'confirmation_token',
    ];

    protected $casts = [
        'confirmed' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
