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
        'subcategory_id',
        'parent_category_id',
    ];

    protected $casts = [
        'deleted' => 'boolean',
        'subcategory_id' => 'integer',
        'parent_category_id' => 'integer',
    ];
}
