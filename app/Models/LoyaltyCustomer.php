<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'points',
        'last_visit_at',
    ];

    protected $casts = [
        'last_visit_at' => 'datetime',
    ];

    public function redemptions()
    {
        return $this->hasMany(LoyaltyRedemption::class);
    }
}
