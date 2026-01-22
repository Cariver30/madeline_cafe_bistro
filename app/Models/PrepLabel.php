<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PrepLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'prep_area_id',
        'printer_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $label) {
            if (!$label->slug) {
                $label->slug = Str::slug($label->name);
            }
        });
    }

    public function area()
    {
        return $this->belongsTo(PrepArea::class, 'prep_area_id');
    }

    public function printer()
    {
        return $this->belongsTo(Printer::class);
    }

    public function dishes()
    {
        return $this->morphedByMany(Dish::class, 'labelable', 'prep_labelables');
    }

    public function cocktails()
    {
        return $this->morphedByMany(Cocktail::class, 'labelable', 'prep_labelables');
    }

    public function wines()
    {
        return $this->morphedByMany(Wine::class, 'labelable', 'prep_labelables');
    }

    public function orderItems()
    {
        return $this->belongsToMany(OrderItem::class, 'order_item_prep_labels')
            ->withPivot(['status', 'prepared_at', 'ready_at', 'delivered_at', 'updated_by'])
            ->withTimestamps();
    }
}
