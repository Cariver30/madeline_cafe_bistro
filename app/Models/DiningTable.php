<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiningTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'capacity',
        'section',
        'status',
        'position',
        'notes',
    ];

    public function assignments()
    {
        return $this->hasMany(TableAssignment::class);
    }

    public function activeAssignment()
    {
        return $this->hasOne(TableAssignment::class)->whereNull('released_at');
    }

    public function activeSession()
    {
        return $this->hasOne(TableSession::class)->where('status', 'active');
    }

    public function waitingListEntries()
    {
        return $this->belongsToMany(WaitingListEntry::class, 'table_assignments')
            ->withPivot(['assigned_at', 'released_at'])
            ->withTimestamps();
    }

    public function tableSessions()
    {
        return $this->hasMany(TableSession::class);
    }
}
