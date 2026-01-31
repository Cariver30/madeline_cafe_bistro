<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WineCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'clover_id',
        'name',
        'order',
        'show_on_cover',
        'cover_title',
        'cover_subtitle',
    ];

    protected $casts = [
        'show_on_cover' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(Wine::class, 'category_id')->orderBy('position')->orderBy('id');
    }

    public function subcategories()
    {
        return $this->hasMany(WineSubcategory::class, 'wine_category_id')
            ->orderBy('order')
            ->orderBy('id');
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'wine_category_tax')->withTimestamps();
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
