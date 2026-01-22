<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CocktailSubcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'cocktail_category_id',
        'name',
        'order',
    ];

    public function category()
    {
        return $this->belongsTo(CocktailCategory::class, 'cocktail_category_id');
    }

    public function items()
    {
        return $this->hasMany(Cocktail::class, 'subcategory_id')
            ->orderBy('position')
            ->orderBy('id');
    }

    protected static function booted()
    {
        static::creating(function (self $subcategory) {
            if ($subcategory->order === null) {
                $max = static::where('cocktail_category_id', $subcategory->cocktail_category_id)->max('order');
                $subcategory->order = ($max ?? 0) + 1;
            }
        });
    }
}
