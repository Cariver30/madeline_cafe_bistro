<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'source',
        'status',
        'confirmed_at',
        'cancelled_at',
        'clover_order_id',
        'clover_print_event_id',
        'metered_opened_at',
        'metered_closed_at',
        'clover_receipt_sent_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metered_opened_at' => 'datetime',
        'metered_closed_at' => 'datetime',
        'clover_receipt_sent_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
