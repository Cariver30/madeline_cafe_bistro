<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'special_id',
        'scope',
        'item_id',
        'category_id',
        'active',
        'days_of_week',
        'starts_at',
        'ends_at',
        'offer_type',
        'offer_value',
        'offer_text',
    ];

    protected $casts = [
        'active' => 'boolean',
        'days_of_week' => 'array',
        'offer_value' => 'decimal:2',
    ];

    public function special()
    {
        return $this->belongsTo(Special::class);
    }
}
