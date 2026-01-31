<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wine extends Model
{
    use HasFactory;

    protected $fillable = [
        'clover_id',
        'name',
        'description',
        'price',
        'category_id',
        'subcategory_id',
        'type_id',
        'region_id',
        'image',
        'visible',
        'featured_on_cover',
        'position',
    ];

    protected $casts = [
        'visible' => 'boolean',
        'featured_on_cover' => 'boolean',
    ];

    /**
     * RELACIONES
     */

    // Relación con categoría (ej: Premium, Económico)
    public function category()
    {
        return $this->belongsTo(WineCategory::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(WineSubcategory::class, 'subcategory_id');
    }

    // Relación con tipo de vino (ej: Tinto, Blanco, Espumoso)
    public function type()
    {
        return $this->belongsTo(WineType::class, 'type_id');
    }

    // Relación con región (ej: Mendoza, Toscana)
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    // Muchas uvas pueden estar en un vino
    public function grapes()
    {
        return $this->belongsToMany(Grape::class);
    }

    // Un vino puede maridar con varios tipos de comida
    public function foodPairings()
    {
        return $this->belongsToMany(FoodPairing::class);
    }
    public function dishes()
    {
        return $this->belongsToMany(Dish::class);
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
        return $this->belongsToMany(Tax::class, 'wine_tax')->withTimestamps();
    }

}
