<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grape extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'wine_type_id', 'description'];

    public function wines()
    {
        return $this->belongsToMany(Wine::class);
    }

    public function wineType()
    {
        return $this->belongsTo(WineType::class);
    }
}
