<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'printer_id',
        'order_id',
        'print_template_id',
        'content_type',
        'payload',
        'status',
        'printed_at',
    ];

    protected $casts = [
        'printed_at' => 'datetime',
    ];

    public function printer()
    {
        return $this->belongsTo(Printer::class);
    }

    public function template()
    {
        return $this->belongsTo(PrintTemplate::class, 'print_template_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
