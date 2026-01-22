<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'extra_id',
        'assignable_id',
        'assignable_type',
    ];

    public function extra()
    {
        return $this->belongsTo(Extra::class);
    }

    public function assignable()
    {
        return $this->morphTo();
    }
}
