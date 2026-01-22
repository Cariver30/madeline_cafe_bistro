<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WineSubcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'wine_category_id',
        'name',
        'order',
    ];

    public function category()
    {
        return $this->belongsTo(WineCategory::class, 'wine_category_id');
    }

    public function items()
    {
        return $this->hasMany(Wine::class, 'subcategory_id')
            ->orderBy('position')
            ->orderBy('id');
    }

    protected static function booted()
    {
        static::creating(function (self $subcategory) {
            if ($subcategory->order === null) {
                $max = static::where('wine_category_id', $subcategory->wine_category_id)->max('order');
                $subcategory->order = ($max ?? 0) + 1;
            }
        });
    }
}
