<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TableSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'dining_table_id',
        'waiting_list_entry_id',
        'open_order_id',
        'service_channel',
        'table_label',
        'party_size',
        'guest_name',
        'guest_email',
        'guest_phone',
        'loyalty_visit_id',
        'order_mode',
        'qr_token',
        'status',
        'seated_at',
        'first_order_at',
        'paid_at',
        'expires_at',
        'closed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'closed_at' => 'datetime',
        'seated_at' => 'datetime',
        'first_order_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (self $session) {
            if (empty($session->qr_token)) {
                $session->qr_token = Str::uuid()->toString();
            }
        });
    }

    public function server()
    {
        return $this->belongsTo(User::class, 'server_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function diningTable()
    {
        return $this->belongsTo(DiningTable::class);
    }

    public function waitingListEntry()
    {
        return $this->belongsTo(WaitingListEntry::class);
    }

    public function openOrder()
    {
        return $this->belongsTo(Order::class, 'open_order_id');
    }

    public function loyaltyVisit()
    {
        return $this->belongsTo(LoyaltyVisit::class);
    }
}
