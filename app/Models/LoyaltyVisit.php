<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LoyaltyVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'expected_name',
        'expected_email',
        'expected_phone',
        'qr_token',
        'status',
        'points_awarded',
        'confirmed_at',
        'customer_snapshot',
    ];

    protected $casts = [
        'customer_snapshot' => 'array',
        'confirmed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (self $visit) {
            if (empty($visit->qr_token)) {
                $visit->qr_token = Str::uuid()->toString();
            }
        });
    }

    public function server()
    {
        return $this->belongsTo(User::class, 'server_id');
    }
}
