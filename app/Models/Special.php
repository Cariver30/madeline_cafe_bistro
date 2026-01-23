<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Special extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'active',
        'days_of_week',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'days_of_week' => 'array',
    ];

    public function categories()
    {
        return $this->hasMany(SpecialCategory::class);
    }

    public function items()
    {
        return $this->hasMany(SpecialItem::class);
    }
}
