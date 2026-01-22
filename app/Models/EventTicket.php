<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_section_id',
        'customer_name',
        'customer_email',
        'guest_count',
        'total_paid',
        'ticket_code',
        'meta',
        'status',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function section()
    {
        return $this->belongsTo(EventSection::class, 'event_section_id');
    }
}
