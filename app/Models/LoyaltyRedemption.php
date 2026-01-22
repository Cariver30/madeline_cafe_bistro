<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'loyalty_customer_id',
        'loyalty_reward_id',
        'points_used',
        'status',
        'approved_by',
        'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(LoyaltyCustomer::class, 'loyalty_customer_id');
    }

    public function reward()
    {
        return $this->belongsTo(LoyaltyReward::class, 'loyalty_reward_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
