<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitingListEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_name',
        'guest_phone',
        'guest_email',
        'party_size',
        'notes',
        'status',
        'quoted_minutes',
        'quoted_at',
        'reservation_at',
        'confirmation_received_at',
        'reminder_30_sent_at',
        'reminder_10_sent_at',
        'auto_cancelled_at',
        'notified_at',
        'seated_at',
        'cancelled_at',
        'no_show_at',
        'cancel_token',
    ];

    protected $casts = [
        'quoted_at' => 'datetime',
        'reservation_at' => 'datetime',
        'confirmation_received_at' => 'datetime',
        'reminder_30_sent_at' => 'datetime',
        'reminder_10_sent_at' => 'datetime',
        'auto_cancelled_at' => 'datetime',
        'notified_at' => 'datetime',
        'seated_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'no_show_at' => 'datetime',
    ];

    public function assignments()
    {
        return $this->hasMany(TableAssignment::class);
    }

    public function tables()
    {
        return $this->belongsToMany(DiningTable::class, 'table_assignments')
            ->withPivot(['assigned_at', 'released_at'])
            ->withTimestamps();
    }
}
