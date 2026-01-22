<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_session_id',
        'server_id',
        'status',
        'confirmed_at',
        'cancelled_at',
        'payment_method',
        'paid_at',
        'paid_total',
        'tip_total',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'payment_status',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'paid_at' => 'datetime',
        'paid_total' => 'decimal:2',
        'tip_total' => 'decimal:2',
    ];

    public function tableSession()
    {
        return $this->belongsTo(TableSession::class);
    }

    public function server()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function batches()
    {
        return $this->hasMany(OrderBatch::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
