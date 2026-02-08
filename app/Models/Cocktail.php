<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Dish;

class Cocktail extends Model
{
    use HasFactory;

    protected $fillable = [
        'clover_id',
        'name',
        'description',
        'price',
        'category_id',
        'subcategory_id',
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
        return $this->belongsTo(CocktailCategory::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(CocktailSubcategory::class, 'subcategory_id');
    }

    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'cocktail_dish');
    }

    public function extras()
    {
        return $this->morphToMany(Extra::class, 'assignable', 'extra_assignments')->withTimestamps();
    }

    public function prepLabels()
    {
        return $this->morphToMany(PrepLabel::class, 'labelable', 'prep_labelables')
            ->withTimestamps();
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'cocktail_tax')->withTimestamps();
    }
}
