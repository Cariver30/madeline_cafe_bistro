<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'points_required',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function redemptions()
    {
        return $this->hasMany(LoyaltyRedemption::class);
    }
}
