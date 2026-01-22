<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrinterRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'printer_id',
        'print_template_id',
        'category_scope',
        'category_id',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function printer()
    {
        return $this->belongsTo(Printer::class);
    }

    public function template()
    {
        return $this->belongsTo(PrintTemplate::class, 'print_template_id');
    }
}
