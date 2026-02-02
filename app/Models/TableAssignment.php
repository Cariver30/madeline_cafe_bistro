<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'waiting_list_entry_id',
        'dining_table_id',
        'assigned_at',
        'released_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function waitingListEntry()
    {
        return $this->belongsTo(WaitingListEntry::class);
    }

    public function diningTable()
    {
        return $this->belongsTo(DiningTable::class);
    }
}
