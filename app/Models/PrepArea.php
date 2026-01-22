<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PrepArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'active',
        'is_default',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $area) {
            if (!$area->slug) {
                $area->slug = Str::slug($area->name);
            }
        });
    }

    public function labels()
    {
        return $this->hasMany(PrepLabel::class);
    }
}
