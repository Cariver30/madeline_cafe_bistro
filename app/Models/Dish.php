<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cocktail;

class Dish extends Model
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
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(CategorySubcategory::class, 'subcategory_id');
    }

    public function foodPairings()
    {
        return $this->belongsToMany(FoodPairing::class);
    }

    public function wines()
    {
        return $this->belongsToMany(Wine::class);
    }

    public function cocktails()
    {
        return $this->belongsToMany(Cocktail::class, 'cocktail_dish');
    }

    public function recommendedDishes()
    {
        return $this->belongsToMany(
            Dish::class,
            'dish_recommendations',
            'dish_id',
            'recommended_dish_id'
        );
    }

    public function recommendedBy()
    {
        return $this->belongsToMany(
            Dish::class,
            'dish_recommendations',
            'recommended_dish_id',
            'dish_id'
        );
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
        return $this->belongsToMany(Tax::class, 'dish_tax')->withTimestamps();
    }
}
