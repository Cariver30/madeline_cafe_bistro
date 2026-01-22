<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventPromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subject',
        'preview_text',
        'hero_image',
        'body_html',
        'attachments',
        'status',
        'sent_at',
        'send_count',
        'send_error',
    ];

    protected $casts = [
        'attachments' => 'array',
        'sent_at' => 'datetime',
    ];
}
