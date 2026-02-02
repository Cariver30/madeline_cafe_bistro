<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitingListSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'default_wait_minutes',
        'notify_after_minutes',
        'sms_enabled',
        'email_enabled',
        'notify_message_template',
    ];

    protected $casts = [
        'sms_enabled' => 'boolean',
        'email_enabled' => 'boolean',
    ];

    public static function current(): self
    {
        $setting = static::query()->first();
        if ($setting) {
            return $setting;
        }

        return static::create([
            'default_wait_minutes' => 15,
            'notify_after_minutes' => 10,
            'sms_enabled' => true,
            'email_enabled' => false,
        ]);
    }
}
