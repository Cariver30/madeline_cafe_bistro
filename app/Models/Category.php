<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'clover_id',
        'name',
        'order',
        'manual_order',
        'show_on_cover',
        'cover_title',
        'cover_subtitle',
    ];

    protected $casts = [
        'manual_order' => 'boolean',
        'show_on_cover' => 'boolean',
    ];

    public function dishes()
    {
        return $this->hasMany(Dish::class)->orderBy('position')->orderBy('id');
    }

    public function subcategories()
    {
        return $this->hasMany(CategorySubcategory::class)
            ->orderBy('order')
            ->orderBy('id');
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'category_tax')->withTimestamps();
    }

    protected static function booted()
    {
        static::creating(function ($category) {
            if (is_null($category->order)) {
                $category->order = (static::max('order') ?? 0) + 1;
            }
        });
    }
}
