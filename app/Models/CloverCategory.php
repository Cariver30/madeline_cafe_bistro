<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CloverCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'clover_id',
        'name',
        'sort_order',
        'deleted',
        'scope',
    ];

    protected $casts = [
        'deleted' => 'boolean',
    ];
}
