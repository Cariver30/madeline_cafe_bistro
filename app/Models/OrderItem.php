<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_batch_id',
        'itemable_type',
        'itemable_id',
        'name',
        'quantity',
        'unit_price',
        'notes',
        'voided_at',
        'voided_by',
        'void_reason',
        'category_scope',
        'category_id',
        'category_name',
        'category_order',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'category_id' => 'integer',
        'category_order' => 'integer',
        'voided_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function batch()
    {
        return $this->belongsTo(OrderBatch::class, 'order_batch_id');
    }

    public function itemable()
    {
        return $this->morphTo();
    }

    public function extras()
    {
        return $this->hasMany(OrderItemExtra::class);
    }

    public function prepLabels()
    {
        return $this->belongsToMany(PrepLabel::class, 'order_item_prep_labels')
            ->withPivot(['status', 'prepared_at', 'ready_at', 'delivered_at', 'updated_by'])
            ->withTimestamps();
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }
}
