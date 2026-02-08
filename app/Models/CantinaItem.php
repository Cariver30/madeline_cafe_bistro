<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CantinaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'clover_id',
        'name',
        'description',
        'price',
        'category_id',
        'image',
        'visible',
        'manual_hidden',
        'featured_on_cover',
        'position',
    ];

    protected $casts = [
        'visible' => 'boolean',
        'manual_hidden' => 'boolean',
        'featured_on_cover' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(CantinaCategory::class, 'category_id');
    }

    public function extras()
    {
        return $this->morphToMany(Extra::class, 'assignable', 'extra_assignments')->withTimestamps();
    }
}
