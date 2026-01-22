<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemExtra extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'extra_id',
        'name',
        'group_name',
        'kind',
        'price',
        'quantity',
    ];

    protected $casts = [
        'extra_id' => 'integer',
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function extra()
    {
        return $this->belongsTo(Extra::class);
    }
}
