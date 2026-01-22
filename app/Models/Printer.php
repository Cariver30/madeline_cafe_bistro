<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Printer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'model',
        'connection',
        'device_id',
        'token',
        'location',
        'is_active',
        'last_seen_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (self $printer) {
            if (empty($printer->token)) {
                $printer->token = Str::uuid()->toString();
            }
        });
    }

    public function routes()
    {
        return $this->hasMany(PrinterRoute::class);
    }

    public function jobs()
    {
        return $this->hasMany(PrintJob::class);
    }
}
