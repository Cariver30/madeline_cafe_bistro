<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Popup extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'image', 'view', 'start_date', 'end_date', 'active', 'repeat_days'
    ];
}
