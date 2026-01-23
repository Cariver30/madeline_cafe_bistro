<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CantinaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'image',
        'visible',
        'featured_on_cover',
        'position',
    ];

    protected $casts = [
        'visible' => 'boolean',
        'featured_on_cover' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(CantinaCategory::class, 'category_id');
    }
}
