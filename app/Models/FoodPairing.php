<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodPairing extends Model
{
    use HasFactory;

    // Asegurate de incluir 'dish_id' como fillable
    protected $fillable = ['name', 'dish_id'];

    // Relación muchos a muchos con vinos
    public function wines()
    {
        return $this->belongsToMany(Wine::class);
    }

    // Nueva relación: un maridaje pertenece a un plato
    public function dishes()
{
    return $this->belongsToMany(Dish::class);
}

}
