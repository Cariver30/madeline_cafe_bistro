<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cocktail extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'category_id', 'image', 'visible'];

    public function category()
    {
        return $this->belongsTo(CocktailCategory::class);
    }
}




