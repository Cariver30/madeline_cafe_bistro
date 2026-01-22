<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategorySubcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'order',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function dishes()
    {
        return $this->hasMany(Dish::class, 'subcategory_id')
            ->orderBy('position')
            ->orderBy('id');
    }

    protected static function booted()
    {
        static::creating(function (self $subcategory) {
            if ($subcategory->order === null) {
                $max = static::where('category_id', $subcategory->category_id)->max('order');
                $subcategory->order = ($max ?? 0) + 1;
            }
        });
    }
}
