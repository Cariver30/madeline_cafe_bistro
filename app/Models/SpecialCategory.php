<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'special_id',
        'scope',
        'category_id',
        'active',
        'days_of_week',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'days_of_week' => 'array',
    ];

    public function special()
    {
        return $this->belongsTo(Special::class);
    }
}
